<?php

namespace App\Http\Responses\Focus\quote;

use App\Models\Access\User\User;
use App\Models\additional\Additional;
use App\Models\bank\Bank;
use App\Models\classlist\Classlist;
use App\Models\Company\RecipientSetting;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\quote\Quote;
use Illuminate\Contracts\Support\Responsable;
use App\Models\lead\Lead;
use App\Models\fault\Fault;
use App\Models\hrm\Hrm;
use App\Models\product\ProductVariation;
use App\Models\quote_note\QuoteNote;
use App\Models\template_quote\TemplateQuote;

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

        $words['title'] = 'Quote / ∑MTO';
        if (request('doc_type') == 'maintenance') $words['title'] = 'Maintenance Quote';
            
        $leads = Lead::where('status', 0)->orderBy('id', 'desc')->get();
        $additionals = Additional::all();
        $price_customers = Customer::whereHas('products')->get(['id', 'company']);
        $faults = Fault::all(['name']);
        $employees = Hrm::all();
        $classlists = Classlist::get();

        $templateQuotes = TemplateQuote::all();
        $quote_pi_limit = RecipientSetting::where('type','budget_limit')->first();
        $products = ProductVariation::all();
        $currencyList = Currency::all();
        $quote_notes = QuoteNote::get();

        $common_params = ['quote_notes','classlists', 'currencyList' ,'templateQuotes','lastquote','leads', 'words', 'additionals', 'price_customers', 'prefixes','faults', 'employees','quote_pi_limit','products'];

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
        return view('focus.quotes.create', compact(...$common_params))
            ->with(bill_helper(2, 4));
    }
}
