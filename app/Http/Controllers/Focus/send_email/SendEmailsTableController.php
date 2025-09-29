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
namespace App\Http\Controllers\Focus\send_email;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\send_email\SendEmailRepository;

/**
 * Class SendEmailsTableController.
 */
class SendEmailsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var SendEmailRepository
     */
    protected $send_email;

    /**
     * contructor to initialize repository object
     * @param SendEmailRepository $send_email ;
     */
    public function __construct(SendEmailRepository $send_email)
    {
        $this->send_email = $send_email;
    }

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->send_email->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('subject', function ($send_email) {
                 return $send_email->subject;
            })
            ->addColumn('text_email', function ($send_email) {
                // return $send_email->text_email;
                return $this->maskPasswordInMessage($send_email->text_email);
            })
            ->addColumn('status', function ($send_email) {
                $status = '';
                if($send_email->status == 'sent'){
                    $status = '<span style="color: green;"><b>Sent</b></span>';
                }else{

                    $status = '<span style="color: red;"><b>Not Sent</b></span>';
                }
                return $status;
            })
            ->addColumn('created_at', function ($send_email) {
                return Carbon::parse($send_email->created_at)->toDateString();
            })
            ->addColumn('actions', function ($send_email) {
                $btn = '';
                if(access()->allow('manage-send_email')) $btn.= '<a href="' . route('biller.send_emails.show', $send_email)  . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i  class="fa fa-eye"></i></a>';
                if (access()->allow('edit-send_email') && $send_email->delivery_type == 'schedule' && $send_email->scheduled_date > now()->toDateString()) {
                    $btn.= '<a href="' . route('biller.send_emails.edit', $send_email->id) . '" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-pencil "></i></a>';
                    $btn .= '<a href="' . route('biller.send_emails.destroy', $send_email->id) . '" 
                        class="btn btn-danger round" data-method="delete"
                        data-trans-button-cancel="' . trans('buttons.general.cancel') . '"
                        data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '"
                        data-trans-title="' . trans('strings.backend.general.are_you_sure') . '" data-toggle="tooltip" data-placement="top" title="Delete"
                    >
                        <i  class="fa fa-trash"></i>
                    </a>';
                }
                return $btn;
            })
            ->make(true);
    }
    public function maskPasswordInMessage($message)
    {
        // Use regex to find the password in the message
        $pattern = '/Password:\s([\w@#$%^&*]+)/';
        return preg_replace_callback($pattern, function ($matches) {
            $password = $matches[1];
            $maskedPassword = str_repeat('*', strlen($password) - 2) . substr($password, -2);
            return "Password: $maskedPassword";
        }, $message);
    }
}
