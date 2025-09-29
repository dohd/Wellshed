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

namespace App\Http\Controllers\Focus\lead;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\lead\ManageLeadRequest;
use App\Http\Responses\Focus\lead\CreateResponse;
use App\Http\Responses\Focus\lead\EditResponse;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\ReferralsPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;
use App\Models\branch\Branch;
use App\Models\classlist\Classlist;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\customergroup\Customergroup;
use App\Models\customfield\Customfield;
use App\Models\lead\Lead;
use App\Models\lead\LeadSource;
use App\Models\potential\Potential;
use App\Models\tender\Tender;
use App\Repositories\Focus\lead\LeadRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;

/**
 * ProductcategoriesController
 */
class LeadsController extends Controller
{
    /**
     * variable to store the repository object
     * @var LeadRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LeadRepository $repository ;
     */
    public function __construct(LeadRepository $repository)
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
        $open_lead = Lead::where('status', 0)->count();
        $closed_lead = Lead::where('status', 1)->count();
        $total_lead = Lead::count();
        $income_accounts = Account::where('account_type', 'Income')->get();
        $leadSources = LeadSource::select('id', 'name')->get();
        $classlists = Classlist::get();
        $tenderStatus = Tender::select('tender_stages')->distinct()->pluck('tender_stages');

        return new ViewResponse('focus.leads.index', compact('tenderStatus', 'classlists', 'open_lead', 'closed_lead', 'total_lead', 'income_accounts', 'leadSources'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {

        //Leads with no client id
        $potentials = Lead::where('client_id',0)->get();
        foreach($potentials as $lead)
        {
            if($lead->potential) continue;
            if($lead['client_name']){
                $new_data = [
                    'client_name' => $lead['client_name'],
                    'client_email' => $lead['client_email'],
                    'client_contact' => $lead['client_contact'],
                    'client_address' => $lead['client_address'],
                    'lead_id' => $lead->id
                ];
                Potential::create($new_data);
            }
            
        }
        return new CreateResponse('focus.leads.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageLeadRequest $request)
    {
        $validated = $request->validate([
            'reference' => 'required',
            'date_of_request' => 'required',
            'title' => 'required',
            'lead_source_id' => 'required',
            'assign_to' => 'required',
            'reservation_uuid' => ['nullable', function ($attribute, $value, $fail) use ($request) {

                    if($value) {

                        $reservation = ThirdPartiesPromoCodeReservation::where('redeemable_code', $value)->first() ??
                            CustomersPromoCodeReservation::where('redeemable_code', $value)->first() ??
                            ReferralsPromoCodeReservation::where('redeemable_code', $value)->first();

                        if (!$reservation) $fail("Redeemable Code Does Not Exist!");
                    }
                },
            ],
        ]);


        // filter request input fields
        $data = $request->except(['_token', 'ins', 'files']);

        if ($validated['reservation_uuid']) {

            $reservation = ThirdPartiesPromoCodeReservation::where('redeemable_code', $validated['reservation_uuid'])->first() ??
                CustomersPromoCodeReservation::where('redeemable_code', $validated['reservation_uuid'])->first() ??
                ReferralsPromoCodeReservation::where('redeemable_code', $validated['reservation_uuid'])->first();

            if ($reservation) $data['reservation_uuid'] = $reservation->uuid;
        }

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        try {
            $this->repository->create($data);
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Lead', $th);
        }

        return new RedirectResponse(route('biller.leads.index'), ['flash_success' => 'Ticket / Lead Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\lead\Lead $lead
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Lead $lead)
    {
        $customers = Customer::whereHas('currency')->get(['id', 'company']);
        $branches = Branch::get(['id', 'name', 'customer_id']);
        $prefixes = prefixesArray(['lead'], $lead->ins);
        $income_accounts = Account::whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'income'))->get();
        $leadSources = LeadSource::select('id', 'name')->get();
        $classlists = Classlist::get();
        $currencies = Currency::get(['id', 'code']);

        $reservation = CustomersPromoCodeReservation::find($lead->reservation_uuid) ??
            ThirdPartiesPromoCodeReservation::find($lead->reservation_uuid) ??
            ReferralsPromoCodeReservation::find($lead->reservation_uuid);

        $l1 = ThirdPartiesPromoCodeReservation::where('status', 'reserved')->get()->pluck('redeemable_code')->toArray();
        $l2 = CustomersPromoCodeReservation::where('status', 'reserved')->get()->pluck('redeemable_code')->toArray();
        $l3 = ReferralsPromoCodeReservation::where('status', 'reserved')->get()->pluck('redeemable_code')->toArray();

        $redeemableCodes = array_merge($l1, $l2, $l3);


        return new EditResponse('focus.leads.edit', compact('redeemableCodes', 'currencies', 'classlists', 'lead', 'branches', 'customers', 'prefixes', 'income_accounts', 'leadSources', 'reservation'));
    }

    /**
     * Update the specified resource.
     *
     * @param \App\Models\lead\Lead $lead
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function update(Request $request, Lead $lead)
    {
        // validate fields
        $fields = [
            'reference' => 'required',
            'date_of_request' => 'required',
            'title' => 'required',
            'lead_source_id' => 'required',
            'assign_to' => 'required',
            'reservation_uuid' => ['nullable', function ($attribute, $value, $fail) use ($request) {

                    if($value) {

                        $reservation = ThirdPartiesPromoCodeReservation::where('redeemable_code', $value)->first() ??
                            CustomersPromoCodeReservation::where('redeemable_code', $value)->first() ??
                            ReferralsPromoCodeReservation::where('redeemable_code', $value)->first();

                        if (!$reservation) $fail("Redeemable Code Does Not Exist!");
                    }
                },
            ],
        ];

        $validated = $request->validate($fields);

        // update input fields from request
        $data = $request->except(['_token', 'ins', 'files']);
        $data['date_of_request'] = date_for_database($data['date_of_request']);

        if ($validated['reservation_uuid']) {

            $reservation = ThirdPartiesPromoCodeReservation::where('redeemable_code', $validated['reservation_uuid'])->first() ??
                CustomersPromoCodeReservation::where('redeemable_code', $validated['reservation_uuid'])->first() ??
                ReferralsPromoCodeReservation::where('redeemable_code', $validated['reservation_uuid'])->first();

            if ($reservation) $data['reservation_uuid'] = $reservation->uuid;
        }


        try {
            $this->repository->update($lead, $data);
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Lead', $th);
        }

        return new RedirectResponse(route('biller.leads.index'), ['flash_success' => 'Ticket / Lead Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\lead\Lead $lead
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Lead $lead)
    {
        try {
            $this->repository->delete($lead);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Lead', $th);
        }

        return new RedirectResponse(route('biller.leads.index'), ['flash_success' => 'Ticket / Lead Successfully Deleted']);
    }

    /**
     * Show the view for the specific resource
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param \App\Models\lead\Lead $lead
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Lead $lead, Request $request)
    {
        $days = '';
        if ($lead->exact_date) {
            $exact = Carbon::parse($lead->exact_date);
            $difference = $exact->diff(Carbon::now());
            $days = $difference->days;
            return new ViewResponse('focus.leads.view', compact('lead', 'days'));
        }
        return new ViewResponse('focus.leads.view', compact('lead', 'days'));
    }

    // fetch lead details with specific lead_id
    public function lead_load(Request $request)
    {
        $id = $request->get('id');
        
        $leads = Lead::all()->where('rel_id', $id);

        return response()->json($leads);
    }
    
    // search specific lead with defined parameters
    public function lead_search(ManageLeadRequest $request)
    {
        $q = $request->post('keyword');

        $leads = Lead::where('title', 'LIKE', '%'. $q .'%')->limit(6)->get();

        return response()->json($leads);        
    }

    /**
     * Update Lead Open Status
     */
    public function update_status(Lead $lead, Request $request)
    {
        // dd($lead);
        $status = $request->status;
        $reason = $request->reason;
        $note = $request->note;
        $lead->update(compact('status', 'reason', 'note'));

        return redirect()->back();
    }

    public function update_reminder(Lead $lead, Request $request)
    {
        // dd($lead);
        $reminder_date = $request->reminder_date;
        $exact_date = $request->exact_date;
        $lead->update(compact('reminder_date', 'exact_date'));

        return redirect()->back();
    }

    public function download_walkins()
    {
        $file_name = 'walkins.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$file_name",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $leads = Lead::where('client_id', 0)->get();
        $columns = ['Client Name', 'Contact', 'Email', 'Address'];

        // Use output buffering to create the CSV content
        $callback = function () use ($leads, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->client_name,
                    $lead->client_contact,
                    $lead->client_email,
                    $lead->client_address
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function index_potential()
    {
        return view('focus.leads.index_potential');
    }
    public function get_potentials(Request $request)
    {
        $all_potentials = Potential::all();
        return DataTables::of($all_potentials)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('client_name', function ($potential) {
             return $potential->client_name;
        })
        ->addColumn('client_contact', function ($potential) {
             return $potential->client_contact;
        })
        ->addColumn('lead', function ($potential) {
             return $potential->lead ? gen4tid('TKT-',$potential->lead->reference) : '';
        })
        ->addColumn('client_email', function ($potential) {
            return $potential->client_email;
       })
        ->addColumn('client_address', function ($potential) {
            return $potential->client_address;
       })
        ->addColumn('actions', function ($potential) {
            return '';
        })
        ->make(true);
    }

    public function create_client($lead_id,Request $request)
    {
        $lead = Lead::find($lead_id);
        $customer_data = [
            'name' => $lead->client_name,
            'contact' => $lead->client_contact,
            'email' => $lead->client_email,
            'address' => $lead->client_address,
        ];
        $input = $request->only('rel_type', 'rel_id');
        $customergroups = Customergroup::all();
        $customer = array();
        if (isset($input['rel_id'])) $customer = Customer::find($input['rel_id']);
        $fields = custom_fields(Customfield::where('module_id', '1')->get()->groupBy('field_type'));
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['receivable', 'loan']))
            ->whereHas('currency')
            ->get(['id', 'holder']);
        return view('focus.customers.create', compact('customer_data','accounts', 'customergroups', 'fields', 'input', 'customer', 'accounts'));
    }
}
