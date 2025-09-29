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
namespace App\Http\Controllers\Focus\message_template;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Focus\message_template\MessageTemplateRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class MessageTemplatesTableController.
 */
class MessageTemplatesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var MessageTemplateRepository
     */
    protected $message_template;

    /**
     * contructor to initialize repository object
     * @param MessageTemplateRepository $message_template ;
     */
    public function __construct(MessageTemplateRepository $message_template)
    {
        $this->message_template = $message_template;
    }

    /**
     * This method return the data of the model
     * @param Managemessage_templateRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->message_template->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('type', function ($message_template) {
                 return str_replace('_',' ',ucfirst($message_template->type));
            })
            ->addColumn('user_type', function ($message_template) {
                 return ucfirst($message_template->user_type);
            })
            ->addColumn('created_at', function ($message_template) {
                return Carbon::parse($message_template->created_at)->toDateString();
            })
            ->addColumn('actions', function ($message_template) {
                return $message_template->action_buttons;
            })
            ->make(true);
    }
}
