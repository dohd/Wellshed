<?php

namespace App\Http\Controllers\Focus\health_and_safety;

use App\Http\Controllers\Controller;
use App\Models\account\Account;
use App\Models\additional\Additional;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\misc\Misc;
use App\Models\project\Project;
use App\Models\health_and_safety\HealthAndSafetyTracking;
use DateTime;
use Illuminate\Http\Request;
use App\Http\Responses\RedirectResponse;
use App\Exceptions\GeneralException;
use App\Http\Requests\Request as RequestsRequest;
use App\Models\health_and_safety_objectives\HealthAndSafetyObjective;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Exception;

class HealthAndSafetyTrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (!access()->allow('manage-health-safety-tracking')) return redirect()->back();

        $months = [
            "January" => 1,
            "February" => 2,
            "March" => 3,
            "April" => 4,
            "May" => 5,
            "June" => 6,
            "July" => 7,
            "August" => 8,
            "September" => 9,
            "October" => 10,
            "November" => 11,
            "December" => 12,
        ];

        return view('focus.health_and_safety.index', compact( 'months'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if (!access()->allow('create-health-safety-tracking')) return redirect()->back();

        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $projects = Project::all();

        $employees = Hrm::all();

        $clients = Customer::all();
        $objectives = HealthAndSafetyObjective::all();

        return view('focus.health_and_safety.create', compact('clients','objectives', 'customers', 'projects', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!access()->allow('create-health-safety-tracking')) return redirect()->back();

        // dd($request->all());
        $input = $request->all();

        $input['employee'] = json_encode($request->employee);;
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;

        try {
            DB::beginTransaction();

            $hsTracking = new HealthAndSafetyTracking();
            $hsTracking->project_id = $input['project'];
            $hsTracking->date = (new DateTime($input['date']))->format('Y-m-d');
            $hsTracking->completion_date = (new DateTime($input['completion_date']))->format('Y-m-d');
            $hsTracking->fill($input);
            $hsTracking->save();

            DB::commit();
        } catch (Exception $ex) {

            DB::rollBack();
            return $ex->getMessage();
        }

        return new RedirectResponse(route('biller.health-and-safety.index'), ['flash_success' => 'Issue has been added successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        if (!access()->allow('manage-health-safety-tracking')) return redirect()->back();

        $data = HealthAndSafetyTracking::find($id);

        $employees = [];
        foreach (json_decode($data['employee']) as $employee){
            $c = Hrm::where('id', $employee)->first();
            $d['a'] = $c->first_name.' '. $c->last_name;
            $employees[] = $d;
        }
        // dd($k);
        return view('focus.health_and_safety.view', compact('data', 'employees'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        if (!access()->allow('edit-health-safety-tracking')) return redirect()->back();

        $data = HealthAndSafetyTracking::find($id);
        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $projects = Project::all();

        $employees = Hrm::all();

        $clients = Customer::all();

        $proId = $data->project->id;

        $data->project = $proId;

//        return $data;

        return view('focus.health_and_safety.edit', compact('data', 'clients', 'customers', 'projects', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if (!access()->allow('edit-health-safety-tracking')) return redirect()->back();

        $data = HealthAndSafetyTracking::find($id);

        $input = $request->all();
        $input['employee'] = json_encode($request->employee);;
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;
        $input['date'] = (new DateTime($input['date']))->format('Y-m-d');
        $input['completion_date'] = (new DateTime($input['completion_date']))->format('Y-m-d');


        try {
            $data->update($input);
        } catch (\Throwable $th) {
            throw new GeneralException('Error updating issue.');
        }

        return new RedirectResponse(route('biller.health-and-safety.index'), ['flash_success' => 'Issue has been updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        if (!access()->allow('delete-health-safety-tracking')) return redirect()->back();

        $data = HealthAndSafetyTracking::find($id);
        $data->delete();
        return new RedirectResponse(route('biller.health-and-safety.index'), ['flash_success' => 'Issue has been deleted successfully']);
    }

    public function clientProjects(Request $request)
    {
        $projects = Project::where('customer_id', $request->customer_id)
            ->where('end_note', '!=', 'Closed')
            ->where('end_note', '!=', 'Completed')
            ->get();

        return response()->json($projects);
    }

    public function monthlySummary()
    {
        // TODO: Get number of days of current month
        $month = date('n');
        $year = date('Y');
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $thisMonth = [];
        // TODO: Loop through days
        for ($i = 1; $i <= $days; $i++) {
            $t['day'] = $i;
            $t['date'] = $year . '-' . $month . '-' . $i;
            $date = \DateTime::createFromFormat('Y-m-d', $t['date']);
            $date =  $date->format('Y-m-d');
            $t['date'] = $date;

            $data = DB::table('health_and_safety_tracking')
                ->where('date', $date)
                ->get()->toArray();

            if (empty($data)) {
                $t['color'] = 'green';
            } else {
                foreach ($data as $d){
                    if (in_array('lost-work-day', (array)$d)) {
                        $t['color'] = 'red';
                    } else {
                        $t['color'] = 'yellow';
                    }
                }
            }
            $thisMonth[]  = $t;
        }
        // dd($thisMonth);


        // dd($data);

        // $days = [];
        // foreach ($data as $d) {
        //     $color['day'] = date('d', strtotime($d->date));
        //     $color['date'] = $d->date;

        //     if ($d->status == 'lost-work-day') {
        //         $color['color'] = 'red';
        //     } elseif ($d->status == 'first-aid-case') {
        //         $color['color'] = 'yellow';
        //     } else {
        //         $color['color'] = 'green';
        //     }

        //     $days[] = $color;
        // }

        // array_multisort(array_column($days, 'day'), SORT_ASC, $days);

        // dd($days);



        $month = date('n');
        $year = date('Y');
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $projects = Project::all();
        $additionals = Additional::all();

        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get();
        $tags = Misc::where('section', 1)->get();

        $employees = Hrm::all();


        $firstFour = array_slice($thisMonth, 0, 4);
        $chunk1 = array_chunk($firstFour, 2);

        $secondSixteen = array_slice($thisMonth, 4, 16);
        $chunk2 = array_chunk($secondSixteen, 8);

        $thirdGroup = array_slice($thisMonth, 20, $days);
        $chunk3 = array_chunk($thirdGroup, 2);



        // return view('focus.tracking_sheets.health_and_safety.summary', compact('chunk1', 'thisMonth'));
        return view('focus.health_and_safety.summary', compact('chunk1','chunk2','chunk3', 'thisMonth','days', 'additionals', 'customers', 'accounts', 'projects', 'mics', 'employees', 'statuses', 'tags'));
    }

    public function dayIncidents(Request $request){
        $day = $request->day;
        $month = date('n');
        $year = date('Y');

        $t = $year . '-' . $month . '-' . $day;
        $date = \DateTime::createFromFormat('Y-m-d', $t);
        $date =  $date->format('Y-m-d');

        $data = DB::table('health_and_safety_tracking')
                ->where('date', $date)
                ->get();
            
        $daysData= [];
        foreach($data as $d){
            $e = json_decode($d->employee);
            $d->customer = Customer::where('id', $d->customer_id)->first();
            $d->project = Project::where('id', $d->project_id)->first();
            // $employees = [];
            // foreach($e as $f){
            //     $employees[] = Hrm::where('id', $f)->first();
            // }
            // $d->emp = $employees;
            
            $daysData[] = $d;
        }
        return response()->json([$daysData, $date]);
    }
}
