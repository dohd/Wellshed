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
namespace App\Http\Controllers\Focus\quote_note;

use App\Models\quote_note\QuoteNote;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;


/**
 * QuoteNotesController
 */
class QuoteNotesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\quote_note\Request $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.quote_notes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Createquote_noteRequestNamespace $request
     * @return \App\Http\Responses\Focus\quote_note\CreateResponse
     */
    public function create()
    {
        return view('focus.quote_notes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $input = $request->only(['title', 'description']);
        $input['ins'] = auth()->user()->ins;
        // dd($input);
        //Create the model using repository create method
        try {
            QuoteNote::create($input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating Quote Note', $th);
        }
        
        //return with successfull message
        return new RedirectResponse(route('biller.quote_notes.index'), ['flash_success' => 'Quote Note Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\quote_note\quote_note $quote_note
     * @param Editquote_noteRequestNamespace $request
     * @return \App\Http\Responses\Focus\quote_note\EditResponse
     */
    public function edit(QuoteNote $quote_note)
    {
        return view('focus.quote_notes.edit', compact('quote_note'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Updatequote_noteRequestNamespace $request
     * @param App\Models\quote_note\quote_note $quote_note
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, QuoteNote $quote_note)
    {
        //Input received from the request
        $input = $request->only(['title', 'description']);
        //Update the model using repository update method
        try {
            $quote_note->update($input);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Updating Quote Note', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.quote_notes.index'), ['flash_success' => 'Quote Note Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletequote_noteRequestNamespace $request
     * @param App\Models\quote_note\quote_note $quote_note
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(QuoteNote $quote_note)
    {
        //Calling the delete method on repository
        try {
            $quote_note->delete();
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Deleting Quote Note', $th);
        }
        //returning with successfull message
        return new RedirectResponse(route('biller.quote_notes.index'), ['flash_success' => 'Quote Note Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deletequote_noteRequestNamespace $request
     * @param App\Models\quote_note\quote_note $quote_note
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(QuoteNote $quote_note, Request $request)
    {

        //returning with successfull message
        return new ViewResponse('focus.quote_notes.view', compact('quote_note'));
    }

    public function get_notes(Request $request)
    {
        if($request->footer_id){
            $quote_notes = QuoteNote::find($request->footer_id);
        }else if($request->header_id){
            $quote_notes = QuoteNote::find($request->header_id);
        }
        return response()->json($quote_notes);
    }

}
