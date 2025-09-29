<?php

namespace App\Repositories\Focus\invoice;

use App\Models\items\InvoiceItem;
use App\Models\invoice\Invoice;
use App\Exceptions\GeneralException;
use App\Models\misc\Misc;
use App\Models\project\Project;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

/**
 * Class InvoiceRepository.
 */
class InvoiceRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = Invoice::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        // date filter
        if (request('start_date') && request('end_date')) {
            $q->whereBetween('invoicedate', [
                date_for_database(request('start_date')), 
                date_for_database(request('end_date'))
            ]);
        }

        // Invoice Category
        $q->when(!empty(request('invoice_category')), function($q) {
            $q->where('account_id', request('invoice_category'));
        });

        // project filter (project view)
        $q->when(request('project_id'), function($q) {
            $q->where(function($q) {
                $q->whereHas('quotes', function($q) {
                    $q->whereHas('project', function($q) {
                        $q->where('projects.id', request('project_id'));
                    });
                });
                $q->orWhereHas('project', fn($q) => $q->where('projects.id', request('project_id')));
                $q->orWhereHas('boqValuation.project', fn($q) => $q->where('projects.id', request('project_id')));
            });
        });

        // customer and status filter
        $q->when(request('customer_id'), function ($q) {
            $q->where('customer_id', request('customer_id'));
        })->when(request('invoice_status'), function ($q) {
            $status = request('invoice_status');
            switch ($status) {
                case 'not yet due': 
                    $q->where('invoiceduedate', '>', date('Y-m-d'));
                    break;
                case 'due':    
                    $q->where('invoiceduedate', '<=', date('Y-m-d'));
                    break;                 
            }         
        })->when(request('payment_status'), function ($q) {
            $status = request('payment_status');
            switch ($status) {
                case 'unpaid':
                    $q->where('amountpaid', 0);
                    break; 
                case 'partially paid':
                    $q->whereColumn('amountpaid', '<', 'total')->where('amountpaid', '>', 0);
                    break; 
                case 'paid':
                    $q->whereColumn('amountpaid', '>=', 'total');
                    break; 
            }         
        });

        if (!request('customer_id')) {
            $q->limit(500);
        }

        return $q->orderBy('id','desc');
    }

    /**
     * Create project invoice
     * @throws \Exception
     */
    public function create_project_invoice(array $input)
    {
        // dd($input);
        $bill = $input['bill'];
        $duedate = $bill['invoicedate'] . ' + ' . $bill['validity'] . ' days';
        $bill['invoiceduedate'] = date_for_database($duedate);
        foreach ($bill as $key => $val) {
            if ($key == 'invoicedate') $bill[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax', 'taxable', 'fx_curr_rate'])) {
                $bill[$key] = numberClean($val);
            }
        }

        $tid = Invoice::max('tid');
        if ($bill['tid'] <= $tid) $bill['tid'] = $tid+1;
        //  forex values
        $fx_rate = @$bill['fx_curr_rate'];
        if ($fx_rate > 1) {
            $bill = array_replace($bill, [
                'fx_taxable' => round($bill['taxable'] * $fx_rate, 4),
                'fx_subtotal' => round($bill['subtotal'] * $fx_rate, 4),
                'fx_tax' => round($bill['tax'] * $fx_rate, 4),
                'fx_total' => round($bill['total'] * $fx_rate, 4),
            ]);
        }

        DB::beginTransaction();
        
        // create invoice
        $result = Invoice::create($bill);
        $result->update(['currency_id' => $result->customer->currency_id]);
        
        // create invoice items
        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            foreach ($item as $j => $value) {
                if (in_array($j, ['tax_rate', 'product_price', 'product_tax', 'product_subtotal', 'product_amount'])) {
                    $item[$j] = floatval(str_replace(',', '', $value));
                }
            }
            // forex values
            $fx_rate = $result->fx_curr_rate;
            if ($fx_rate > 1) {
                $item = array_replace($item, [
                    'fx_curr_rate' => $fx_rate,
                    'fx_product_tax' => round($item['product_tax'] * $fx_rate, 4),
                    'fx_product_price' => round($item['product_price'] * $fx_rate, 4),
                    'fx_product_subtotal' => round($item['product_subtotal'] * $fx_rate, 4),
                    'fx_product_amount' => round($item['product_amount'] * $fx_rate, 4),
                ]);
            }

            $bill_items[$k] = array_replace($item, [
                'invoice_id' => $result->id,
            ]);
        }
        InvoiceItem::insert($bill_items);

        // update Quote or PI invoice status
        foreach ($result->products as $key => $item) {
            $quote = $item->quote;
            if ($quote) $quote->update(['invoiced' => 'Yes']);
            if ($quote && $key == 0) $result->update(['currency_id' => $quote->currency_id]);
        }

        // Complete Quoted Project (autoclose)
        $project = Project::whereHas('quotes', fn($q) => $q->whereHas('invoice', fn($q) => $q->where('invoices.id', $result->id)))->first();
        $misc = Misc::where('name', 'LIKE', '%completed%')->first();
        if ($project && $misc) $project->update(['status' => $misc->id, 'end_note' => 'Auto-completion status on invoicing']);

        /** accounting */
        $this->post_invoice($result);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * Update Project Invoice
     */
    public function update_project_invoice($invoice, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $bill = $input['bill'];
        $duedate = $bill['invoicedate'] . ' + ' . $bill['validity'] . ' days';
        $bill['invoiceduedate'] = date_for_database($duedate);
        foreach ($bill as $key => $val) {
            if ($key == 'invoicedate') $bill[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax', 'taxable', 'fx_curr_rate'])) {
                $bill[$key] = numberClean($val);
            }
        }

        //  forex values
        $fx_rate = @$bill['fx_curr_rate'];
        if ($fx_rate > 1) {
            $bill = array_replace($bill, [
                'fx_taxable' => round($bill['taxable'] * $fx_rate, 4),
                'fx_subtotal' => round($bill['subtotal'] * $fx_rate, 4),
                'fx_tax' => round($bill['tax'] * $fx_rate, 4),
                'fx_total' => round($bill['total'] * $fx_rate, 4),
            ]);
        }

        $invoice->update($bill);

        // update invoice items
        $bill_items = $input['bill_items'];
        foreach ($bill_items as $k => $item) {
            $item = array_replace($item, [
                'id' => $item['id'],
                'reference' => @$item['reference'] ?: '', 
                'description' => $item['description'],
                'tax_rate' => numberClean($item['tax_rate']),
                'product_tax' => floatval(str_replace(',', '', $item['product_tax'])),
                'product_price' => floatval(str_replace(',', '', $item['product_price'])),
                'product_subtotal' => floatval(str_replace(',', '', $item['product_subtotal'])),
                'product_amount' => floatval(str_replace(',', '', $item['product_amount'])),
            ]);
            // forex values
            $fx_rate = $invoice->fx_curr_rate;
            if ($fx_rate > 1) {
                $item = array_replace($item, [
                    'fx_curr_rate' => $fx_rate,
                    'fx_product_tax' => round($item['product_tax'] * $fx_rate, 4),
                    'fx_product_price' => round($item['product_price'] * $fx_rate, 4),
                    'fx_product_subtotal' => round($item['product_subtotal'] * $fx_rate, 4),
                    'fx_product_amount' => round($item['product_amount'] * $fx_rate, 4),
                ]);
            }
            $bill_items[$k] = $item;
        }
        Batch::update(new InvoiceItem, $bill_items, 'id');

        // update Quote or PI invoice status
        foreach ($invoice->products as $item) {
            if ($item->quote) $item->quote->update(['invoiced' => 'Yes']);
        }

        // Complete Quoted Project (autoclose)
        $project = Project::whereHas('quotes', fn($q) => $q->whereHas('invoice', fn($q) => $q->where('invoices.id', $invoice->id)))->first();
        $misc = Misc::where('name', 'LIKE', '%completed%')->first();
        if ($project && $misc) $project->update(['status' => $misc->id, 'end_note' => 'Auto-completion status on invoicing']);

        /**accounting */
        $invoice->transactions()->delete();
        $invoice['is_update'] = true;
        $this->post_invoice($invoice);

        if ($bill) {
            DB::commit();
            return $invoice;  
        }
    }

    /**
     * Delete Project Invoice
     *
     * @param Invoice $invoice
     * @return bool
     * @throws GeneralException
     */
    public function delete($invoice)
    {
        if ($invoice->payments()->exists()) {
            foreach ($invoice->payments as $pmt_item) {
                $tids[] = @$pmt_item->paid_invoice->tid ?: '';
            }
            throw ValidationException::withMessages(['Invoice is linked to payments: (' . implode(', ', $tids) . ')']);
        }
        // check if invoice is attached to project
        if ($invoice->project) throw ValidationException::withMessages(['Invoice is attached to Project No: ' . (string) $invoice->project->tid]);
            
        DB::beginTransaction();
        
        // update Quote or PI invoice status
        foreach ($invoice->products as $item) {
            if ($item->quote) $item->quote->update(['invoiced' => 'No']);
        }
        // delete POS payment
        if ($invoice->product_expense_total > 0 && $invoice->payments()->exists()) {
            $pmt_item = $invoice->payments()->first();
            if ($pmt_item && $pmt_item->paid_invoice) $pmt_item->paid_invoice()->delete();
            $invoice->payments()->delete();
        }
        $invoice->transactions()->delete();
        $invoice->products()->delete();

        if ($invoice->delete()) {
            DB::commit();
            return true;
        }
    }
}
