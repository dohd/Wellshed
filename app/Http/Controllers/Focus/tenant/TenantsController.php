<?php

namespace App\Http\Controllers\Focus\tenant;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\User\User;
use App\Models\additional\Additional;
use App\Models\Company\Company;
use App\Models\Company\EmailSetting;
use App\Models\Company\SmsSetting;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;
use App\Models\tenant\GraceDaysRequest;
use App\Models\tenant\Tenant;
use App\Models\tenant\TenantActivation;
use App\Models\tenant\TenantDeactivation;
use App\Models\tenant\TenantLoyaltyPointsRedemption;
use App\Models\tenant_service\TenantService;
use App\Repositories\Focus\tenant\TenantRepository;
use Closure;
use DateInterval;
use DateTime;
use DB;
use Exception;
use Illuminate\Http\Request;

/**
 * ProductcategoriesController
 */
class TenantsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $repository ;
     */
    public function __construct(TenantRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return new ViewResponse('focus.tenants.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
        $tenant_services = TenantService::whereHas('package')->get();
        $salesAgents = Hrm::orderBy('first_name')->select('id', 'first_name', 'last_name')->get();
        $additionals = Additional::get();

        return view('focus.tenants.create', compact('additionals', 'tenant_services', 'salesAgents'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {  
        $request->validate([
            'cname' => 'required',
            'address' => 'required',
            'country' => 'required',
            'postbox' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'package_id' => 'required',
            'date' => 'required',
            'subscr_term' => 'required',
            'billing_date' => ['required', 'date_format:Y-m-d\TH:i'],
            'billing_notes' => ['nullable', 'string', 'max:2000'],
            'sales_agent_id' => 'required',
            'relationship_manager_id' => 'required',
        ]);

        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Customer Account', $th);
        }
        
        return new RedirectResponse(route('biller.tenants.index'), ['flash_success' => 'Account  Successfully Created']);
    }


    public function subscriptionDetails($id) {
        $tenant = Tenant::find($id);
        $package = @$tenant->package->service->package;
        if (!$package || $package && !count($package)) {
            return redirect()->back()->with('flash_error', 'Your Package Details are Not Fully Set Up Yet... Come back some time in the future.');
        }
        $package = $package->first();

        $packagePrice = 0.00;
        $maintPrice = 0.00;
        $subscriptionPrice = 0.00;
        if ($package) {
            $packagePrice = $package->price;
            $maintPrice = (float) @$tenant->package->service->maintenance_cost;
            $subscriptionPrice = amountFormat($packagePrice + $maintPrice);
        }

        $cutoffDate = null;
        if ($tenant->billing_date) {
            $cutoffDate = (new DateTime($tenant->billing_date))->add(new DateInterval('P' . $tenant->grace_days . 'D'));
        }

        $agent = User::withoutGlobalScopes()->find($tenant->sales_agent_id);
        $agentMeta = HrmMeta::where('user_id', optional($agent)->id)->first();
        $rm = User::withoutGlobalScopes()->find($tenant->relationship_manager_id);
        $rmMeta = HrmMeta::where('user_id', optional($rm)->id)->first();
        $subscriptionDetails = [
            'package_number' => $package->package_number,
            'package_name' => $package->name,
            'subscription_price' => $subscriptionPrice,
            'billing_date' => $tenant->billing_date ? (new DateTime($tenant->billing_date))->format('h:ia | l, jS F, Y') : null,
            'cutoff_date' => $cutoffDate ? $cutoffDate->format('h:ia | l, jS F, Y') : null,
            'bank' => 'KCB Bank',
            'mpesa_paybill' => 522522,
            'account' => 1295110113,
            'agent' => optional($agent)->first_name . ' ' . optional($agent)->last_name,
            'agent_email' => optional($agent)->email,
            'agent_phone' => optional($agentMeta)->secondary_contact,
            'rm' => optional($rm)->first_name . ' ' . optional($rm)->last_name,
            'rm_email' => optional($rm)->email,
            'rm_phone' => optional($rmMeta)->secondary_contact,
        ];
        
        return view('focus.tenants.subscription_details', compact('subscriptionDetails'));
    }


    public function requestGraceDays(Request $request, $tenantId){
        try {
            DB::beginTransaction();
            $tenant = Tenant::find($tenantId);
            $lastRequest = GraceDaysRequest::where('tenant_id', $tenant->id)->latest()->first();
            if ($lastRequest) {
                $lastRequestDate = new DateTime($lastRequest->created_at); // Convert to DateTime instance
                $currentDate = new DateTime(); // Get current date and time
                // Calculate the difference in days
                $interval = $lastRequestDate->diff($currentDate);
                if ($interval->days < 30) {
                    return new RedirectResponse(route('biller.tenants.subscription-details', $tenantId), ['flash_error' => 'Grace Days Requests can only be made once every 30 days.']);
                }
            }
            $validated = $request->validate([
                'days' => ['integer',  function (string $attribute, $value, Closure $fail) use ($tenant) {
                    $maxCutoffDate = (new DateTime($tenant->billing_date))->add(new DateInterval('P' . 7 . 'D'));
                    $requestedCutoffDate = (new DateTime($tenant->billing_date))->add(new DateInterval('P' . $value . 'D'));
                    if ($requestedCutoffDate > $maxCutoffDate) $fail("You can only request a maximum of 7 grace days after your set billing date");
                },
                ]
            ]);
            $tenant->grace_days = $validated['days'];
            $tenant->save();
            GraceDaysRequest::create([
                'tenant_id' => $tenant->id,
                'days' => $validated['days'],
            ]);
            DB::commit();
        }
        catch(Exception $exception){
            DB::rollBack();
            return [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
            return errorHandler('Error Updating Account!', $th);
        }

        return new RedirectResponse(route('biller.tenants.subscription-details', $tenantId), ['flash_success' => 'Grace Days Successfully Granted']);
    }

    public function redeemLoyaltyPoints(Request $request, $tenantId){

        try {
            DB::beginTransaction();

            $tenant = Tenant::find($tenantId);

            $validated = $request->validate([
                'points' => ['required', 'integer', 'min:1', 'max:' . $tenant->loyalty_points],
            ]);

            $redeemedDays = (integer) bcmul($validated['points'], 2, 0);

            $tenant->billing_date = (new DateTime($tenant->billing_date))->add(new DateInterval('P' . $redeemedDays . 'D'))->format('Y-m-d H:i:s');
            $tenant->loyalty_points  -= $validated['points'];
            $tenant->save();

            TenantLoyaltyPointsRedemption::create([
                'tenant_id' => $tenant->id,
                'points' => $validated['points'],
                'days' => $redeemedDays,
            ]);

            DB::commit();
        }
        catch(Exception $exception){

            DB::rollBack();

            return [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];

            return errorHandler('Error Updating Account!', $th);
        }

        return new RedirectResponse(route('biller.tenants.subscription-details', $tenantId), ['flash_success' => $validated['points'] . ' Loyalty Points Redeemed Successfully for ' . $redeemedDays . ' Additional Subscription Days']);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Tenant $tenant, Request $request)
    {
        $user = User::where(['ins' => $tenant->id, 'created_at' => $tenant->created_at])->first();
        $tenant_services = TenantService::whereHas('package')->get();

        $disabledUsers = User::where('ins', $tenant->id)->get();

        $allStatusZero = $disabledUsers->every(function ($user) {
            return $user->login_access === 0;
        });

        $salesAgents = Hrm::orderBy('first_name')->select('id', 'first_name', 'last_name')->get();
        $additionals = Additional::get();

        return view('focus.tenants.edit', compact('additionals', 'tenant', 'user', 'tenant_services', 'allStatusZero', 'salesAgents'));
    }

    /**
     * Update Resource in Storage
     * 
     */
    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'cname' => 'required',
            'address' => 'required',
            'country' => 'required',
            'postbox' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'package_id' => 'required',
            'date' => 'required',
            'subscr_term' => 'required',
            'billing_date' => ['required', 'date_format:Y-m-d\TH:i'],
            'billing_notes' => ['nullable', 'string', 'max:2000'],
            'billing_status' => 'required',
            'grace_days' => ['required', 'numeric', 'min:0'],
            'sales_agent_id' => 'required',
            'relationship_manager_id' => 'required',
        ]);

        try {
            $this->repository->update($tenant, $request->except(['_token']));
        } catch(\Throwable $th){
            return errorHandler('Error Updating Account!', $th);
        }
        
        return new RedirectResponse(route('biller.tenants.index'), ['flash_success' => 'Account  Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Tenant $tenant)
    {
        try {
            $this->repository->delete($tenant);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Account!', $th);
        }

        return new RedirectResponse(route('biller.tenants.index'), ['flash_success' => 'Account  Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Tenant $tenant, Request $request)
    {
        $user = User::where(['ins' => $tenant->id, 'created_at' => $tenant->created_at])->first();
        $service = @$tenant->package->service ?: new TenantService;
        return new ViewResponse('focus.tenants.view', compact('tenant', 'user', 'service'));
    }

    /**
     * Select Tenants
     * 
     */
    public function select(Request $request)
    {
        $q = $request->input('q');
        $tenants = Tenant::where('cname', 'LIKE', '%' . $q . '%')->limit(10)->get();
        return response()->json($tenants);
    }

    /**
     * Select Business Customers
     */
    public function customers(Request $request)
    {
        $q = $request->input('q');
        $customers = Customer::where('company', 'LIKE', '%' . $q . '%')
        ->doesntHave('tenant_package')
        ->limit(10)->get();

        return response()->json($customers);
    }

    /**
     * Update Lead Status
     */
    public function update_status(Tenant $tenant, Request $request)
    {
        try {
            DB::beginTransaction();

            $tenant->update([
                'status' => $request->status,
                'status_msg' => $request->status_msg,
            ]);
            if ($tenant->status == 'Active') {
                User::where('ins', $tenant->id)->update(['status' => 1, 'login_access' => 1]);
            }

            DB::commit();
            return redirect()->back()->with('flash_success', 'Status Updated Successfully');
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Status!', $th);
        }        
    }


    public function disableClientAccounts($allStatusZero, $clientBusinessId) {

        try {
           $users = User::where('ins', $clientBusinessId)->get();

            DB::beginTransaction();

            $tenant = Tenant::find($clientBusinessId);
            $sms_setting = SmsSetting::withoutGlobalScopes()->where('ins',$tenant->id)->first();
            $email_setting = EmailSetting::withoutGlobalScopes()->where('ins',$tenant->id)->first();
            if($allStatusZero) {

                $tenant->status = 'Active';
                TenantActivation::create([
                    'tenant_id' => $tenant->id,
                ]);
                $sms_setting->active = 1;
                $sms_setting->update();
                $email_setting->active = 1;
                $email_setting->update();
            }
            else {

                $tenant->status = 'Suspended';
                TenantDeactivation::create([
                    'tenant_id' => $tenant->id,
                ]);
                $sms_setting->active = 0;
                $sms_setting->update();
                $email_setting->active = 0;
                $email_setting->update();
            }
            $tenant->save();


            foreach ($users as $user) {


                    if($allStatusZero) $user->login_access = 1;
                    else $user->login_access = 0;
                    $user->save();
                }


            DB::commit();

        } catch (Exception $ex) {

            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ];

            return errorHandler('Error Updating Status!', $ex);
        }

        $tenant = Company::find($clientBusinessId);
        $tenant->status = $allStatusZero ? 'Active' : 'Suspended';
        $tenant->save();

        $allStatusZero = $users->every(function ($user) {
            return $user->login_access === 0;
        });

        return new RedirectResponse(route('biller.tenants.index'),
            ['flash_success' => $allStatusZero ?
                "User Accounts for Client '" . $tenant->cname . "' Disabled Successfully!" :
                "User Accounts for Client '" . $tenant->cname . "' Enabled Successfully!"]);
    }
}
