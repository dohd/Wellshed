<?php

namespace App\Repositories\Focus\orders;

use DB;
use Carbon\Carbon;
use App\Exceptions\GeneralException;
use App\Models\delivery_frequency\DeliveryFreq;
use App\Models\orders\Orders;
use App\Models\orders\OrdersItem;
use App\Repositories\BaseRepository;
use DateTime;

/**
 * Class OrdersRepository.
 */
class OrdersRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Orders::class;

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
        // dd($input);
        DB::beginTransaction();
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if(in_array($key,['total','tax','taxable','subtotal']))
                $data[$key] = numberClean($val);
            if(in_array($key,['start_month','end_month']))
                $data[$key] = date_for_database($val);
        }
        $data['status'] = 'confirmed';
        $result = Orders::create($data);

        //Line items
        $data_items = $input['data_items'];
         $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'order_id' => $result->id, 
                'rate' =>  floatval(str_replace(',', '', $v['rate'])),
                'itemtax' => floatval(str_replace(',', '', $v['itemtax'])),
                'amount' => floatval(str_replace(',', '', $v['amount'])),
                'qty' => floatval(str_replace(',', '', $v['qty'])),
            ]);
        }, $data_items);
        OrdersItem::insert($data_items);
        $days = $input['days'];
        $days = array_map(function ($v) use($result) {
            return array_replace($v, [
                'order_id' => $result->id,
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'frequency' => $result->frequency,
                'expected_time' => Carbon::parse($v['expected_time'])->format('H:i:s'),
            ]);
        }, $days);
        DeliveryFreq::insert($days);
        if ($result) {
            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.Orderss.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Orders $Orders
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($order, array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if(in_array($key,['total','tax','taxable','subtotal']))
                $data[$key] = numberClean($val);
            if(in_array($key,['start_month','end_month']))
                $data[$key] = date_for_database($val);
        }
        if($order->status == 'draft'){
            $data['status'] = 'confirmed';
        }
        $order->update($data);

        $data_items = $input['data_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $order->items()->whereNotIn('id', $item_ids)->delete();
        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'amount', 'tax_rate','itemtax', 'qty']))
                    $item[$key] = floatval(str_replace(',', '', $val));
                
            }
            $order_item = OrdersItem::firstOrNew(['id' => $item['id']]);
            $order_item->fill(array_replace($item, ['order_id' => $order['id']]));
            if (!$order_item->id) unset($order_item->id);
            $order_item->save();
        }
        $days = $input['days'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['d_id']; }, $days);
        $order->deliver_days()->whereNotIn('id', $item_ids)->delete();
        // create or update items
        foreach($days as $item) {
            $item['id'] = $item['d_id'];
            unset($item['d_id']);
            foreach ($item as $key => $val) {
                if(in_array($key,['expected_time']))
                    $item[$key] = Carbon::parse($val)->format('H:i:s');
            }
            $deliver = DeliveryFreq::firstOrNew(['id' => $item['id']]);
            $deliver->fill(array_replace($item, ['order_id' => $order['id']]));

            if (!$deliver->id) unset($deliver->id);
            $deliver->save();
        }

    	if ($order)
            DB::commit();
            return true;

        throw new GeneralException(trans('exceptions.backend.Orderss.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Orders $Orders
     * @throws GeneralException
     * @return bool
     */
    public function delete($orders)
    {
        if ($orders->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.Orderss.delete_error'));
    }
}
