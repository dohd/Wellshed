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

namespace App\Http\Controllers\Focus\prospectcallresolved;

use App\Http\Controllers\Controller;
use App\Models\hrm\Hrm;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\prospectcallresolved\ProspectCallResolvedRepository;

/**
 * Class BranchTableController.
 */
class ProspectsCallResolvedTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $prospectcallresolved;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(ProspectCallResolvedRepository $prospectcallresolved)
    {

        $this->prospectcallresolved = $prospectcallresolved;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->prospectcallresolved->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('date', function ($prospectcallresolved) {
                $date = '';
                if ($prospectcallresolved) {
                    $date = $prospectcallresolved->updated_at == null ? '-----' : $prospectcallresolved->updated_at;
                }

                return dateFormat($date);
            })
            ->addColumn('title', function ($prospectcallresolved) {
                $title = '';
                if ($prospectcallresolved->prospect) {
                    $title = $prospectcallresolved->prospect->title == null ? '-----' : $prospectcallresolved->prospect->title;
                }

                return $title;
            })
            ->addColumn('company', function ($prospectcallresolved) {
                $company = '';
                if ($prospectcallresolved->prospect) {
                    $company = $prospectcallresolved->prospect->company == null ? '-----' : $prospectcallresolved->prospect->company;
                }
                return $company;
            })
            ->addColumn('contact_person', function ($prospectcallresolved) {
                $contact_person = '';
                if ($prospectcallresolved->prospect) {
                    $contact_person = $prospectcallresolved->prospect->contact_person == null ? '-----' : $prospectcallresolved->prospect->contact_person;
                }
                return $contact_person;
            })
            ->addColumn('phone', function ($prospectcallresolved) {
                $phone = '';
                if ($prospectcallresolved->prospect) {
                    $phone = $prospectcallresolved->prospect->phone == null ? '-----' : $prospectcallresolved->prospect->phone;
                }
                return $phone;
            })

            ->addColumn('industry', function ($prospectcallresolved) {
                $client_industry = '';
                if ($prospectcallresolved->prospect) {
                    $client_industry = $prospectcallresolved->prospect->industry == null ? '-----' : $prospectcallresolved->prospect->industry;
                }
                return $client_industry;
            })
            ->addColumn('region', function ($prospectcallresolved) {
                $client_region = '';
                if ($prospectcallresolved->prospect) {
                    $client_region = $prospectcallresolved->prospect->region == null ? '-----' : $prospectcallresolved->prospect->region;
                }
                return $client_region;
            })
            ->addColumn('temperate', function ($prospectcallresolved) {
                $status = '';
                if ($prospectcallresolved->prospect) {
                    $status = $prospectcallresolved->prospect->temperate;
                }

                return $status;
            })
            ->addColumn('reminder_date', function ($prospectcallresolved) {
                $reminder_date = ''; // Default value if no reminder_date is found
            
                if ($prospectcallresolved->prospect) {
                    // Fetch the latest remark
                    $remark = $prospectcallresolved->prospect->remarks()->latest()->first();
            
                    if ($remark) {
                        // Get the reminder_date or default to '-----' if null
                        $reminder_date = $remark->reminder_date ?? '';
                    }
                }
            
                return $reminder_date;
            })
            
            ->addColumn('follow_up', function ($prospectcallresolved) {
                $status = '';
                $show = false;
                if ($prospectcallresolved->prospect) {
                    $status = $prospectcallresolved->prospect->call_status;
                    $show = true;
                    if ($status == 'called') {
                        $show = true;
                    } else if ($status == 'callednotavailable') {
                        $show = true;
                    } else {
                        $show = false;
                    }

                    $text = "";
                    $openstatus = $prospectcallresolved->prospect->status;
                    if ($openstatus == 'open') {
                        $text = "Follow up";
                    } else {
                        $text = "Show details";
                    }
                }
                return $show ? '<a id="follow" href="javascript:void(0)" class="btn btn-primary follow" data-id="' . $prospectcallresolved->prospect->id . '"   data-resolve="' . $prospectcallresolved->id . '">
                         ' . $text . '
                         </a>' : '<a id="call" href="javascript:void(0)" class="btn btn-primary" data-id="' . $prospectcallresolved->prospect_id . '" call-id="' . $prospectcallresolved->call_id . '" data-toggle="tooltip" data-resolve="' . $prospectcallresolved->id . '"  title="Call" >
                         <i  class="fa fa-vcard"></i>
                                  </a>';
            })


            ->addColumn('call_status', function ($prospectcallresolved) {
                $status = '';
                if ($prospectcallresolved->prospect) {
                    $status = $prospectcallresolved->prospect->call_status;
                    if ($status == 'notcalled') {
                        $status = "Not called";
                    } else if ($status == 'callednotpicked') {
                        $status = "Called Not Picked";
                    } else if ($status == 'calledrescheduled') {
                        $status = "Call Rescheduled";
                    } else if ($status == 'callednotavailable') {
                        $status = "Called Not Available";
                    } else {
                        $status = "Called";
                    }
                }
                return $status;
            })
            ->addColumn('status', function ($prospectcallresolved) {
                $status = '';
                if ($prospectcallresolved->prospect) {
                    $status = $prospectcallresolved->prospect->status;
                    if ($status == 'open') {
                        $status = "Open";
                    } else if ($status == 'won') {
                        $status = "Closed - Won";
                    } else {
                        $status = "Closed - Lost";
                    }
                }
                return $status;
            })
            ->addColumn('reason', function ($prospectcallresolved) {
                // Default value for reason
                $reason = '-----';
            
                if ($prospectcallresolved->prospect) {
                    // Fetch the latest remark
                    $remark = $prospectcallresolved->prospect->remarks()->latest()->first();
            
                    if ($remark) {
                        // Check if 'any_remarks' is null or not
                        $reason = $remark->any_remarks ?? '-----'; // Use '-----' if any_remarks is null
                    }
                }
            
                return $reason;
            })
            ->addColumn('latest_user', function ($prospectcallresolved) {
                // Default value for reason
                $user_name = '';
            
                if ($prospectcallresolved->prospect) {
                    // Fetch the latest remark
                    $remark = $prospectcallresolved->prospect->remarks()->latest()->first();
            
                    if ($remark) {
                        // Check if 'any_remarks' is null or not
                        $user = Hrm::find($remark->user_id);
                        $user_name = $user->fullname ?? ''; // Use '-----' if any_remarks is null
                    }
                }
            
                return $user_name;
            })
            ->addColumn('reason', function ($prospectcallresolved) {
                // Default value for reason
                $reason = '-----';
            
                if ($prospectcallresolved->prospect) {
                    // Fetch the latest remark
                    $remark = $prospectcallresolved->prospect->remarks()->latest()->first();
            
                    if ($remark) {
                        // Check if 'any_remarks' is null or not
                        $reason = $remark->any_remarks ?? '-----'; // Use '-----' if any_remarks is null
                    }
                }
            
                return $reason;
            })
            // ->addColumn('actions', function ($prospectcallresolved) {
            //     return $prospectcallresolved->action_buttons;
            // })
            ->make(true);
    }
}
