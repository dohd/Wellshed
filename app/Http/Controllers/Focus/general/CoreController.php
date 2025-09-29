<?php

namespace App\Http\Controllers\Focus\general;

use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Auth\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class CoreController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo;

    public function redirectPath()
    {
        return session()->get('url_intended') ?: route('biller.dashboard');
    }

    public function showLoginForm()
    {
        if (url()->current() . '/' == url()->previous()) {
            session()->forget('url_intended');
        }
        if (session()->has('url_intended')) {
            $url = session()->get('url_intended');
            if (strpos($url, 'logout') !== false) session()->forget('url_intended');
        }

        return view('core.index');
    }

    /*
    * Check to see if the users account is confirmed and active
    */
    protected function authenticated(Request $request, $user)
    {
        // fetch EFRIS Access Token 
        if (config('services.efris.base_url')) {
            $controller = new \App\Http\Controllers\Focus\etr\EfrisController;
            $controller->getSymmetricKey();
        }

        if (!$user->isConfirmed()) {
            access()->logout();
            trigger_error(trans('exceptions.frontend.auth.confirmation.resend', ['user_id' => $user->id]));
        }
        if (!$user->isLoginAccessActive()) {
            access()->logout();
            if ($user->ins === 1 || $user->ins === 2) return view('core.account-deactivated');
            $business = $user->business;
            $today = new DateTime();
            $billingDate = new DateTime($business->billing_date);
            $cutoffDate = $billingDate->add(new DateInterval('P' . $business->grace_days . 'D'));

            // conditional checks for subscription expiry
            $check1 = $business->subscription_balance > 0 &&
                $today > $cutoffDate && $business->status === 'Active';
            $check2 = $today > $cutoffDate;
            if ($check1 || $check2) {
                return response()->view('core.subscription_expired');
            }

            if ($business->status !== 'Active') {
                return response()->view('core.account-deactivated');
            }
        }
        
        // Set Omniconvo Access Token in Session
        $controller = new \App\Http\Controllers\Focus\omniconvo\OmniController;
        $controller->fetchAccessToken();
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
        // return 'username';
    }

    protected function validateLogin(Request $request)
    {
        $request->merge(['username' => $request->email]);
        // unset($request['email']);
        $this->validate($request, [
            // 'username' => request('username')? 'required|string' : '',
            'email' => request('email') ? 'required|string' : '',
            'password' => 'required|string',
            'g-recaptcha-response' => 'required_if:captcha_status,true|captcha',
        ], ['g-recaptcha-response.required_if' => 'Captcha Error']);
    }

    public function logout(Request $request)
    {
        if (!$request->auth) $this->redirectTo = session()->get('url_intended');

        // clear session
        if (app('session')->has(config('access.socialite_session_name'))) {
            app('session')->forget(config('access.socialite_session_name'));
        }
        app()->make(Auth::class)->flushTempSession();
        $this->guard()->logout();
        $request->session()->flush();
        $request->session()->regenerate();

        session(['url_intended' => $this->redirectTo]);

        return redirect()->route('biller.index');
    }
}
