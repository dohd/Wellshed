<?php

namespace App\Repositories\Focus\supplier_creditnote;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Focus\cuInvoiceNumber\ControlUnitInvoiceNumberController;
use App\Repositories\BaseRepository;
use App\Models\creditnote\CreditNote;
use App\Models\creditnote\CreditNoteItem;
use App\Models\supplier_creditnote\SupplierCreditNote;
use App\Models\supplier_creditnote\SupplierCreditNoteItem;
use App\Repositories\Accounting;
use App\Repositories\CustomerSupplierBalance;
use Illuminate\Support\Facades\DB;
use \Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;

/**
 * Class SupplierCreditNoteRepository.
 */
class SupplierCreditNoteRepository extends BaseRepository
{
    use Accounting, CustomerSupplierBalance;
    /**
     * Associated Repository Model.
     */
    const MODEL = SupplierCreditNote::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query()->where('is_debit', request('is_debit', 0));

        $q->when(request('start_date') && request('end_date'), function($q) {
            $q->whereBetween('date', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        });

        return $q->get();
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
        // sanitize
        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            $keys = ['taxable', 'subtotal', 'tax', 'total', 'fx_curr_rate', 'fx_subtotal', 'fx_taxable', 'fx_tax', 'fx_total', 'fx_gain', 'fx_loss'];
            if (in_array($key, $keys)) $input[$key] = floatval(str_replace(',', '', $val));
            $keys = ['qty', 'rate', 'prod_taxid', 'prod_tax', 'prod_total', 'prod_subtotal', 'prod_taxable', 'prod_fx_rate', 'prod_fx_taxable', 'prod_fx_subtotal', 'prod_fx_tax', 'prod_fx_total', 'prod_fx_gain', 'prod_fx_loss'];
            if (in_array($key, $keys)) $input[$key] = array_map(fn($v) => floatval(str_replace(',', '', $v)), $val);
        }
        $data = Arr::only($input, [
            'supplier_id', 'tid', 'date', 'classlist_id', 'tax_id', 'note', 'is_debit', 'bill_id', 'grn_type','grn_id',
            'cu_invoice_no', 'taxable', 'subtotal', 'tax', 'total', 'account_id', 'payment_mode', 'reference_no',
            'currency_id', 'fx_curr_rate', 'fx_subtotal', 'fx_taxable', 'fx_tax', 'fx_total', 'fx_gain', 'fx_loss',
            'efris_reason_code', 'efris_reason_code_name',
        ]);
        $data_items = array_diff_key($input, $data);
        // dd($data, $data_items);

        DB::beginTransaction();

        // cu invoice no
        if (!isset(auth()->user()->business->etr_code)) throw ValidationException::withMessages(['Please set CU Serial Number in the Business Settings']);
        $cuPrefixArr = explode('KRAMW', auth()->user()->business->etr_code);
        $cuPrefix = @$cuPrefixArr[1];
        if (empty($data['cu_invoice_no'])) {
            $cuResponse = ['isSet' => true];
        } elseif ($cuPrefix) {
            $setCu = @explode($cuPrefix, $input['cu_invoice_no'])[1] ?: $input['cu_invoice_no'];
            $cuResponse = (new ControlUnitInvoiceNumberController())->setCuInvoiceNumber($setCu);
        }
        if (!$cuResponse['isSet']) throw ValidationException::withMessages([$cuResponse['message']]);

        // create credit/debit note
        $creditnote = SupplierCreditNote::create($data);

        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['prod_total'] > 0);
        // if (!$data_items) throw ValidationException::withMessages(['Line Items required!']);
        foreach ($data_items as $key => $value) {
            $value = array_replace($value, [
                'supplier_creditnote_id' => $creditnote->id,
                'user_id' => auth()->user()->id, 
                'ins' => auth()->user()->ins,
                'prod_taxid' => $value['prod_taxid'],
                'prod_tax' => $value['prod_tax'],
                'prod_total' => $value['prod_total'],
                'prod_subtotal' => $value['prod_subtotal'],
                'prod_taxable' => $value['prod_taxable'],
            ]);
            // unset($value['prod_taxid'], $value['prod_tax'], $value['prod_total'], $value['prod_subtotal'], $value['prod_taxable']);
            $data_items[$key] = $value;
        }
        SupplierCreditNoteItem::insert($data_items);

        if ($creditnote) {
            DB::commit();
            return $creditnote;
        }
    }

    /**
     * For Updating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function update($creditnote, array $input)
    {
        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            $keys = ['taxable', 'subtotal', 'tax', 'total', 'fx_curr_rate', 'fx_subtotal', 'fx_taxable', 'fx_tax', 'fx_total', 'fx_gain', 'fx_loss'];
            if (in_array($key, $keys)) $input[$key] = floatval(str_replace(',', '', $val));
            $keys = ['qty', 'rate', 'prod_taxid', 'prod_tax', 'prod_total', 'prod_subtotal', 'prod_taxable', 'prod_fx_rate', 'prod_fx_taxable', 'prod_fx_subtotal', 'prod_fx_tax', 'prod_fx_total', 'prod_fx_gain', 'prod_fx_loss'];
            if (in_array($key, $keys)) $input[$key] = array_map(fn($v) => floatval(str_replace(',', '', $v)), $val);
        }
        $data = Arr::only($input, [
            'supplier_id', 'tid', 'date', 'classlist_id', 'tax_id', 'note', 'is_debit', 'bill_id', 'grn_type','grn_id',
            'cu_invoice_no', 'taxable', 'subtotal', 'tax', 'total', 'account_id', 'payment_mode', 'reference_no',
            'currency_id', 'fx_curr_rate', 'fx_subtotal', 'fx_taxable', 'fx_tax', 'fx_total', 'fx_gain', 'fx_loss',
            'efris_reason_code', 'efris_reason_code_name',
        ]);
        $data_items = array_diff_key($input, $data);
    
        DB::beginTransaction();

        $result = $creditnote->update($data);

        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['prod_total'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Line Items required!']);
        $creditnote->items()->whereNotIn('id', array_map(fn($v) => $v['id'], $data_items))->delete();
        // update or create item
        foreach($data_items as $value) {
            $value = array_replace($value, [
                'supplier_creditnote_id' => $creditnote->id,
                'user_id' => auth()->user()->id, 
                'ins' => auth()->user()->ins,
                'prod_taxid' => $value['prod_taxid'],
                'prod_tax' => $value['prod_tax'],
                'prod_total' => $value['prod_total'],
                'prod_subtotal' => $value['prod_subtotal'],
                'prod_taxable' => $value['prod_taxable'],
            ]);
            // unset($value['prod_taxid'], $value['prod_tax'], $value['prod_total'], $value['prod_subtotal'], $value['prod_taxable']);
            $cnote_item = SupplierCreditNoteItem::firstOrNew(['id' => $value['id']]);
            $cnote_item->fill($value);
            $cnote_item->save();
        }

        if ($result) {
            DB::commit();
            return $result;
        }
    }    

    /**
     * For deleting the respective model from storage
     *
     * @param CreditNote $creditnote
     * @throws GeneralException
     * @return bool
     */
    public function delete($creditnote)
    {
        DB::beginTransaction();
        
        $creditnote->items()->delete();
        $result = $creditnote->delete();
        

        if ($result) {
            DB::commit();
            return true;
        }
    }    
}