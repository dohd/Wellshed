<?php

namespace App\Http\Responses\Focus\boq;

use App\Models\additional\Additional;
use App\Models\boq\BoQ;
use App\Models\boq\BoQSheet;
use App\Models\customer\Customer;
use App\Models\lead\Lead;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\boq\boq
     */
    protected $boqs;

    /**
     * @param App\Models\boq\boq $boqs
     */
    public function __construct($boqs)
    {
        $this->boqs = BoQ::  // Removes the global scope for 'ins'
        with([
            'sheets' => function($query) {
                $query->withoutGlobalScope('ins');  // Remove 'ins' scope for sheets
            },
            'sheets.items' => function($query) {
                $query->withoutGlobalScope('ins');  // Remove 'ins' scope for items
            },
            'products'
        ])->find($boqs->id);
    
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
        $additionals = Additional::all();
        $customers = Customer::all();
        $prefixes = prefixesArray(['quote', 'lead'], auth()->user()->ins);
        $leads = Lead::orderBy('id', 'desc')->get();
        $boq_id = $this->boqs->id;
        $boq_sheets = BoQSheet::whereHas('boqs', function($q) use($boq_id){
            $q->where('boq_id', $boq_id);
        })
        ->get();
        return view('focus.boqs.edit', compact('additionals','customers','leads','prefixes','boq_sheets'))->with([
            'boqs' => $this->boqs
        ]);
    }
}