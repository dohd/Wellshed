<?php

namespace App\Http\Controllers\Crm;

use App\Http\Responses\RedirectResponse;
use App\Models\Company\ConfigMeta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\NotifyCustomerRegistration;
use App\Models\Company\Company;
use App\Models\customer\Customer;
use App\Models\customer\CustomerAddress;
use App\Models\hrm\Hrm;
use App\Models\subpackage\SubPackage;
use App\Models\subscription\Subscription;
use App\Models\target_zone\CustomerZoneItem;
use App\Models\target_zone\TargetZone;
use DB;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class CustomerLogin extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/crm/home';

    /**
     **_ Create a new controller instance.
     * _**
     **_ @return void
     * _**/
    public function __construct()
    {
        if (!session()->has('theme')) {
            session(['theme' => 'ltr']);
        }

        $this->middleware('guest')->except('logout');
        Auth::logout();

    }

    /**
     * _
     * _ @return property guard use for login
     * _
     * _&*/
    public function guard()
    {

        return Auth::guard('crm');
    }

    protected function authenticated(Request $request, $user) {}
    
    /**
     * Customer Self Registration
     * 
     * */
    public function register(Request $request )
    {
        if ($request->isMethod('get')) {
            $subpackages = SubPackage::all();
            $targetzones = TargetZone::with('items')->get();
            return view('crm.register', compact('subpackages', 'targetzones'));
        }
        // dd($request->all());
        $request->validate([
            'sub_package_id' => 'required',
            'segment' => 'required',
            'company' => 'required_if:segment,office',
            'first_name' => 'required_if:segment,household',
            'last_name' => 'required_if:segment,household',
            'email' => 'required_without:phone_no',
            'phone_no' => 'required_without:email',
            'password' => 'required',
            'target_zone_id' => 'required',
            'target_zone_item_id' => ['required', 'array', 'min:1'],
            'building_name' => 'required',
            'floor_no' => 'required',
            'door_no' => 'required',
        ], [
            'sub_package_id' => 'package is required',
            'target_zone_id' => 'delivery zone is required',
            'target_zone_item_id' => 'location is required',
        ]);

        $input = $request->all();
        $input['full_name'] = $input['first_name']? "{$input['first_name']} {$input['last_name']}" : '';

        try {
            DB::beginTransaction();

            $ins = Company::where('id', 2)->first(['id'])->id;

            // create customer
            $customer = Customer::create([
                'tid' => Customer::max('tid')+1,
                'segment' => $input['segment'],
                'company' => $input['company'],
                'name' => $input['company'] ?? $input['full_name'],
                'email' => $input['email'],
                'phone' => $input['phone_no'],
                'ins' => $ins,
            ]);    

            // create user
            $emailExists = Hrm::where('email', $input['email'])->exists();
            if ($emailExists) return errorHandler('Email: ' . $input['email'] . ' is already taken!');

            $user = Hrm::create([
                'tid' => Hrm::max('tid')+1,
                'first_name' => $input['first_name'] ?? $input['company'],
                'last_name' => $input['last_name'],
                'username' => $input['company'] ?? $input['full_name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'login_access' => 1,
                'status' => 1,
                'confirmed' => 1,
                'customer_id' => $customer->id,
                'ins' => $ins,
            ]);

            // create subscription
            $subscr = Subscription::create([
                'customer_id' => $customer->id,
                'sub_package_id' => $input['sub_package_id'],
                'start_date' => now(),
                'end_date' => date('Y-m-d H:i:s', strtotime('+1 month')),
                'ins' => $ins,
            ]);

            // create address
            $addressData = $request->only('building_name', 'floor_no', 'door_no', 'additional_info');
            $addressData['ins'] = $ins;
            $customerAddr = CustomerAddress::create($addressData);

            // create zone items
            foreach ($input['target_zone_item_id'] as $id) {
                $customerZoneItems[] = CustomerZoneItem::create([
                    'target_zone_item_id' => $id,
                    'target_zone_id' => $input['target_zone_id'],
                    'customer_id' => $customer->id,
                    'customer_address_id' => $customerAddr->id,
                ]);
            }

            DB::commit();

            if ($user) NotifyCustomerRegistration::dispatch($user,$input['password'],$ins);

            return redirect()
                ->route('login')
                ->with(['flash_success' => 'Registration Successful']);
        } catch (\Exception $e) {
            return errorHandler('Registration Error: Please contact admin', $e);            
        }
    }

    // login from for customer
    public function showLoginForm()
    {
        if (Auth::guard('crm')->check()) {
            return new RedirectResponse(route('crm.invoices.index'), ['']);
        }

        return view('crm.login');
    }


    public function login(Request $request)
    {

        $this->validateLogin($request);


        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            $u = ConfigMeta::withoutGlobalScopes()->where('ins', '=', auth('crm')->user()->ins)->where('feature_id', '=', 15)->first('value1')->value1;
            $login = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 18)->first('value2')->value2;
            session(['theme' => $u]);
            if (!$login) return $this->disabled($request);

            return $this->sendLoginResponse($request);


        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }


    protected function sendLoginResponse(Request $request)
    {

        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return new RedirectResponse(route('crm.invoices.index'), ['']);
    }


    public function logout(Request $request)
    {
        Auth::guard('crm')->logout();
        return new RedirectResponse(route('crm.login'), ['flash_success' => trans('customers.logout_success')]);
    }

    public function disabled(Request $request)
    {
        Auth::guard('crm')->logout();
        return new RedirectResponse(route('crm.login'), ['flash_error' => trans('customers.login_is_suspended')]);
    }


}
