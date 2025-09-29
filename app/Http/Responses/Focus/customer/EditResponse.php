<?php

namespace App\Http\Responses\Focus\customer;

use App\Models\account\Account;
use App\Models\customergroup\Customergroup;
use App\Models\customergroup\CustomerGroupEntry;
use App\Models\customfield\Customfield;
use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\items\CustomEntry;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\customer\Customer
     */
    protected $customer;

    /**
     * @param App\Models\customer\Customer $customer
     */
    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $customergroups = Customergroup::all();
        $current_group = CustomerGroupEntry::where('customer_id', '=', $this->customer->id)->get();
        $fields = Customfield::where('module_id', '=', '1')->get()->groupBy('field_type');
        $fields_raw = array();

        if (isset($fields['text'])) {
            foreach ($fields['text'] as $row) {
                $data = CustomEntry::where('custom_field_id', '=', $row['id'])->where('module', '=', 1)->where('rid', '=', $this->customer->id)->first();
                $fields_raw['text'][] = array('id' => $row['id'], 'name' => $row['name'], 'default_data' => $data['data']);
            }
        }
        if (isset($fields['number'])) {
            foreach ($fields['number'] as $row) {
                $data = CustomEntry::where('custom_field_id', '=', $row['id'])->where('module', '=', 1)->where('rid', '=', $this->customer->id)->first();
                $fields_raw['number'][] = array('id' => $row['id'], 'name' => $row['name'], 'default_data' => $data['data']);
            }
        }

        $fields = custom_fields($fields_raw);

        // load A/R accounts
        // $local_acc = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['receivable', 'loan']))
        //     ->whereHas('currency', fn($q) => $q->where('rate', 1))
        //     ->first(['id', 'holder', 'currency_id']);
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['receivable', 'loan']))
            ->whereHas('currency')
            ->get(['id', 'holder', 'currency_id']);
        // $accounts = collect(array_filter([$local_acc]))->merge($accounts);
        
        // Restrict currency to that of the initial invoice or receipt
        $invoice = Invoice::where('customer_id', $this->customer->id)->first();
        $receipt = InvoicePayment::where('customer_id', $this->customer->id)->first();
        // dd(@$invoice->currency_id, @$receipt->currency_id);
        if (@$invoice->currency_id) $accounts = $accounts->where('currency_id', $invoice->currency_id);
        elseif (@$receipt->currency_id) $accounts = $accounts->where('currency_id', $receipt->currency_id);
        
        return view('focus.customers.edit', compact('accounts', 'customergroups', 'fields', 'current_group'))
            ->with(['customer' => $this->customer,]);
    }
}
