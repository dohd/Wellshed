<?php

namespace App\Http\Responses\Focus\quote;

use App\Models\additional\Additional;
use App\Models\bank\Bank;
use App\Models\classlist\Classlist;
use App\Models\Company\RecipientSetting;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\lead\Lead;
use App\Models\quote\Quote;
use Illuminate\Contracts\Support\Responsable;
use App\Models\fault\Fault;
use App\Models\hrm\Hrm;
use App\Models\product\ProductVariation;
use App\Models\productvariable\Productvariable;
use App\Models\quote_note\QuoteNote;
use App\Models\template_quote\TemplateQuote;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\quote\Quote
     */
    protected $quote;

    /**
     * @param App\Models\quote\Quote $quote
     */
    public function __construct($quote)
    {
        $this->quote = $quote;
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
        $quote = $this->quote;
        $words['title'] = 'Edit Quote / ∑MTO';
        $revisions = range(1, 5);

        $banks = Bank::all();
        $leads = Lead::where('status', 0)->orderBy('id', 'DESC')->get();
        $additionals = Additional::all();
        $price_customers = Customer::whereHas('products')->get(['id', 'company']);

        $lastquote = new Quote;
        $lastquote->tid = $quote->query()->where('ins', $quote->ins)->where('bank_id', 0)->max('tid');
        $lastpi = new Quote;
        $lastpi->tid = $quote->query()->where('ins', $quote->ins)->where('bank_id', '>', 0)->max('tid');

        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'lead'], $quote->ins);
        $equipments = $quote->equipments()->orderBy('row_index_id', 'ASC')->get();
        $faults = Fault::all(['name']);
        $productvariables = Productvariable::all();
        $employees = Hrm::all();

        $templateQuotes = TemplateQuote::all();
        $quote_pi_limit = RecipientSetting::where('type','budget_limit')->first();
        $products = ProductVariation::all();

        $currencyList = Currency::all();
        $classlists = Classlist::get();
        $quote_notes = QuoteNote::get();

        $common_params = ['quote_notes', 'classlists', 'currencyList' ,'templateQuotes','lastquote', 'quote', 'leads', 'words', 'additionals', 'price_customers', 'prefixes','equipments','faults','productvariables', 'employees','quote_pi_limit','products'];

        // copy quote to quote
        if (request('task') == 'quote_to_quote') {
            $words['title'] = 'Copy Quote to Quote';

            return view('focus.quotes.edit')
                ->with(compact(...$common_params))
                ->with(bill_helper(2, 4));
        }
        // copy quote to pi
        if (request('task') == 'quote_to_pi') {
            $words['title'] = 'Copy Quote to PI';
            $lastquote = $lastpi;

            return view('focus.quotes.edit')
                ->with(compact('banks', ...$common_params))
                ->with(bill_helper(2, 4));
        }
        // copy pi to pi
        if (request('task') == 'pi_to_pi') {
            $words['title'] = 'Copy PI to PI';
            $lastquote = $lastpi;

            return view('focus.quotes.edit')
                ->with(compact('banks', ...$common_params))
                ->with(bill_helper(2, 4));
        }
        // copy pi to quote
        if (request('task') == 'pi_to_quote') {
            $words['title'] = 'Copy PI to Quote';

            return view('focus.quotes.edit')
                ->with(compact(...$common_params))
                ->with(bill_helper(2, 4));
        }

        // append previous lead when editing
        $leads[] = Lead::find($quote->lead_id);
        $words['edit_mode'] = true;

        // edit proforma invoice
        if ($quote->bank_id) {    
            $words['title'] = 'Edit Proforma Invoice';

            return view('focus.quotes.edit')
                ->with(compact('banks','revisions', ...$common_params))
                ->with(bill_helper(2, 4));
        }
        // edit quote
        return view('focus.quotes.edit')
            ->with(compact('revisions', ...$common_params))
            ->with(bill_helper(2, 4));
    }
}
