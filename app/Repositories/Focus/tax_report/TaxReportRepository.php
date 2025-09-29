<?php

namespace App\Repositories\Focus\tax_report;

use App\Exceptions\GeneralException;
use App\Models\items\TaxReportItem;
use App\Models\tax_prn\TaxPrn;
use App\Models\tax_report\TaxReport;
use App\Repositories\BaseRepository;
use DateTime;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;


class TaxReportRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = TaxReport::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('record_month'), function($q) {
            $q->where('record_month', request('record_month'));
        })->when(request('return_month'), function($q) {
            $q->where('return_month', request('return_month'));
        })->when(request('tax_group') != '', function ($q) {
            $q->where('tax_group', request('tax_group'));
        });
    
        return $q;
    }

    // sales returns
    public function getForSalesDataTable()
    {
        $q = TaxReportItem::query()->where(function ($q) {
            $q->whereHas('invoice')->orWhereHas('credit_note');
        })->where('is_filed', request('is_filed', 1));
        
        $q->when(request('tax_report_id'), function ($q) {
            $q->whereHas('tax_report', fn($q) => $q->where('id', request('tax_report_id')));
        })->when(request('record_month'), function ($q) {
            $q->whereHas('tax_report', fn($q) => $q->where('record_month', request('record_month')));
        })->when(request('return_month'), function ($q) {
            $q->whereHas('tax_report', fn($q) => $q->where('return_month', request('return_month')));
        })->when(request('tax_group') != '', function ($q) {
            $q->whereHas('tax_report', fn($q) => $q->where('tax_group', request('tax_group')));
        });

        return $q->with(['invoice', 'credit_note'])->get();
    }
    // purchase returns
    public function getForPurchasesDataTable()
    {
        $q = TaxReportItem::query()->where(function ($q) {
            $q->whereHas('purchase')->orWhereHas('debit_note');
        })->where('is_filed', request('is_filed', 1));

        $q->when(request('tax_report_id'), function ($q) {
            $q->whereHas('tax_report', fn($q) => $q->where('id', request('tax_report_id')));
        })->when(request('record_month'), function ($q) {
            $q->whereHas('tax_report', fn($q) => $q->where('record_month', request('record_month')));
        })->when(request('return_month'), function ($q) {
            $q->whereHas('tax_report', fn($q) => $q->where('return_month', request('return_month')));
        })->when(request('tax_group') != '', function ($q) {
            $q->whereHas('tax_report', fn($q) => $q->where('tax_group', request('tax_group')));
        });
        
        return $q->with(['purchase', 'debit_note'])->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return TaxReport $tax_report
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data_keys = [
            'sale_subtotal', 'sale_tax', 'sale_total', 'purchase_subtotal', 'purchase_tax', 
            'purchase_total',
        ];
        foreach ($input as $key => $val) {
            if (in_array($key, $data_keys)) $input[$key] = numberClean($val);
            if (in_array($key, ['record_month', 'return_month'])) {
                $date = DateTime::createFromFormat('m-Y', $input[$key]);
                if ($date) $input[$key] = $date->format('m-Y');
                else throw ValidationException::withMessages(['Valid date format required mm-YYYY']);
            }
        }
        
        // report data
        $report_data = Arr::only($input, [
            'record_month', 'tax_group', 'return_month', 'note', 'sale_month', 'sale_tax_rate', 
            'purchase_month', 'purchase_tax_rate', ...$data_keys
        ]);
        $result = TaxReport::create($report_data);

        // sale data items
        $sale_data_items = Arr::only($input, ['sale_id', 'sale_type', 'sale_is_filed']);
        $sale_data_items = modify_array($sale_data_items);
        if ($sale_data_items) {
            $sale_data_items = array_map(fn($v) => [
                'tax_report_id' => $result->id,
                'invoice_id' => $v['sale_type'] == 'invoice'? $v['sale_id'] : null,
                'credit_note_id' => $v['sale_type'] == 'credit_note'? $v['sale_id'] : null,
                'is_filed' => $v['sale_is_filed'],
            ], $sale_data_items);

            // delete previously removed items on consecutive filing
            $invoice_ids = array_map(fn($v) => $v['invoice_id'], $sale_data_items);
            TaxReportItem::whereIn('invoice_id', $invoice_ids)->where('is_filed', 0)->delete();
            $credit_note_ids = array_map(fn($v) => $v['credit_note_id'], $sale_data_items);
            TaxReportItem::whereIn('credit_note_id', $credit_note_ids)->where('is_filed', 0)->delete();

            TaxReportItem::insert($sale_data_items);
        }

        // purchase data items
        $purchase_data_items = Arr::only($input, ['purchase_id', 'purchase_type', 'purchase_is_filed']);
        $purchase_data_items = modify_array($purchase_data_items);
        if ($purchase_data_items) {
            $purchase_data_items = array_map(fn($v) => [
                'tax_report_id' => $result->id,
                'purchase_id' => $v['purchase_type'] == 'purchase'? $v['purchase_id'] : null,
                'debit_note_id' => $v['purchase_type'] == 'debit_note'? $v['purchase_id'] : null,
                'is_filed' => empty($v['purchase_is_filed']) ? "1" : $v['purchase_is_filed'],
            ], $purchase_data_items);

            // delete previously removed items on consecutive filing
            $purchase_ids = array_map(fn($v) => $v['purchase_id'], $purchase_data_items);
            TaxReportItem::whereIn('purchase_id', $purchase_ids)->where('is_filed', 0)->delete();
            $debit_note_ids = array_map(fn($v) => $v['debit_note_id'], $purchase_data_items);
            TaxReportItem::whereIn('debit_note_id', $debit_note_ids)->where('is_filed', 0)->delete();

            TaxReportItem::insert($purchase_data_items);
        }
        
        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param TaxReport $tax_report
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(TaxReport $tax_report, array $input)
    {
        // dd($input);
        if ($tax_report->return_month) {
            $is_exists = TaxPrn::where('return_month', 'LIKE', "%{$tax_report->return_month}%")->exists();
            if ($is_exists) throw ValidationException::withMessages(['Not allowed. Filed Tax Returns have been acknowledged']);
        }
    
        DB::beginTransaction();

        $data_keys = [
            'sale_subtotal', 'sale_tax', 'sale_total', 'purchase_subtotal', 
            'purchase_tax', 'purchase_total',
        ];
        foreach ($input as $key => $val) {
            if (in_array($key, $data_keys)) $input[$key] = numberClean($val);
            if (in_array($key, ['record_month', 'return_month'])) {
                $date = DateTime::createFromFormat('m-Y', $input[$key]);
                if ($date) $input[$key] = $date->format('m-Y');
                else throw ValidationException::withMessages(['Valid date format required mm-YYYY']);
            }
        }

        // report data
        $report_data = Arr::only($input, [
            'record_month', 'tax_group', 'return_month', 'note', 'sale_month', 'sale_tax_rate', 
            'purchase_month', 'purchase_tax_rate', ...$data_keys
        ]);
        $result = $tax_report->update($report_data);

        // sale data items
        $sale_data_items = Arr::only($input, ['sale_item_id', 'sale_is_filed']);
        $sale_data_items = modify_array($sale_data_items);
        if ($sale_data_items) {
            $sale_data_items = array_map(fn($v) => [
                'id' => $v['sale_item_id'], 
                'is_filed' => $v['sale_is_filed'],
            ], $sale_data_items);
            Batch::update(new TaxReportItem, $sale_data_items, 'id');
        }
        
        // purchase data items
        $purchase_data_items = Arr::only($input, ['purchase_item_id', 'purchase_is_filed']);
        $purchase_data_items = modify_array($purchase_data_items);
        if ($purchase_data_items) {
            $purchase_data_items = array_map(fn($v) => [
                'id' => $v['purchase_item_id'], 
                'is_filed' => $v['purchase_is_filed'],
            ], $purchase_data_items);
            Batch::update(new TaxReportItem, $purchase_data_items, 'id');
        }
        
        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param TaxReport $tax_report
     * @throws GeneralException
     * @return bool
     */
    public function delete(TaxReport $tax_report)
    {
        if ($tax_report->return_month) {
            $is_exists = TaxPrn::where('return_month', 'LIKE', "%{$tax_report->return_month}%")->exists();
            if ($is_exists) throw ValidationException::withMessages(['Not allowed. Filed Tax Returns have been acknowledged']);
        }

        if ($tax_report->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }
}
