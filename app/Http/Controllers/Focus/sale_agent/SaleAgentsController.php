<?php

namespace App\Http\Controllers\Focus\sale_agent;

use App\Http\Controllers\Controller;
use App\Models\sale_agent\SaleAgent;
use App\Models\send_sms\SendSms;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class SaleAgentsController extends Controller
{

    // NEW: request OTP before registration (no agent row yet)
    public function requestOtpForPhone(Request $request)
    {
        $v = \Validator::make($request->all(), [
            'phone' => 'required|string|max:30',
        ]);
        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $phone = $request->phone;

        // Simple throttle: max 5 OTPs per 10 minutes per ip+phone
        $throttleKey = 'otp:req:' . sha1($request->ip() . ':' . $phone);
        $count = Cache::get($throttleKey, 0);
        if ($count >= 5) {
            return response()->json(['message' => 'Too many OTP requests. Try again later.'], 429);
        }
        Cache::put($throttleKey, $count + 1, now()->addMinutes(10));

        // Create an OTP valid for 10 minutes
        $code = (string) random_int(100000, 999999);
        Cache::put('otp:phone:' . $phone, $code, now()->addMinutes(10));

        // TODO: integrate SMS provider here to send $code to $phone
        $message = "Your code is {$code}. Expires at 10 min. Don’t share this code.";
        $this->sendSms(2, $phone, $message);
        // For development only, return the code:
        return response()->json([
            'message' => 'OTP generated and sent (stub).',
            'testing_otp' => $code, // REMOVE in production
            'expires_in' => 600
        ]);
    }
    // UPDATE: require OTP to register (prevents spam registrations)
    public function register(Request $request)
    {
        $v = \Validator::make($request->all(), [
            'first_name'    => 'required|string|max:120',
            'last_name'     => 'required|string|max:120',
            // ensure 18+
            'date_of_birth' => 'required|date|before_or_equal:' . \Carbon\Carbon::now()->subYears(18)->toDateString(),
            'phone'  => 'required|string|max:30|unique:sales_agents,phone',
            'alternative_number'  => 'nullable|string|max:30',
            'email'  => 'nullable|email|max:150|unique:sales_agents,email',
            'county' => 'nullable|string|max:120',
            'city'   => 'nullable|string|max:120',
            'referral_code' => 'nullable|string|max:60',
            'consent_terms' => 'accepted',
            'consent_data'  => 'accepted',
            'otp_code'      => 'required|string|max:10', // 👈 NEW
        ]);
        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        // Validate OTP against cached value for this phone
        $cacheKey = 'otp:phone:' . $request->phone;
        $expected = Cache::get($cacheKey);
        if (! $expected || $expected !== $request->otp_code) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }
        // Consume OTP
        Cache::forget($cacheKey);

        // Create agent (phone is now verified)
        $agent = SaleAgent::create([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'phone'  => $request->phone,
            'alternative_number'  => $request->alternative_number,
            'email'  => $request->email,
            'public_code'  => $request->phone,
            'county' => $request->county,
            'city'   => $request->city,
            'referral_code' => $request->referral_code,
            'consent_terms' => (bool) $request->boolean('consent_terms'),
            'consent_data'  => (bool) $request->boolean('consent_data'),
            'is_phone_verified' => true,   // 👈 mark verified on successful OTP
            'status' => 'pending',
        ]);

        $message = "Your Enrollment Code is: {$agent->public_code}";
        $this->sendSms(2, $agent->phone, $message);
        $agent->profile()->create([]);

        $continueUrl = rtrim(config('app.frontend_onboard_url', env('FRONTEND_ONBOARD_URL', 'https://virtualgigs.erpproject.co.ke/agents/onboard')), '/');
        $continueUrl .= '?token=' . urlencode($agent->onboarding_token) . '&agent=' . urlencode($agent->uuid);

        return response()->json([
            'agent' => $this->formatAgent($agent->load('profile')),
            'onboarding_token' => $agent->onboarding_token,
            'continue_url' => $continueUrl,
        ], 201);
    }

    private function continueUrlFor(SaleAgent $agent)
    {
        $continueUrl = rtrim(config('app.frontend_onboard_url', env('FRONTEND_ONBOARD_URL', 'https://virtualgigs.erpproject.co.ke/agents/onboard')), '/');
        return $continueUrl . '?token=' . urlencode($agent->onboarding_token) . '&agent=' . urlencode($agent->uuid);
    }

    public function sendSms($ins, $phoneNumber, $content)
    {


        try {

            DB::beginTransaction();

            $cost_per_160 = 0.6;
            $charCount = strlen($content);
            $send_sms = new SendSms();

            $send_sms->subject = $content;
            $send_sms->phone_numbers = $phoneNumber;
            $send_sms->user_type = 'customer';
            $send_sms->delivery_type = 'now';
            $send_sms->message_type = 'single';
            $send_sms->sent_to_ids = '';
            $send_sms->characters = $charCount;
            $send_sms->cost = $cost_per_160;
            $send_sms->user_count = 1;
            $send_sms->total_cost = $cost_per_160 * ceil($charCount / 160);
            if (auth()->user()) {

                $send_sms->user_id = auth()->user()->id;
                $send_sms->ins = auth()->user()->ins;

                $send_sms->save();
                (new RosesmsRepository(auth()->user()->ins))->bulk_sms($phoneNumber, $content, $send_sms);
            } else {
                $send_sms->user_id = $ins;
                $send_sms->ins = $ins;

                $send_sms->save();
                (new RosesmsRepository($ins))->bulk_sms($phoneNumber, $content, $send_sms);
            }


            DB::commit();
        } catch (Exception $ex) {

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }
    }

    /**
     * POST /api/agents/{uuid}/request-otp – generate and (stub) send OTP
     */
    public function requestOtp($uuid)
    {
        $agent = SaleAgent::where('uuid', $uuid)->firstOrFail();
        $code = (string) random_int(100000, 999999);
        $agent->otp_code = $code;
        $agent->otp_expires_at = now()->addMinutes(10);
        $agent->save();


        // TODO: Integrate SMS provider here.
        $message = "Your code is {$code}. Expires at {$agent->otp_expires_at->toIso8601String()} min. Don’t share this code.";
        $this->sendSms(2, $agent->phone, $message);
        // For now we return the code for testing (remove in production).
        return response()->json([
            'message' => 'OTP generated and sent (stub).',
            'testing_otp' => $code,
            'expires_at' => $agent->otp_expires_at->toIso8601String(),
        ]);
    }

    public function resolveByCode($public_code)
    {
        $agent = SaleAgent::where('public_code', $public_code)->firstOrFail();
        return response()->json(['uuid' => $agent->uuid]);
    }

    /**
     * POST /api/agents/{uuid}/verify-otp – verify phone number
     */
    public function verifyOtp($uuid, Request $request)
    {
        $v = Validator::make($request->all(), ['code' => 'required|string|max:10']);
        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $agent = SaleAgent::where('uuid', $uuid)->firstOrFail();
        if (! $agent->otp_code || now()->greaterThan($agent->otp_expires_at)) {
            return response()->json(['message' => 'OTP expired or not requested.'], 400);
        }
        if ($agent->otp_code !== $request->code) {
            return response()->json(['message' => 'Invalid OTP.'], 422);
        }

        // mark verified + rotate onboarding token so the agent can resume safely
        $agent->is_phone_verified = true;
        $agent->otp_code = null;
        $agent->otp_expires_at = null;
        $agent->onboarding_token = Str::random(60);
        $agent->save();

        return response()->json([
            'message' => 'Phone verified.',
            'onboarding_token' => $agent->onboarding_token,
            'continue_url' => $this->continueUrlFor($agent),
            'agent' => $this->formatAgent($agent->load('profile')),
        ]);
    }

    /**
     * PATCH /api/agents/{uuid}/profile – update CV-related info (requires X-Onboarding-Token)
     */
    public function updateProfile($uuid, Request $request)
    {
        $agent = SaleAgent::where('uuid', $uuid)->firstOrFail();


        // Simple token check for continuation from external Frontend
        $token = $request->header('X-Onboarding-Token');
        if (! $token || $token !== $agent->onboarding_token) {
            return response()->json(['message' => 'Unauthorized (invalid onboarding token).'], 401);
        }


        $v = Validator::make($request->all(), [
            'headline' => 'nullable|string|max:160',
            'bio' => 'nullable|string|max:2000',
            'describe_yourself' => 'nullable|string|max:2000',
            'employment_status' => 'nullable|string|max:40',
            'professional_courses' => 'nullable|array',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:60',
            'experience' => 'nullable|array',
            'education' => 'nullable|array',
            'linkedin_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'availability' => 'nullable|string|max:30',
            'hourly_rate' => 'nullable|numeric|min:0',
            'preferred_categories' => 'nullable|array',
            'extra' => 'nullable|array',
            'status' => 'nullable|in:pending,active,rejected,blocked',
        ]);
        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }


        $profile = $agent->profile ?: $agent->profile()->create([]);
        $profile->fill($request->only([
            'headline',
            'bio',
            'describe_yourself',
            'employment_status',
            'linkedin_url',
            'facebook_url',
            'tiktok_url',
            'instagram_url',
            'twitter_url',
            'portfolio_url',
            'availability',
            'hourly_rate'
        ]));
        foreach (['skills', 'experience', 'education', 'professional_courses', 'preferred_categories', 'extra'] as $jsonField) {
            if ($request->has($jsonField)) {
                $profile->{$jsonField} = $request->input($jsonField);
            }
        }
        $profile->save();


        if ($request->filled('status')) {
            $agent->status = $request->input('status');
            $agent->save();
        }


        return response()->json($this->formatAgent($agent->load('profile')));
    }

    /**
     * POST /api/agents/{uuid}/documents – upload CV file (requires X-Onboarding-Token)
     */
    public function uploadCv($uuid, Request $request)
    {
        $agent = SaleAgent::where('uuid', $uuid)->firstOrFail();
        $token = $request->header('X-Onboarding-Token');
        if (! $token || $token !== $agent->onboarding_token) {
            return response()->json(['message' => 'Unauthorized (invalid onboarding token).'], 401);
        }


        $v = Validator::make($request->all(), [
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);
        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }


        $path = $request->file('cv')->store('agents/cv', 'public');
        $profile = $agent->profile ?: $agent->profile()->create([]);
        $profile->cv_path = $path;
        $profile->save();


        return response()->json([
            'message' => 'CV uploaded.',
            'cv_url' => url('storage/' . $path),
        ], 201);
    }


    /**
     * GET /api/agents/{uuid} – fetch agent + profile
     */
    public function show($uuid)
    {
        $agent = SaleAgent::with('profile')->where('uuid', $uuid)->firstOrFail();
        return response()->json($this->formatAgent($agent));
    }

    private function formatAgent(SaleAgent $agent)
    {
        return [
            'uuid' => $agent->uuid,
            'first_name' => $agent->first_name,
            'last_name' => $agent->last_name,
            'full_name' => trim(($agent->first_name ? $agent->first_name . ' ' : '') . ($agent->last_name ?? '')),
            'date_of_birth' => $agent->date_of_birth,
            'email' => $agent->email,
            'phone' => $agent->phone,
            'county' => $agent->county,
            'city' => $agent->city,
            'referral_code' => $agent->referral_code,
            'is_phone_verified' => (bool) $agent->is_phone_verified,
            'status' => $agent->status,
            'consent' => [
                'terms' => (bool) $agent->consent_terms,
                'data' => (bool) $agent->consent_data,
            ],
            'profile' => ($agent->relationLoaded('profile') && $agent->profile) ? [
                'headline' => $agent->profile->headline,
                'bio' => $agent->profile->bio,
                'describe_yourself' => $agent->profile->describe_yourself,
                'employment_status' => $agent->profile->employment_status,
                'skills' => $agent->profile->skills,
                'experience' => $agent->profile->experience,
                'education' => $agent->profile->education,
                'professional_courses' => $agent->profile->professional_courses,
                'cv_url' => $agent->profile->cv_path ? url('storage/' . $agent->profile->cv_path) : null,
                'linkedin_url' => $agent->profile->linkedin_url,
                'facebook_url' => $agent->profile->facebook_url,
                'tiktok_url' => $agent->profile->tiktok_url,
                'instagram_url' => $agent->profile->instagram_url,
                'twitter_url' => $agent->profile->twitter_url,
                'portfolio_url' => $agent->profile->portfolio_url,
                'availability' => $agent->profile->availability,
                'hourly_rate' => $agent->profile->hourly_rate,
                'preferred_categories' => $agent->profile->preferred_categories,
                'extra' => $agent->profile->extra,
            ] : null,
        ];
    }

    private function formatPublicAgent(SaleAgent $agent)
    {
        return [
            'public_code' => $agent->public_code,
            'name'        => $agent->name,
            'county'      => $agent->county,
            'city'        => $agent->city,
            'profile' => $agent->profile ? [
                'headline'  => $agent->profile->headline,
                'skills'    => $agent->profile->skills,
                'experience' => $agent->profile->experience,
                'education' => $agent->profile->education,
                'cv_url'    => $agent->profile->cv_path ? url('storage/' . $agent->profile->cv_path) : null,
                'linkedin_url' => $agent->profile->linkedin_url,
                'facebook_url' => $agent->profile->facebook_url,
                'tiktok_url' => $agent->profile->tiktok_url,
                'instagram_url' => $agent->profile->instagram_url,
                'twitter_url' => $agent->profile->twitter_url,
                'portfolio_url' => $agent->profile->portfolio_url,
                'availability'  => $agent->profile->availability,
            ] : null,
        ];
    }

    // New: GET /api/agents/code/{public_code}
    public function showByCode($public_code)
    {
        $agent = SaleAgent::with('profile')
            ->where('public_code', $public_code)
            ->firstOrFail();

        // Optional: Only expose ACTIVE agents
        if ($agent->status !== 'active') {
            return response()->json(['message' => 'Agent not available'], 404);
        }

        return response()->json($this->formatPublicAgent($agent));
    }

    public function updateCore($uuid, Request $request)
    {
        $agent = SaleAgent::where('uuid', $uuid)->firstOrFail();

        // Require onboarding token, same as profile updates
        $token = $request->header('X-Onboarding-Token');
        if (! $token || $token !== $agent->onboarding_token) {
            return response()->json(['message' => 'Unauthorized (invalid onboarding token).'], 401);
        }

        $v = Validator::make($request->all(), [
            'name'   => 'nullable|string|max:120',
            'phone'  => 'nullable|string|max:30|unique:agents,phone,' . $agent->id,
            'email'  => 'nullable|email|max:150|unique:agents,email,' . $agent->id,
            'county' => 'nullable|string|max:120',
            'city'   => 'nullable|string|max:120',
            'referral_code' => 'nullable|string|max:60',
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        // Detect phone change: require re-verification if changed
        $phoneChanged = $request->filled('phone') && $request->phone !== $agent->phone;

        $agent->fill($request->only(['name', 'phone', 'email', 'county', 'city', 'referral_code']));
        if ($phoneChanged) {
            $agent->is_phone_verified = false; // force a fresh OTP verification
        }
        $agent->save();

        return response()->json([
            'message' => 'Core details updated' . ($phoneChanged ? ' (re-verify phone to complete changes)' : ''),
            'agent' => $this->formatAgent($agent->load('profile')),
        ]);
    }

    public function index()
    {
        return view('focus.sale_agents.index');
    }

    public function get(Request $request)
    {
        $core = SaleAgent::get();
        return DataTables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($sale_agent) {
                $name = '';
                if($sale_agent->name){
                    $name = $sale_agent->name;
                }else{
                    $name = $sale_agent->first_name .' '.$sale_agent->last_name;
                }
                 return $name;
            })
            ->addColumn('actions', function ($sale_agent) {
                return $sale_agent->action_buttons;
            })
            ->make(true);
    }
}
