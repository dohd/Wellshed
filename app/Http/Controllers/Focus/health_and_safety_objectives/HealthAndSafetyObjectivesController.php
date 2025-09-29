<?php

namespace App\Http\Controllers\Focus\health_and_safety_objectives;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\health_and_safety_objectives\HealthAndSafetyObjectivesRepository;
use Illuminate\Http\Request;
use App\Http\Responses\RedirectResponse;
use App\Models\health_and_safety_objectives\HealthAndSafetyObjective;

class HealthAndSafetyObjectivesController extends Controller
{
    protected $repository;

    public function __construct(HealthAndSafetyObjectivesRepository $repository)
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
        return view('focus.health_and_safety_objectives.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('focus.health_and_safety_objectives.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;
        $this->repository->create($input);
        return new RedirectResponse(route('biller.health-and-safety-objectives.index'), ['flash_success' => 'Objective added successfully']);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $healthAndSafetyObjective = HealthAndSafetyObjective::find($id);
        return view('focus.health_and_safety_objectives.view', compact('healthAndSafetyObjective'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $healthAndSafetyObjective = HealthAndSafetyObjective::find($id);
        return view('focus.health_and_safety_objectives.edit', compact('healthAndSafetyObjective'));
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
        $healthAndSafetyObjective = HealthAndSafetyObjective::find($id);
        $input = $request->all();
        $this->repository->update($healthAndSafetyObjective, $input);
        return new RedirectResponse(route('biller.health-and-safety-objectives.index'), ['flash_success' => 'Objective updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $healthAndSafetyObjective = HealthAndSafetyObjective::find($id);
        $healthAndSafetyObjective->delete();
        return new RedirectResponse(route('biller.health-and-safety-objectives.index'), ['flash_success' => 'Objective deleted successfully']);
    }
}
