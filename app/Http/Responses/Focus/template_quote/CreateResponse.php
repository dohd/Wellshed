<?php

namespace App\Http\Responses\Focus\template_quote;

use App\Models\Access\User\User;
use App\Models\additional\Additional;
use App\Models\bank\Bank;
use App\Models\customer\Customer;
use App\Models\quote\Quote;
use Illuminate\Contracts\Support\Responsable;
use App\Models\lead\Lead;
use App\Models\fault\Fault;
use App\Models\hrm\Hrm;

class CreateResponse implements Responsable
{
    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $ins = auth()->user()->ins;
        $lastquote = new Quote;
        $lastquote->tid = Quote::where('ins', $ins)->where('bank_id', 0)->max('tid');
        $prefixes = prefixesArray(['quote', 'lead'], $ins);

        $words['title'] = 'Quote';
        if (request('doc_type') == 'maintenance') $words['title'] = 'Maintenance Quote';
            
        $leads = Lead::where('status', 0)->orderBy('id', 'desc')->get();
        $additionals = Additional::all();
        $price_customers = Customer::whereHas('products')->get(['id', 'company']);
        $faults = Fault::all(['name']);
        $employees = Hrm::all();

        $common_params = ['lastquote','leads', 'words', 'additionals', 'price_customers', 'prefixes','faults', 'employees'];

        // create proforma invoice
        if (request('page') == 'pi') {
            $lastquote->tid = Quote::where('ins', $ins)->where('bank_id', '>', 0)->max('tid');
            $prefixes = prefixesArray(['proforma_invoice', 'lead'], $ins);

            $banks = Bank::all();
            $words['title'] = 'Proforma Invoice';
            if (request('doc_type') == 'maintenance') 
                $words['title'] = 'Maintenance Proforma Invoice';

            return view('focus.quotes.create', compact('banks', ...$common_params))
                ->with(bill_helper(2, 4));
        }
        // create quote
        return view('focus.template_quotes.create', compact(...$common_params))
            ->with(bill_helper(2, 4));
    }
}
