<?php

namespace App\Http\Controllers\Focus\quality_tracking;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Controller;
use App\Models\customer\Customer;
use App\Models\health_and_safety\HealthAndSafetyTracking;
use App\Models\health_and_safety_objectives\HealthAndSafetyObjective;
use App\Models\hrm\Hrm;
use App\Models\project\Project;
use App\Repositories\Focus\quality_tracking\QualityTrackingRepository;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use App\Http\Responses\RedirectResponse;
use App\Models\quality_tracking\QualityTracking;
use Illuminate\Support\Facades\DB;

class QualityTrackingController extends Controller
{
    protected $repository;

    public function __construct(QualityTrackingRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!access()->allow('manage-quality-tracking')) return redirect()->back();

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


        return view('focus.quality_tracking.index', compact( 'months'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if (!access()->allow('create-quality-tracking')) return redirect()->back();

        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $projects = Project::all();

        $employees = Hrm::all();

        $clients = Customer::all();
        $objectives = HealthAndSafetyObjective::all();

        return view('focus.quality_tracking.create', compact('clients','objectives', 'customers', 'projects', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!access()->allow('create-quality-tracking')) return redirect()->back();

        $input = $request->all();

        $input['employee'] = json_encode($request->employee);;
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;

        try {
            DB::beginTransaction();

            $qualityTracking = new QualityTracking();

            $qualityTracking->project_id = $input['project'];
            $qualityTracking->date = (new DateTime($input['date']))->format('Y-m-d');
            $qualityTracking->completion_date = (new DateTime($input['completion_date']))->format('Y-m-d');
            $qualityTracking->fill($input);

            $qualityTracking->save();

            DB::commit();
        } catch (Exception $ex) {

            DB::rollBack();
            return $ex->getMessage();
        }

        return new RedirectResponse(route('biller.quality-tracking.index'), ['flash_success' => 'Issue has been added successfully']);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        if (!access()->allow('manage-quality-tracking')) return redirect()->back();

        $data = QualityTracking::find($id);

        $employees = [];
        if ($data['employee']) {
            foreach (json_decode($data['employee']) as $employee) {
                $c = Hrm::where('id', $employee)->first();
                $d['a'] = $c->first_name . ' ' . $c->last_name;
                $employees[] = $d;
            }
        }

        return view('focus.quality_tracking.view', compact('data', 'employees'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        if (!access()->allow('edit-quality-tracking')) return redirect()->back();

        $data = QualityTracking::find($id);
        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $projects = Project::all();

        $employees = Hrm::all();

        $clients = Customer::all();

        $proId = optional($data->project)->id;

        $data->project = $proId;

//        return $data;

        return view('focus.quality_tracking.edit', compact('data', 'clients', 'customers', 'projects', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function update(Request $request, $id)
    {

        if (!access()->allow('edit-quality-tracking')) return redirect()->back();

        $data = QualityTracking::find($id);

        $input = $request->all();
        $input['employee'] = json_encode($request->employee);;
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;
        $input['date'] = (new DateTime($input['date']))->format('Y-m-d');
        $input['completion_date'] = (new DateTime($input['completion_date']))->format('Y-m-d');

        try {

            $data->fill($input);
            $data->save();
        } catch (\Throwable $th) {
            throw new GeneralException('Error updating issue.');
        }


        return new RedirectResponse(route('biller.quality-tracking.index'), ['flash_success' => 'Quality Tracking updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        if (!access()->allow('delete-quality-tracking')) return redirect()->back();

        $qualityObjective = QualityTracking::find($id);
        $qualityObjective->delete();

        return new RedirectResponse(route('biller.quality-tracking.index'), ['flash_success' => 'Objective deleted successfully']);
    }
}
