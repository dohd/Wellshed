<?php

namespace App\Http\Controllers\Focus\rjc;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\rjc\ManageRjcRequest;
use App\Http\Responses\Focus\rjc\EditResponse;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\djc\Djc;
use App\Models\items\RjcItem;
use App\Models\project\Project;
use App\Models\rjc\Rjc;
use App\Models\verifiedjcs\VerifiedJc;
use App\Repositories\Focus\rjc\RjcRepository;
use Illuminate\Http\Request;

class RjcsController extends Controller
{
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param RjcRepository $repository ;
     */
    public function __construct(RjcRepository $repository)
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
        return new ViewResponse('focus.rjcs.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid =  Rjc::where('ins', auth()->user()->ins)->max('tid');
        $projects =  Project::doesntHave('rjc')->whereHas('quotes', function ($q) {
            $q->whereIn('invoiced', ['Yes', 'No']);
        })->get(['id', 'name', 'tid', 'main_quote_id']);
            
        foreach($projects as $project) {
            $lead_tids = [];
            $quote_tids = [];                
            foreach ($project->quotes as $quote) {
                $lead_tids[] = gen4tid('Tkt-', @$quote->lead->reference);
                if ($quote->bank_id) $quote_tids[] = gen4tid('PI-', $quote->tid);
                else $quote_tids[] = gen4tid('QT-', $quote->tid);
            }
            $project['lead_tids'] = implode(', ', $lead_tids);            
            $project['quote_tids'] = implode(', ', $quote_tids);            
        }
        
        return view('focus.rjcs.create', compact('projects', 'tid'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'attention' => 'required',
            'prepared_by' => 'required',
            'technician' => 'required',
            'subject' => 'required'
        ]);

        $data = $request->only([
            'tid', 'project_id', 'client_ref', 'technician', 'action_taken', 'root_cause', 'recommendations', 'subject', 
            'prepared_by', 'attention', 'region', 'report_date', 'image_one', 'image_two', 'image_three', 'image_four', 'caption_one', 
            'caption_two', 'caption_three', 'caption_four'
        ]);
        $data_items = $request->only(['row_index', 'unique_id', 'jobcard', 'equip_serial', 'make_type', 'capacity', 'location', 'last_service_date', 'next_service_date']);

        $data['ins'] = auth()->user()->ins;
        $data_items = modify_array($data_items);

        $result = $this->repository->create(compact('data', 'data_items'));

        // print preview
        $valid_token = token_validator('', 'd' . $result->id, true);
        $msg = ' <a href="'. route('biller.print_rjc', [$result->id, 11, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>'; 

        return new RedirectResponse(route('biller.rjcs.index'), ['flash_success' => 'Rjc Report Successfully Created' . $msg]);
    }    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Rjc $rjc)
    {
        $rjc_items = RjcItem::where('rjc_id', $rjc->id)->get();

        return new ViewResponse('focus.rjcs.view', compact('rjc', 'rjc_items'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Rjc $rjc)
    {
        $rjc_items = $rjc->rjc_items()->orderBy('row_index', 'ASC')->get();
        $projects =  Project::where('main_quote_id', '>', 0)
            ->orderBy('id', 'desc')
            ->get(['id', 'name', 'tid', 'main_quote_id']);
        // append quote tid
        foreach($projects as $project) {
            $lead_tids = array();
            $quote_tids = array();                
            foreach ($project->quotes as $quote) {
                $lead_tids[] = 'Tkt-'.sprintf('%04d', $quote->lead->reference);
                
                if ($quote->bank_id) $quote_tids[] = gen4tid('PI-', $quote->tid);
                else $quote_tids[] = gen4tid('QT-', $quote->tid);
            }
            $project['lead_tids'] = implode(', ', $lead_tids);            
            $project['quote_tids'] = implode(', ', $quote_tids);            
        }

        return new EditResponse('focus.rjcs.edit', compact('rjc', 'rjc_items', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ManageRjcRequest $request, Rjc $rjc)
    {
        $request->validate([
            'attention' => 'required',
            'prepared_by' => 'required',
            'technician' => 'required',
            'subject' => 'required'
        ]);

        $data = $request->only([
            'tid', 'project_id', 'client_ref', 'technician', 'action_taken', 'root_cause', 'recommendations', 
            'subject', 'prepared_by', 'attention', 'region', 'report_date', 'image_one', 'image_two', 'image_three', 
            'image_four', 'caption_one', 'caption_two', 'caption_three', 'caption_four'
        ]);
        $data_items = $request->only([
            'row_index', 'item_id', 'unique_id', 'jobcard', 'equip_serial', 'make_type', 'capacity', 'location', 'last_service_date', 'next_service_date'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data_items = modify_array($data_items);

        $result = $this->repository->update($rjc, compact('data', 'data_items'));

        // print preview
        $valid_token = token_validator('', 'd' . $result->id, true);
        $msg = ' <a href="'. route('biller.print_rjc', [$result->id, 11, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>'; 
        
        return new RedirectResponse(route('biller.rjcs.index'), ['flash_success' => 'Rjc Report Successfully Updated' . $msg]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rjc $rjc)
    {
        $this->repository->delete($rjc);

        return new RedirectResponse(route('biller.rjcs.index'), ['flash_success' => 'Rjc Report Successfully Deleted']);
    }

    // Extra Project Details
    public function project_extra_details()
    {
        $project = Project::find(request('project_id'));
        $verified_jobcards = VerifiedJc::where('type', 1)->where('quote_id', $project->main_quote_id)->with('equipment')->get();

        $djc = Djc::where('lead_id', $project->quote->lead_id)->get(['id', 'subject', 'job_card'])->last();
        if ($djc) $djc->preview_link = route('biller.print_djc', [$djc->id, 10, token_validator('', 'd'.$djc->id, true), 1]);
        
        return response()->json(compact('verified_jobcards', 'djc'));
    }
}
