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
namespace App\Http\Controllers\Focus\prospect_question;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Focus\prospect_question\ProspectQuestionRepository;
use Yajra\DataTables\Facades\DataTables;


/**
 * Class ProspectQuestionsTableController.
 */
class ProspectQuestionsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProspectQuestionRepository
     */
    protected $prospect_question;

    /**
     * contructor to initialize repository object
     * @param ProspectQuestionRepository $prospect_question ;
     */
    public function __construct(ProspectQuestionRepository $prospect_question)
    {
        $this->prospect_question = $prospect_question;
    }

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->prospect_question->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('title', function ($prospect_question) {
                 return $prospect_question->title;
            })
            ->addColumn('description', function ($prospect_question) {
                return $prospect_question->description;
            })
            ->addColumn('created_at', function ($prospect_question) {
                return Carbon::parse($prospect_question->created_at)->toDateString();
            })
            ->addColumn('actions', function ($prospect_question) {
                return $prospect_question->action_buttons;
            })
            ->make(true);
    }
}
