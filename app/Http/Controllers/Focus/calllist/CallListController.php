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

namespace App\Http\Controllers\Focus\calllist;

use App\Models\prospect\Prospect;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\prospect_call_list\ProspectCallListController;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\calllist\CreateResponse;
use App\Http\Responses\Focus\calllist\EditResponse;
use App\Repositories\Focus\calllist\CallListRepository;
use App\Http\Requests\Focus\calllist\CallListRequest;
use App\Models\Access\User\User;
use App\Models\branch\Branch;
use App\Models\calllist\CallList;
use App\Models\hrm\Hrm;
use App\Models\prospect_calllist\ProspectCallList;
use App\Models\prospect_question\ProspectQuestion;
use App\Models\prospect_reassign\ProspectReassign;
use DB;
use Illuminate\Support\Carbon;
use DateTime;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

/**
 * CallListController
 */
class CallListController extends Controller
{
    /**
     * variable to store the repository object
     * @var CallListRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param CallListRepository $repository ;
     */
    public function __construct(CallListRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        $employees = Hrm::all();
        $call_lists = CallList::select('title')->distinct()->pluck('title');
        return new ViewResponse('focus.prospects.calllist.index', compact('employees','call_lists'));
        //return new ViewResponse('focus.prospects.calllist.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\calllist\CreateResponse
     */
    public function create()
    {
        $direct = Prospect::where('call_status','notcalled')->where('category', 'direct')->whereDoesntHave('prospectcalllist')->count();
        $excel = Prospect::select(DB::raw('title,COUNT("*") AS count '))->groupBy('title')->where('call_status','notcalled')->where('category', 'excel')->whereDoesntHave('prospectcalllist')->get();
        $employees = Hrm::all();

        return view('focus.prospects.calllist.create', compact('direct', 'excel','employees'));
    }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param StoreProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\RedirectResponse
    //  */
    public function store(CallListRequest $request)
    {

        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files']);
        try {
            $res = $this->repository->create($data);
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error creating Calllist', $th);
        }
    
       
        
       return new RedirectResponse(route('biller.calllists.index'), ['flash_success' => 'CallList Created successfully!!']);
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param \App\Models\calllist\CallList $calllist
    //  * @param EditProductcategoryRequestNamespace $request
    //  * @return \App\Http\Responses\Focus\productcategory\EditResponse
    //  */
    public function edit(CallList $calllist)
    {
        $branches = Branch::get(['id', 'name', 'customer_id']);
        $direct = Prospect::where('call_status','notcalled')->where('category', 'direct')->whereDoesntHave('prospectcalllist')->count();
        $excel = Prospect::select(DB::raw('title,COUNT("*") AS count '))->groupBy('title')->where('call_status','notcalled')->where('category', 'excel')->whereDoesntHave('prospectcalllist')->get();
        $employees = Hrm::all();
        $calllist->title = Str::before($calllist['title'], ' -');


        return new EditResponse('focus.prospects.calllist.edit', compact('calllist', 'branches','direct', 'excel','employees'));
    }
    public function update(Request $request, CallList $calllist)
    {
        // dd($calllist, $request->all());
        $data = $request->only(['prospects_number','start_date','end_date','employee_id']);
        try {
            $this->repository->update($calllist, compact('data'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error updating Calllist', $th);
        }

        return new RedirectResponse(route('biller.calllists.index'), ['flash_success' => 'CallList Updated successfully!!']);
    }
    public function destroy(CallList $calllist)
    {
        $this->repository->delete($calllist);

        return new RedirectResponse(route('biller.calllists.index'), ['flash_success' => 'CallList Deleted Successfully']);
    }
    public function show(CallList $calllist)
    {
     
        return new ViewResponse('biller.calllists.index', compact('calllist'));
    }

    public function mytoday()
    {
        $calllists = CallList::all();
        $prospect_questions = ProspectQuestion::all();
       
        return view('focus.prospects.calllist.mycalls',compact('calllists','prospect_questions'));
    }
    public function allocationdays($id)
    {
       $titles =  Prospect::select('title')->distinct('title')->get();
       $calllist = CallList::find($id);
       $daterange ="Days With Prospects ".Carbon::parse($calllist->start_date)->format('Y-m-d')." To ".Carbon::parse($calllist->end_date)->format('Y-m-d');
       $start = Carbon::parse($calllist->start_date)->format('n');
       $end =Carbon::parse($calllist->end_date)->format('n');
        $id = $calllist->id;

        return view('focus.prospects.calllist.allocationdays',compact('id','start','end','daterange','titles'));
    }
    public function prospectviacalllist(Request $request)
    {
      
       
        $prospects = ProspectCallList::where('call_id',$request->id)->whereMonth('call_date', $request->month)
        ->whereDay('call_date', $request->day)
        ->with(['prospect' => function ($q) {
            $q->select('id', 'title', 'company','industry','contact_person','email','phone','region','call_status');
        }])
        ->get();
        $prospectstotal = ProspectCallList::where('call_id',$request->id)->whereMonth('call_date', $request->month)
        ->whereHas('prospect', function ($q) {
                $q->select('id','call_status')->where('is_called',0)->orWhere('is_called',1);
            })
       
        ->get()
        ->toArray();
        $total_call_group = array_reduce($prospectstotal, function ($init, $curr) {
            $d = (new DateTime($curr['call_date']))->format('j');
            $key_exists = in_array($d, array_keys($init));
            if (!$key_exists) $init[$d] = array();
            $init[$d][] = $curr['prospect_id'];
            
            return $init;
        }, []);
        $total_day_call = array();
        foreach ($total_call_group as $key => $val) {
            $total_day_call[] = array(
                'day' => $key,
                'count' => count(array_unique($val))
            );
        }
          
        $not = ProspectCallList::where('call_id',$request->id)->whereMonth('call_date', $request->month)
        ->whereHas('prospect', function ($q) {
                $q->select('id','call_status')->where('is_called',0);
            })
       
        ->get()->toArray();


        $day_call_group = array_reduce($not, function ($init, $curr) {
            $d = (new DateTime($curr['call_date']))->format('j');
            $key_exists = in_array($d, array_keys($init));
            if (!$key_exists) $init[$d] = array();
            $init[$d][] = $curr['prospect_id'];
            
            return $init;
        }, []);
        $day_call = array();
        foreach ($day_call_group as $key => $val) {
            $day_call[] = array(
                'day' => $key,
                'count' => count(array_unique($val))
            );
        }
        
     return response()->json(['notcalled'=>$day_call,'prospectstotal'=>$total_day_call,'prospects'=>$prospects]);
    }

    public function unallocatedbytitle($title){
        
    }

    public function previous_call_list()
    {
        $calllists = CallList::all();
        $prospect_questions = ProspectQuestion::all();
        return view('focus.prospects.calllist.previous_call_list', compact('calllists','prospect_questions'));
    }

    public function get_previous_call_lists(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $previous_call_lists = ProspectCallList::whereHas('prospect_status')->where('reassign_status',0)
        ->when(!$user->hasPermission('view_all_call_lists'), function ($query) {
            $query->whereHas('call_list', fn($q) => $q->where('employee_id', auth()->user()->id));
        })
        ->whereDate('call_date','<', Carbon::today()->toDateString())->get();

        return DataTables::of($previous_call_lists)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('title', function ($prospectcalllist) {

                return $prospectcalllist->prospect->title == null ? '-----' : $prospectcalllist->prospect->title;
            })
            ->addColumn('company', function ($prospectcalllist) {
                return $prospectcalllist->prospect->company == null ? '-----' : $prospectcalllist->prospect->company;
            })
            ->addColumn('industry', function ($prospectcalllist) {
                return $prospectcalllist->prospect->industry == null ? '-----' : $prospectcalllist->prospect->industry;
            })
            ->addColumn('call_prospect', function ($prospectcalllist) {

                $show = true;
                $status =$prospectcalllist->prospect->is_called;
                if($status == 0){
                    $show = true;
                }else{
                    $show = false;
                }
                
               
                return $show? '<a id="call" href="javascript:void(0)" class="btn btn-primary" data-id="' . $prospectcalllist->prospect_id . '" call-id="'.$prospectcalllist->call_id.'" data-toggle="tooltip"  title="Call" >
                <i  class="fa fa-vcard"></i>
                         </a>':'<a"><i  class="fa fa-check-circle  fa-2x text-primary"></i></a>';
            })
            ->addColumn('phone', function ($prospectcalllist) {
                return $prospectcalllist->prospect->phone == null ? '-----':$prospectcalllist->prospect->phone;
            })
            
            ->addColumn('region', function ($prospectcalllist) {
                return $prospectcalllist->prospect->region == null ? '-----':$prospectcalllist->prospect->region;
            })
            ->addColumn('call_status', function ($prospectcalllist) {
                $status =$prospectcalllist->prospect->call_status;
                if ($status == 'notcalled') {
                    $status = "Not called";
                } 
                else if ($status == 'callednotpicked'){
                    $status = "Called Not Picked";
                }
                else if($status == 'calledrescheduled') {
                    $status = "Call Rescheduled";
                }
                else if($status == 'callednotavailable') {
                    $status = "Called Not Available";
                }
                else{
                    $status = "Called";
                }
                return  $status;
            })
            ->addColumn('call_date', function ($prospectcalllist) {
                
                $call_date = $prospectcalllist->call_date == null ? '-----':$prospectcalllist->call_date;
               

                return $call_date;
            })
            ->make(true);
        
    }

    public function reasign_call_list()
    {
        $direct = Prospect::where('call_status','notcalled')->where('category', 'direct')->whereDoesntHave('prospectcalllist')->count();
        $excel = Prospect::select(DB::raw('title,COUNT("*") AS count '))->groupBy('title')->where('call_status','notcalled')->where('category', 'excel')
        ->whereHas('prospectcalllist', function($q){
            $q->whereDate('call_date','<', Carbon::today()->toDateString());
        })
        ->get();
        $employees = Hrm::all();
        return view('focus.prospects.calllist.reasign_call_list', compact('direct', 'excel','employees'));
    }

    public function store_reassign(Request $request)
    {
        // dd($request->all());
        $data = $request->only([
            'title','employee_from_id','total_prospects','prospect_to_assign',
            'start_date','end_date','employee_id'
        ]);

        $data['start_date'] = date_for_database($data['start_date']);
        $data['end_date'] = date_for_database($data['end_date']);
        $call_list_data = $request->only([
            'title',
            'category',
            'prospect_to_assign',
            'employee_id',
        ]);
        $call_list_data['start_date'] = date_for_database($data['start_date']);
        $call_list_data['end_date'] = date_for_database($data['end_date']);
        $call_list_data['prospects_number'] = $call_list_data['prospect_to_assign'];
        unset($call_list_data['prospect_to_assign']);

        try {
            DB::beginTransaction();
        $res = CallList::create($call_list_data);
        $data['call_list_id'] = $res->id;
        // dd($data);
        ProspectReassign::create($data);

        $assign_from = $data['employee_from_id'];
        // $response = $result->refresh();
        
        // return $response;
        $restitle = $res['title'];
        $restitle = strstr($restitle, ' ', true);
        $title = Str::before($res['title'], ' -');
       
        //get call id
        $callid = $res['id'];
        //get prospects based on title
        //New prospects
        if($res->category == 'direct'){
            $prospects = Prospect::where('call_status','notcalled')->where('category','direct')
            ->whereDoesntHave('prospectcalllist')->take($res['prospects_number'])
            ->get([
                "id"
            ])->toArray();
        }else{
            $prospects = Prospect::where('call_status','notcalled')->where('category','excel')->where('title',$title)
            ->whereHas('prospectcalllist', function($q) use($assign_from){
                $q->whereDate('call_date','<', Carbon::today()->toDateString())->where('reassign_status',0);
                $q->whereHas('call_list', function($q) use($assign_from){
                    $q->where('employee_id', $assign_from);
                });
                
            })->take($res['prospects_number'])
            ->get([
                "id"
            ])->toArray();
        }
        // dd($prospects, $title);

        //Find Prospect call lists to assign
        $prospect_calllists = ProspectCallList::whereIn('prospect_id',$prospects)->get();
        foreach($prospect_calllists as $calllist){
            $calllist->reassign_status = 1;
            $calllist->update();
        }
        
        //start and end date  
        $start = $res['start_date'];
        $end = $res['end_date'];
        // Create an empty array to store the valid dates
        $validDates = [];
        $carbonstart = Carbon::parse($start);
        $carbonend = Carbon::parse($end);
        
        // Loop through each date in the range
        for ($date = $carbonstart; $date <= $carbonend; $date->addDay()) {
            // Check if the current date is not a Sunday or Saturday
            if ($date->isWeekday()) {
                // Add the date to the array of valid dates
                $validDates[] = $date->toDateString();
            }
        }
        $prospectcount = count($prospects);
        $dateCount = count($validDates);
        $prospectIndex = 0;
        $dateIndex = 0;

        $prospectcalllist = [];


        // Allocate the prospects to the valid dates

        while ($prospectIndex < $prospectcount && $dateIndex < $dateCount) {
            $prospect = $prospects[$prospectIndex]['id'];
            $date = $validDates[$dateIndex];
            $prospectcalllist[] = [
                "prospect_id" => $prospect,
                "call_id" => $callid,
                "call_date"=>$date,
                "ins" => auth()->user()->ins,
                "user_id" => auth()->user()->id
            ];
            $prospectIndex++;
            $dateIndex = ($dateIndex + 1) % $dateCount;
        }
        // //send data to prospectcalllisttable
         ProspectCallList::insert($prospectcalllist);
         if($prospectcount > 0){
            DB::commit();
            // return $res;
         }
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error while Reassigning', $th);
        }
         return new RedirectResponse(route('biller.calllists.index'), ['flash_success' => 'CallList Created successfully!!']);

    }

    public function get_user_call_lists(Request $request)
    {
        $assign_from = $request->employee_from_id;
        $title = Str::before($request['title'], ' -');
        $prospects = Prospect::where('call_status','notcalled')->where('category','excel')->where('title',$title)
            ->whereHas('prospectcalllist', function($q) use($assign_from){
                $q->whereDate('call_date','<', Carbon::today()->toDateString())->where('reassign_status',0);
                $q->whereHas('call_list', function($q) use($assign_from){
                    $q->where('employee_id', $assign_from);
                });
                
            })
            ->count();
        return response()->json($prospects);
    }
   

}
