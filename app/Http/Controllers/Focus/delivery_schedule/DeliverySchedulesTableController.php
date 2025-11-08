<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */
namespace App\Http\Controllers\Focus\delivery_schedule;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\delivery_schedule\DeliverySchedule;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
/**
 * Class DeliverySchedulesTableController.
 */
class DeliverySchedulesTableController extends Controller
{
    

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        // Build base query with relationships for filtering/columns
        $query = DeliverySchedule::query()
            ->with([
                'order:id,tid,customer_id',
                'order.customer:id,company',
                'delivery_frequency:id,delivery_days',
            ]);

        // -------------------- Filters --------------------

        // Delivery date filter: exact or range (start_date / end_date)
        if ($request->filled('delivery_date')) {
            $date = $this->safeDate($request->input('delivery_date'));
            if ($date) {
                $query->whereDate('delivery_date', $date->toDateString());
            }
        } elseif ($request->filled('start_date') || $request->filled('end_date')) {
            $start = $this->safeDate($request->input('start_date'));
            $end   = $this->safeDate($request->input('end_date'));

            if ($start && $end) {
                $query->whereBetween('delivery_date', [$start->toDateString(), $end->toDateString()]);
            } elseif ($start) {
                $query->whereDate('delivery_date', '>=', $start->toDateString());
            } elseif ($end) {
                $query->whereDate('delivery_date', '<=', $end->toDateString());
            }
        }

        // Order No filter (supports "ORD-123" or "123")
        if ($request->filled('order_no')) {
            $needle = trim($request->input('order_no'));

            $query->whereHas('order', function ($oq) use ($needle) {
                $oq->where('id', $needle);
            });
        }

        // Customer filter (company name)
        if ($request->filled('customer')) {
            $cust = trim($request->input('customer'));
            $query->whereHas('order.customer', function ($cq) use ($cust) {
                $cq->where('id', $cust);
            });
        }

        // Delivery Days filter (e.g., "Mon,Wed,Fri", "Daily")
        if ($request->filled('delivery_days')) {
            $days = trim($request->input('delivery_days'));
            $query->whereHas('delivery_frequency', function ($fq) use ($days) {
                $fq->where('delivery_days', 'like', "%{$days}%");
            });
        }

        // -------------------- DataTables --------------------
        $startFrom = (int) $request->input('start', 0); // for manual index alias
        $counter = $startFrom;

        return DataTables::of($query)
            // If your frontend expects 'DT_Row_Index', add a manual index column.
            ->addColumn('DT_Row_Index', function () use (&$counter) {
                $counter++;
                return $counter;
            })
            ->addColumn('tid', function ($ds) {
                // DS-<tid>
                return gen4tid('DS-', $ds->tid);
            })
            ->addColumn('order', function ($ds) {
                // ORD-<tid> if order exists
                return $ds->order ? gen4tid('ORD-', $ds->order->tid) : '';
            })
            ->addColumn('customer', function ($ds) {
                $customer = optional($ds->order)->customer ?? $ds->customer;
                return $customer->company ?? $customer->name ?? '';
            })
            ->addColumn('delivery_days', function ($ds) {
                return $ds->delivery_frequency ? $ds->delivery_frequency->delivery_days : '';
            })
            ->addColumn('delivery_date', function ($ds) {
                $date = Carbon::parse($ds->delivery_date);
                return $date->format('D, d M Y') . ' (' . $date->format('l') . ')';
            })
            ->addColumn('status', function ($ds) {
                return ucfirst((string) $ds->status);
            })
            ->addColumn('location', function ($ds) {
                return $ds->location ? $ds->location->sub_zone_name : '';
            })
            ->addColumn('actions', function ($ds) {
                // use your accessor / presenter
                return $ds->action_buttons;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    protected function safeDate($value): ?Carbon
    {
        if (!$value) return null;
        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

}
