<?php

namespace App\Http\Controllers\focus\workshift;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\workshift\Workshift;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Repositories\Focus\workshift\WorkshiftRepository;
use App\Http\Responses\Focus\workshift\EditResponse;
use App\Models\product\ProductVariation;
use Illuminate\Validation\ValidationException;

class WorkshiftController extends Controller
{

    /**
     * variable to store the repository object
     * @var WorkshiftRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param WorkshiftRepository $repository ;
     */
    public function __construct(WorkshiftRepository $repository)
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
        return view('focus.workshift.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    { 
        return view('focus.workshift.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $workshift = $request->only([
            'name'
            
        ]);
        $workshift_items = $request->only([
            'weekday',
            'hours',
            'clock_out',
            'clock_in',
            'status'
        ]);
        
        $workshift['ins'] = auth()->user()->ins;
        $workshift['user_id'] = auth()->user()->id;

        $workshift_items = modify_array($workshift_items);
        // dd($workshift_items);
        
        $result = $this->repository->create(compact('workshift', 'workshift_items'));

        return new RedirectResponse(route('biller.workshifts.index'), ['flash_success' => 'workshift created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Workshift $workshift)
    {
        return new ViewResponse('focus.workshift.view', compact('workshift'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Workshift $workshift, Request $request)
    {
        return new EditResponse($workshift);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Workshift $workshift)
    {
        $data = $request->only([
            'name'
        ]);
        $data_items = $request->only([
            'weekday',
            'hours',
            'clock_out',
            'clock_in',
            'status',
            'id'
        ]);
        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data_items = modify_array($data_items);
        //$data_items = array_filter($data_items, fn($v) => $v['item_id']);
        if (!$data_items) throw ValidationException::withMessages(['Please use suggested options for input within a row!']);
        $workshift = $this->repository->update($workshift, compact('data', 'data_items'));

        $msg = 'Direct workshift Updated Successfully.';

        return new RedirectResponse(route('biller.workshifts.index'), ['flash_success' => $msg]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Workshift $workshift)
    {
        $this->repository->delete($workshift);

        return new RedirectResponse(route('biller.workshifts.index'), ['flash_success' => 'workshift deleted successfully']);
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $tools = Workshift::where('name', 'LIKE', '%'.$q.'%')
            ->limit(6)->get(['id', 'name']);

        return response()->json($tools);
    }
    
}
