<?php

namespace App\Repositories\Focus\pos;

use App\Models\account\Account;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\InvoiceItem;
use App\Models\items\InvoicePaymentItem;
use App\Models\product\ProductVariation;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use App\Repositories\Focus\product\ProductRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Class InvoiceRepository.
 */
class PosRepository extends BaseRepository
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

        return $q->get();
    }

    // generate product purchase price
    public function gen_purchase_price($id)
    {
        $product = ProductVariation::find($id);
        return (new ProductRepository)->eval_purchase_price($product->id, $product->qty, $product->purchase_price);
    }

    // generate product default unit of measure
    public function gen_unit_measure($id)
    {
        $variation = ProductVariation::find($id);
        $base_unit = $variation->product->units()->where('unit_type', 'base')->first();
        if (!$base_unit) throw ValidationException::withMessages(['please set product units!']);

        return ['code' => $base_unit->code, 'value' => $base_unit->base_ratio];
    }

    /**
     * Create POS Transaction
     */
    public function create(array $input)
    {
        foreach ($input as $key => $val) {
            if (in_array($key, ['invoicedate', 'invoiceduedate'])) 
                $input[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax', 'tax_id'])) 
                $input[$key] = numberClean($val);
            $item_keys = ['product_qty', 'product_price', 'product_tax', 'product_subtotal', 'total_tax'];
            if (in_array($key, $item_keys)) 
                $input[$key] = array_map(fn($v) => numberClean($v), $val);
        }
        
        DB::beginTransaction();

        // create invoice
        $data = Arr::only($input, ['invoicedate', 'invoiceduedate', 'subtotal', 'tax', 'total', 'customer_id', 'tax_id', 'notes', 'account_id', 'claimer_tax_pin', 'claimer_company']);
        $data = array_replace($data, ['tid' => Invoice::max('tid')+1, 'notes' => $data['notes'] ?: 'POS Counter Sale']);
        $result = Invoice::create($data);

        // create invoice items
        $data_items = Arr::only($input, ['product_id', 'product_name', 'product_qty', 'product_price', 'product_tax', 'product_subtotal', 'total_tax', 'unit_m']);
        $data_items = modify_array($data_items);
        // $data_items = array_filter($data_items, fn($v) => $v['product_qty']);
        // if (!$data_items) throw ValidationException::withMessages(['Cannot Invoice without product line items!']);
        foreach ($data_items as $key => $item) {
            $item = array_replace($item, [
                'invoice_id' => $result->id,
                // expense
                'product_purchase_price' => fifoCost($item['product_id']) ?: latestPurchaseCost($item['product_id']),
                'product_expense_amount' => $item['product_purchase_price'] * $item['product_qty'],
                // sale
                'total_tax' => $item['product_subtotal'] * $item['product_tax'] * 0.01,
                'product_amount' => $item['product_price'] * $item['product_qty'] * (1 + $item['product_tax'] * 0.01),
                'description' => $item['product_name'],
                'unit' => @$this->gen_unit_measure($v['product_id'])['code'],
                'unit_value' => $this->gen_unit_measure($v['product_id'])['value'],
            ]);
            unset($v['product_name'], $v['unit_m']);
            $data_items[$key] = $item;
        }
        InvoiceItem::insert($data_items);
        $result->update(['product_expense_total' => $result->products()->sum('product_expense_amount')]);

        /** accounting */
        $this->post_invoice($result);

        // on direct payment
        if ($input['is_pay']) {
            $this->generate_payment($input, $result);
        }

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * Generate POS Invoice Payment
     */
    public function generate_payment($input, $invoice)
    {
        $data_items = Arr::only($input, ['p_amount', 'p_method']);
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => numberClean($v['p_amount']) > 0);
        if (!$data_items) throw ValidationException::withMessages(['Payment confirmation details required!']);
        foreach ($data_items as $row) {
            // create payment deposit
            $payment_deposit = InvoicePayment::create([
                'tid' => InvoicePayment::max('tid')+1,
                'account_id' => $input['p_account'],
                'customer_id' => $invoice->customer_id,
                'date' => $invoice->invoicedate,
                'amount' => $invoice->total,
                'allocate_ttl' => $invoice->total,
                'reference' => $input['pmt_reference'],
                'payment_type' => 'per_invoice',
                'ins' => $invoice->ins,
                'user_id' => $invoice->user_id,
                'payment_mode' => $row['p_method'],
            ]);
            // create payment deposit item
            InvoicePaymentItem::create([
                'paidinvoice_id' => $payment_deposit->id,
                'invoice_id' => $invoice->id,
                'paid' => $payment_deposit->amount,
            ]);
            /**accounting */
            $this->post_invoice_deposit($payment_deposit);
        }
        // update invoice balances
        $this->updateInvoiceBalance([$invoice->id]);
    }
}
