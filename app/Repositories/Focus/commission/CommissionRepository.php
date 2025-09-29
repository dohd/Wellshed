<?php

namespace App\Repositories\Focus\commission;

use DB;
use Carbon\Carbon;
use App\Models\commission\Commission;
use App\Exceptions\GeneralException;
use App\Models\commission\CommissionItem;
use App\Models\Company\Company;
use App\Models\currency\Currency;
use App\Models\items\UtilityBillItem;
use App\Models\supplier\Supplier;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Class commissionRepository.
 */
class CommissionRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Commission::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['total'])) 
                $data[$key] = numberClean($val);
        }

        $result = Commission::create($data);
        
        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'commission_id' => $result->id, 
                'ins' => $result->ins,
                'invoice_amount' =>  floatval(str_replace(',', '', $v['invoice_amount'])),
                'actual_commission' => floatval(str_replace(',', '', $v['actual_commission'])),
            ]);
        }, $data_items);
        CommissionItem::insert($data_items);
        $this->generate_bill($result);

        if($result){
            DB::commit();
            return $result;
        }
        throw new GeneralException('Error Creating commission');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param commission $commission
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($commission, array $input)
    {
       DB::beginTransaction();
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['total'])) 
                $data[$key] = numberClean($val);
        }
        $commission->update($data);

        $data_items = $input['data_items'];
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $commission->items()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['product_price', 'product_subtotal', 'buy_price', 'estimate_qty']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $commission_item = CommissionItem::firstOrNew(['id' => $item['id']]);
            $commission_item->fill(array_replace($item, ['commission_id' => $commission['id'], 'ins' => $commission['ins']]));
            if (!$commission_item->id) unset($commission_item->id);
            $commission_item->save();
        }
        $this->generate_bill($commission);
        if($commission){
            DB::commit();
            return $commission;
        }

        throw new GeneralException(trans('exceptions.backend.commissions.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param commission $commission
     * @throws GeneralException
     * @return bool
     */
    public function delete($commission)
    {
        try {
            $bill = $commission->bill;
            // Check if commission is already paid
            if ($bill && $bill->payments()->exists()) {
                throw ValidationException::withMessages([
                    'error' => 'Commission is already paid!!'
                ]);
            }
            if ($bill) {
                // $bill->transactions()->delete();
                $bill->items()->delete();
                $bill->delete();
            }

            // Delete related items first
            $commission->items()->delete();

            // Delete commission itself
            if (!$commission->delete()) {
                throw new \Exception('Commission Payment could not be deleted.');
            }

            return true;

        } catch (\Throwable $e) {
            \Log::error('Commission Payment Deletion Failed', [
                'error' => $e->getMessage(),
                'commission_id' => $commission->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            // Preserve validation exceptions, throw others as generic Exception
            if ($e instanceof ValidationException) {
                throw $e;
            }

            throw new \Exception('Error deleting Commission Payment: ' . $e->getMessage(), 0, $e);
        }

    }

    public function generate_bill($commission)
    {
        $supplier = Supplier::where('name', 'LIKE', '%walk-in%')->orWhere('company', 'LIKE', '%walk-in%')->first();
        if (!$supplier) {
            $company = Company::find(auth()->user()->ins);
            $supplier = Supplier::create([
                'name' => 'Walk-In',
                'phone' => 0,
                'address' => 'N/A',
                'city' => @$company->city,
                'region' => @$company->region,
                'country' => @$company->country,
                'email' => 'walkin@sample.com',
                'company' => 'Walk-In',
                'taxid' => 'N/A',
                'role_id' => 0,
            ]);
        }
        $bill_items_data = array_map(fn($v) => [
            'ref_id' => $v['id'],
            'note' => "({$v['name']}) {$v['phone']} {$v['commission_type']}",
            'qty' => 1,
            'subtotal' => 1 * $v['actual_commission'],
            'tax' => 0,
            'total' => $v['actual_commission'], 
        ], $commission->items->toArray());

        $bill_data = [
            'currency_id' => Currency::where('rate', 1)->first()->id,
            'supplier_id' => $supplier->id,
            'reference' => '',
            'reference_type' => 'commission',
            'document_type' => 'commission',
            'ref_id' => $commission->id,
            'commission_id' => $commission->id,
            'date' => $commission->date,
            'due_date' => $commission->date,
            'tax_rate' => 0,
            'subtotal' => $commission->total,
            'tax' => 0,
            'total' => $commission->total,
            'note' => $commission->title,
        ];

        $bill = UtilityBill::where(['document_type' => 'commission','ref_id' => $commission->id])->first();
        if ($bill) {
            // update bill
            $bill->update($bill_data);
            foreach ($bill_items_data as $item) {
                $new_item = UtilityBillItem::firstOrNew(['bill_id' => $bill->id, 'ref_id' => $item['ref_id']]);
                $new_item->fill($item);
                $new_item->save();
            }
            // $bill->transactions()->delete();
        } else {
            // create bill
            $bill_data['tid'] = UtilityBill::max('tid')+1;
            $bill = UtilityBill::create($bill_data);
            $bill_items_data = array_map(function ($v) use($bill) {
                $v['bill_id'] = $bill->id;
                return $v;
            }, $bill_items_data);
            UtilityBillItem::insert($bill_items_data);
        }
        return $bill;
    }
}
