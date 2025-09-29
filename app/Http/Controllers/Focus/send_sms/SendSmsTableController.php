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
namespace App\Http\Controllers\Focus\send_sms;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Focus\general\RosesmsRepository;
use App\Repositories\Focus\send_sms\SendSmsRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class SendSmsTableController.
 */
class SendSmsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var send_smsRepository
     */
    protected $send_sms;

    /**
     * contructor to initialize repository object
     * @param send_smsRepository $send_sms ;
     */
    public function __construct(SendSmsRepository $send_sms)
    {
        $this->send_sms = $send_sms;
    }

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke()
    {

        //
        $core = $this->send_sms->getForDataTable();
        $good_worth = 0;
        foreach ($core as $send_sms) {
            $cost_per_160 = 0.6;
            $charCount = strlen($send_sms->subject); // Get the total character count
            $total = $send_sms->total_cost;
            $users = explode(',', $send_sms->phone_numbers); // Split phone numbers into an array
            
            // If the number of characters is not set to 0
            if(numberFormat($send_sms->characters) == 0){
                // Calculate the number of 160-character blocks, rounding up to cover any remaining characters
                $blocks = ceil($charCount / 160);
                // Calculate the total cost by multiplying the cost per block, the number of blocks, and the number of users
                $total = $cost_per_160 * $blocks * count($users);
            }
            $good_worth += $total;
                
        }
        $aggregate = ['good_worth' => numberFormat($good_worth)];
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('subject', function ($send_sms) {
                 return $this->maskPasswordInMessage($send_sms->subject);
            })
            ->addColumn('message_type', function ($send_sms) {
                return $send_sms->message_type;
            })
            ->addColumn('delivery_type', function ($send_sms) {
                return $send_sms->delivery_type;
            })
            ->addColumn('total_cost', function ($send_sms) {
                $cost_per_160 = 0.6;
                $charCount = strlen($send_sms->subject); // Get the total character count
                $total = $send_sms->total_cost;
                $users = explode(',', $send_sms->phone_numbers); // Split phone numbers into an array
                
                // If the number of characters is not set to 0
                if(numberFormat($send_sms->characters) == 0){
                    // Calculate the number of 160-character blocks, rounding up to cover any remaining characters
                    $blocks = ceil($charCount / 160);
                    // Calculate the total cost by multiplying the cost per block, the number of blocks, and the number of users
                    $total = $cost_per_160 * $blocks * count($users);
                }
                
                return numberFormat($total);
            })
            ->addColumn('status', function ($send_sms) {
                $status = '';
                // dd($send_sms->sms_callbacks, empty($send_sms->sms_callbacks));
                if($send_sms->sms_response){
                    // printlog();
                    if (count($send_sms->sms_response->sms_callbacks) > 0) {
                        $status = '<span style="color: green;"><b>Sent</b></span>';
                    } 
                    else {
                        $status = '<span style="color: red;"><b>Not Sent</b></span>';
                    }
                }
                
                
                return $status;
            })
            ->addColumn('sent_at', function ($send_sms) {
                $time = '';
                if($send_sms->delivery_type == 'now'){
                   $time = $send_sms->created_at;
                }else{
                    $time = $send_sms->scheduled_date;
                }
                
                
                return $time;
            })
            
            ->addColumn('created_at', function ($send_sms) {
                return Carbon::parse($send_sms->created_at)->toDateString();
            })
            ->addColumn('aggregate', function () use ($aggregate){
                return $aggregate;
            })
            ->addColumn('actions', function ($send_sms) {
                $btn = '';
                if(access()->allow('manage-sms_send')) $btn.= '<a href="' . route('biller.send_sms.show', $send_sms)  . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i  class="fa fa-eye"></i></a>';
                if (access()->allow('edit-sms_send') && $send_sms->delivery_type == 'schedule' && $send_sms->scheduled_date > now()->toDateString()) {
                    $btn.= '<a href="' . route('biller.send_sms.edit', $send_sms->id) . '" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-pencil "></i></a>';
                    $btn .= '<a href="' . route('biller.send_sms.destroy', $send_sms->id) . '" 
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
