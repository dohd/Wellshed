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

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\quote_note\QuoteNote;
use Yajra\DataTables\Facades\DataTables;


/**
 * Class quote_notesTableController.
 */
class QuoteNotesTableController extends Controller
{
  

    /**
     * This method return the data of the model
     * @param Managequote_noteRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = QuoteNote::get();
        
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('title', function ($quote_note) {
                 return $quote_note->title;
            })
            ->addColumn('description', function ($quote_note) {
                return $quote_note->description;
            })
            ->addColumn('created_at', function ($quote_note) {
                return Carbon::parse($quote_note->created_at)->toDateString();
            })
            ->addColumn('actions', function ($quote_note) {
                return $quote_note->action_buttons;
            })
            ->make(true);
    }
}
