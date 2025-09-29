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

namespace App\Http\Controllers\Focus\djc;

use App\Models\customer\Customer;
use App\Models\djc\Djc;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\djc\CreateResponse;
use App\Http\Responses\Focus\djc\EditResponse;
use App\Models\SsrDefaultInput\SsrDefaultInput;
use App\Repositories\Focus\djc\DjcRepository;
use App\Http\Requests\Focus\djc\ManageDjcRequest;
use App\Models\items\DjcItem;
use App\Models\lead\Lead;
use Illuminate\Http\Request;

/**
 * DjcsController
 */
class DjcsController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $repository ;
     */
    public function __construct(DjcRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\account\ManageAccountRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {

        $clients = Customer::all();
        foreach($clients as $customer){

            SsrDefaultInput::create([
                'client' => $customer->id,
                'findings' => "
                    <p>During our site visit, we noted the above unit is malfunctioning.<br>Afer thorough diagnosis, it was discovered that:</p>
                    <ul>
                    <li>The unit is running at low pressures&nbsp;</li>
                    <li>The unit's compressor is overheating and running with a humming sound.&nbsp;</li>
                    <li>The unit has leakages identified along the pipe<br><br></li>
                    </ul>
                ",
                'action' => "
                    <p><br>Diagnosis was carried out and the unit subsequently switched off to avoid fault escalation to other parts of the unit.<br><br></p>
                ",
                'recommendations' => "
                    <p>After diagnosis and evaluation was done on the unit, the following actions are recommended to restore it to working condition;</p>
                    <ul>
                    <li>replacement of the fauty compressor</li>
                    <li>sealing the leakages along the pipe</li>
                    <li>replacement of the the power protection equipment</li>
                    <li>execution of flushing, vacuuming, pressure tests as well as recharging the unit with gas.<br><br></li>
                    </ul>                
                ",
            ]);
        }


        return new ViewResponse('focus.djcs.index');
    }

    public function getSsrDefaultInputs(Request $request): \Illuminate\Http\JsonResponse
    {

        $customerId = Lead::find($request->leadId)->client_id;

        $defaultInputs = SsrDefaultInput::where('client', $customerId)->first();

        return response()->json($defaultInputs->toArray(), 200);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @param CreateDjcRequestNamespace $request
     * @return \App\Http\Responses\Focus\djc\CreateResponse
     */
    public function create(ManageDjcRequest $request)
    {
        $ins = auth()->user()->ins;
        $tid = Djc::where('ins', $ins)->max('tid');
        $prefixes = prefixesArray(['djc_report', 'lead'], $ins);

        $leads = Lead::where('status', 0)->orderBy('id', 'DESC')->get();
            
        return new CreateResponse('focus.djcs.create', compact('leads','tid', 'prefixes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAccountRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageDjcRequest $request)
    {
        $request->validate([
            'lead_id' => 'required',
            'attention' => 'required',
            'prepared_by' => 'required',
            'technician' => 'required',
            'subject' => 'required'
        ]);
        // extract request input
        $data = $request->only(['client_ref', 'jobcard_date', 'job_card', 'tid', 'lead_id', 'client_id', 
            'branch_id', 'reference', 'technician', 'action_taken', 'root_cause', 'recommendations', 'subject', 
            'prepared_by', 'attention', 'region', 'report_date', 'image_one', 'image_two', 'image_three', 'image_four',
            'caption_one', 'caption_two', 'caption_three', 'caption_four'
        ]);
        $data_items = $request->only(['row_index', 'unique_id', 'jobcard', 'equip_serial', 'make_type', 
            'capacity', 'location', 'last_service_date', 'next_service_date'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data_items = modify_array($data_items);

        $result = $this->repository->create(compact('data', 'data_items'));

        // print preview 
        $msg = ' <a href="'. route('biller.print_djc', [$result->id, 10, token_validator('', $result->id, true), 1]) .'" class="invisible" id="printpreview"></a>'; 

        return new RedirectResponse(route('biller.djcs.index', [$result['id']]), ['flash_success' => 'Djc Report Created' . $msg]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\djc\Djc $djc
     * @return \App\Http\Responses\Focus\djc\EditResponse
     */
    public function edit(Djc $djc)
    {
        $leads = Lead::where('status', 0)->orderBy('id', 'DESC')->get();
        if ($djc->lead) $leads = $leads->merge(collect([$djc->lead]));
        $djc_items = $djc->items()->orderBy('row_index', 'ASC')->get();
        $prefixes = prefixesArray(['djc_report', 'lead'], $djc->ins);

        return new EditResponse('focus.djcs.edit', compact('djc', 'leads', 'djc_items', 'prefixes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDjcRequestNamespace $request
     * @param App\Models\djc\Djc $djc
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(ManageDjcRequest $request, Djc $djc)
    {
        $request->validate([
            'lead_id' => 'required',
            'attention' => 'required',
            'prepared_by' => 'required',
            'technician' => 'required',
            'subject' => 'required'
        ]);
        // extract request input
        $data = $request->only(['client_ref', 'jobcard_date', 'job_card', 'tid', 'lead_id', 'client_id', 'branch_id', 'reference', 'technician', 'action_taken', 'root_cause', 'recommendations', 'subject', 'prepared_by', 'attention', 'region', 'report_date', 'image_one', 'image_two', 'image_three', 'image_four', 'caption_one', 'caption_two', 'caption_three', 'caption_four']);
        $data_items = $request->only(['row_index', 'item_id', 'unique_id', 'jobcard', 'equip_serial', 'make_type', 'capacity', 'location', 'last_service_date', 'next_service_date']);

        $data['ins'] = auth()->user()->ins;
        $data['id'] = $djc->id;

        $data_items = modify_array($data_items);

        $result = $this->repository->update($djc, compact('data', 'data_items'));

        // print preview 
        $valid_token = token_validator('', 'd' . $result->id, true);
        $msg = ' <a href="'. route('biller.print_djc', [$result->id, 10, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>'; 

        return new RedirectResponse(route('biller.djcs.index'), ['flash_success' => 'Djc report updated' . $msg]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAccountRequestNamespace $request
     * @param App\Models\djc\Djc $djc
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Djc $djc)
    {
        $this->repository->delete($djc);
        
        return new RedirectResponse(route('biller.djcs.index'), ['flash_success' => 'Djc deleted successfully']);
    }

    /**
     * View the specified resource from storage
     * 
     * @param App\Models\djc\Djc $djc
     * @return \App\Http\Responses\ViewResponse
     */
    public function show(Djc $djc)
    {
        $djc_items = DjcItem::where('djc_id', $djc->id)->get();

        return new ViewResponse('focus.djcs.view', compact('djc', 'djc_items'));
    }

    // Delete djc item
    public function delete_item($id)
    {
        $this->repository->delete_item($id);

        return response()->noContent();
    }
}
