<?php

namespace App\Repositories\Focus\import_request;

use DB;
use Carbon\Carbon;
use App\Exceptions\GeneralException;
use App\Models\import_request\ImportRequest;
use App\Models\import_request\ImportRequestExpense;
use App\Models\import_request\ImportRequestItem;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ImportRequestRepository.
 */
class ImportRequestRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = ImportRequest::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()->get();
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
        foreach($data as $key => $val)
        {
            if(in_array($key, ['date','due_date']))
                $data[$key] = date_for_database($val);
        }

        $result = ImportRequest::create($data);
        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'import_request_id' => $result->id, 
                'ins' => $result->ins,
                'qty' => floatval(str_replace(',', '', $v['qty'])),
            ]);
        }, $data_items);
        ImportRequestItem::insert($data_items);

        if($result){
            DB::commit();
            return $result;
        }
        
        throw new GeneralException('Error Creating Import Request');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param ImportRequest $import_request
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($import_request, array $input)
    {
        $data = $input['data'];
        foreach($data as $key => $val)
        {
            if(in_array($key, ['date','due_date']))
                $data[$key] = date_for_database($val);
        }
        DB::beginTransaction();
        $import_request->update($data);

        $data_items = $input['data_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $import_request->items()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['qty']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $import_request_item = ImportRequestItem::firstOrNew(['id' => $item['id']]);
            $import_request_item->fill(array_replace($item, ['import_request_id' => $import_request['id'], 'ins' => $import_request['ins']]));
            if (!$import_request_item->id) unset($import_request_item->id);
            $import_request_item->save();
        }

        if($import_request)
        {
            DB::commit();
            return true;
        }

        throw new GeneralException('Error Updating Import Request');
    }

    public function update_import_request($import_request, array $input)
    {
        $data = $input['data'];
        foreach($data as $key => $val)
        {
            if(in_array($key, ['item_cost','total','shipping_cost']))
                $data[$key] = numberClean($val);
        }
        DB::beginTransaction();
        $import_request->update($data);

        $data_items = $input['data_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $import_request->items()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, [
                    'rate','amount','cbm','total_cbm','cbm_percent','cbm_value','rate_percent',
                    'rate_value','avg_cbm_rate_value','avg_rate_shippment','avg_rate_shippment_per_item'
                ]))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $import_request_item = ImportRequestItem::firstOrNew(['id' => $item['id']]);
            $import_request_item->fill(array_replace($item, ['import_request_id' => $import_request['id'], 'ins' => $import_request['ins']]));
            if (!$import_request_item->id) unset($import_request_item->id);
            $import_request_item->save();
        }

        //Expense items
        $expense_items = $input['expense_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['e_id']; }, $expense_items);
        $import_request->expenses()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($expense_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, [
                    'exp_qty', 'exp_rate','fx_curr_rate','fx_rate'
                ]))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $import_request_expense = ImportRequestExpense::firstOrNew(['id' => $item['e_id']]);
            unset($item['e_id']);
            $import_request_expense->fill(array_replace($item, ['import_request_id' => $import_request['id'],'user_id'=>$import_request['user_id'], 'ins' => $import_request['ins']]));
            if (!$import_request_expense->id) unset($import_request_expense->id);
            $import_request_expense->save();
        }

        if($import_request)
        {
            DB::commit();
            return true;
        }
        throw new GeneralException('Error Updating Import Request');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param ImportRequest $import_request
     * @throws GeneralException
     * @return bool
     */
    public function delete($import_request)
    {
        if ($import_request->items()->delete() && $import_request->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.ImportRequests.delete_error'));
    }
}
