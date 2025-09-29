<?php

namespace App\Repositories\Focus\estimate;

use DB;
use App\Exceptions\GeneralException;
use App\Models\estimate\Estimate;
use App\Models\estimate\EstimateItem;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class EstimateRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Estimate::class;
    
    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return Estimate $estimate
     */
    public function create(array $input)
    {  
        DB::beginTransaction();
        
        $input = array_replace($input, [
            'date' => date_for_database($input['date']),
            'total' => numberClean($input['total']),
            'est_total' => numberClean($input['est_total']),
            'balance' => numberClean($input['balance']),
        ]);
        foreach ($input as $key => $val) {
            if (in_array($key, ['est_qty', 'est_rate', 'est_amount', 'tax', 'qty', 'rate', 'amount', 'rem_qty', 'rem_amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // create estimate
        $data = Arr::only($input, ['date', 'customer_id', 'quote_id', 'note', 'total', 'est_total', 'balance']);
        $estimate = Estimate::create($data);

        $data_items = array_diff_key($input, $data);
        $data_items['estimate_id'] = array_fill(0, count($data_items['est_qty']), $estimate->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['est_amount'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Est. Amount field is required!']);
        EstimateItem::insert($data_items);

        if ($estimate) {
            DB::commit();
            return $estimate;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Estimate $estimate
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(Estimate $estimate, array $input)
    {   
        DB::beginTransaction();

        $input = array_replace($input, [
            'date' => date_for_database($input['date']),
            'total' => numberClean($input['total']),
            'est_total' => numberClean($input['est_total']),
            'balance' => numberClean($input['balance']),
        ]);
        foreach ($input as $key => $val) {
            if (in_array($key, ['est_qty', 'est_rate', 'est_amount', 'tax', 'qty', 'rate', 'amount', 'rem_qty', 'rem_amount'])) {
                $input[$key] = array_map(fn($v) =>  numberClean($v), $val);
            }
        }

        // create estimate
        $data = Arr::only($input, ['date', 'customer_id', 'quote_id', 'note', 'total', 'est_total', 'balance']);
        $result = $estimate->update($data);

        $data_items = array_diff_key($input, $data);
        $data_items['estimate_id'] = array_fill(0, count($data_items['est_qty']), $estimate->id);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['est_amount'] > 0);
        if (!$data_items) throw ValidationException::withMessages(['Est. Amount field is required!']);
        $estimate->items()->delete();
        EstimateItem::insert($data_items);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Estimate $estimate
     * @throws GeneralException
     * @return bool
     */
    public function delete(Estimate $estimate)
    { 
        DB::beginTransaction();
        $estimate->items()->delete();
        if ($estimate->delete()) {
            DB::commit();
            return true;
        }
    }
}
