<?php

namespace App\Http\Controllers\Focus\promotions;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\Company\Company;
use App\Models\promotions\ClientFeedback;
use App\Models\promotions\CompanyPromotionalPrefix;
use App\Models\customer_complain\CustomerComplain;
use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\ReferralsPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;
use App\Models\quality_tracking\QualityTracking;
use App\Models\send_sms\SendSms;
use App\Repositories\Focus\general\RosesmsRepository;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ClientFeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!access()->allow('manage-client-feedback')) return response('', 403);

        if ($request->ajax()) {

            $feedbacks = ClientFeedback::
                when(request('categoryFilter'), function ($q) {

                    $q->where('category', request('categoryFilter'));
                })
                ->where('category','Customer Direct Message')
                ->orderBy('id','desc')
                ->get()
                ->map(function ($feedback) {


                    $show = '<a href="' . route('biller.client-feedback.show', $feedback->id) . '" class="btn btn-secondary round mr-1">View</a>';
                    $delete = '<a href="' . route('biller.delete-client-feedback', $feedback->id) . '" class="btn btn-danger round mr-1 delete">Delete</a>';

                    return [
                        'id' => $feedback->id,
                        'name' => $feedback->name,
                        'email' => $feedback->email,
                        'phone' => $feedback->phone,
                        'category' => $feedback->category,
                        'details' => strip_tags($feedback->details),
                        'date' => dateFormat($feedback->created_at),
                        'action' => $show . $delete,
                    ];
                });


            return Datatables::of($feedbacks)
                ->rawColumns(['action'])
                ->make(true);


        }

        return view('focus.client_feedback.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $prefix = $request->query('prefix');
        $uuid = $request->query('uuid');

        $company = CompanyPromotionalPrefix::where('company_id', $prefix)->first();
        $reservation = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->find($uuid) ??
                    CustomersPromoCodeReservation::withoutGlobalScopes()->find($uuid) ??
                    ReferralsPromoCodeReservation::withoutGlobalScopes()->find($uuid);
        return view('focus.client_feedback.create', compact('company','reservation'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255'],
            'phone'          => ['required', 'string'],
            'title'          => ['required', 'string'],
            'redeemable_uuid'=> ['nullable', 'string'],
            'promo_code_id'  => ['nullable', 'integer'],
            'category'       => ['required', 'in:Quality Concern,Complaint,Customer Direct Message'],
            'details'        => ['required', 'string'],
            'company_id'     => ['required', 'integer', 'exists:companies,id'],

            // NEW: multi-file support (dynamic inputs)
            'files'          => ['nullable', 'array'],
            'files.*'        => ['file', 'max:10000'], // 10,000 KB ~= 10MB per file

            // BACKWARD COMPAT: old single input "file"
            'file'           => ['nullable', 'file', 'max:10000'],
        ]);

        try {
            DB::beginTransaction();

            $paths = [];

            // Prefer new multi-file inputs if present
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $upload) {
                    if (!$upload) continue;

                    $originalName   = $upload->getClientOriginalName();
                    $base           = pathinfo(str_replace(' ', '', $originalName), PATHINFO_FILENAME);
                    $uniqueFileName = uniqid($base . '-', true) . '.' . $upload->getClientOriginalExtension();

                    // Keep your existing disk/folder
                    $storedPath = $upload->storeAs('client-feedback', $uniqueFileName, 'public');
                    $paths[]    = $storedPath;
                }
            }
            // Fallback to legacy single "file" input
            elseif ($request->hasFile('file')) {
                $file          = $request->file('file');
                $originalName  = $file->getClientOriginalName();
                $base          = pathinfo(str_replace(' ', '', $originalName), PATHINFO_FILENAME);
                $uniqueName    = uniqid($base . '-', true) . '.' . $file->getClientOriginalExtension();
                $storedPath    = $file->storeAs('client-feedback', $uniqueName, 'public');
                $paths[]       = $storedPath;
            }

            // Backward-compatible storage format
            $filePathToStore = null;
            if (count($paths) === 1) {
                $filePathToStore = $paths[0];                  // same as before (string)
            } elseif (count($paths) > 1) {
                $filePathToStore = json_encode($paths);        // multiple files (JSON string)
            }

            $feedback = ClientFeedback::create([
                'name'              => $validated['name'],
                'email'             => $validated['email'] ?? null,
                'phone'             => $validated['phone'],
                'category'          => $validated['category'],
                'details'           => $validated['details'],
                'file_path'         => $filePathToStore,
                'company_id'        => $validated['company_id'],
                'title'             => $validated['title'],
                'redeemable_uuid'   => $validated['redeemable_uuid'] ?? null,
                'promo_code_id'     => $validated['promo_code_id'] ?? null,
            ]);

            if ($validated['category'] === 'Quality Concern') {
                QualityTracking::create([
                    'date'                 => (new DateTime())->format('Y-m-d'),
                    'incident_desc'        => $validated['details'],
                    'ins'                  => $validated['company_id'],
                    'customer_feedback_id' => $feedback->id,
                ]);
            }

            if ($validated['category'] === 'Complaint') {
                CustomerComplain::create([
                    'date'                 => (new DateTime())->format('Y-m-d'),
                    'issue_description'    => $validated['details'],
                    'ins'                  => $validated['company_id'],
                    'customer_feedback_id' => $feedback->id,
                ]);
            }

            DB::commit();
            $reservation = ThirdPartiesPromoCodeReservation::find($feedback->redeemable_uuid) ??
                    CustomersPromoCodeReservation::find($feedback->redeemable_uuid) ??
                    ReferralsPromoCodeReservation::find($feedback->redeemable_uuid);
            $code = $reservation ? $reservation->redeemable_code : '';
            $company = Company::find($validated['company_id']);
            $message = "To {$company->cname}: New customer feedback '{$validated['title']}' received, for redeemable code {$code}, (Type: {$validated['category']}) from {$validated['name']}. Please check the system for details and take appropriate action.";
            $this->sendSms($company->id, $company->notification_number, $message);

        } catch (\Throwable $ex) {
            DB::rollBack();
            return response()->json([
                'message' => $ex->getMessage(),
                'code'    => $ex->getCode(),
                'file'    => $ex->getFile(),
                'line'    => $ex->getLine(),
            ], 500);
        }

        $prefix = CompanyPromotionalPrefix::where('company_id', $validated['company_id'])->first();

        return view('focus.client_feedback.success', [
            'prefix'    => optional($prefix)->prefix,
            'companyId' => $validated['company_id'],
            'uuid'      => $validated['redeemable_uuid'],
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\promotions\ClientFeedback  $clientFeedback
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        if (!access()->allow('manage-client-feedback')) return response('', 403);

        $clientFeedback = ClientFeedback::find($id);
        $clientFeedback->promo_code = $clientFeedback->promoCode ? $clientFeedback->promoCode->code : '';
        $reservation = ThirdPartiesPromoCodeReservation::find($clientFeedback->redeemable_uuid) ??
                    CustomersPromoCodeReservation::find($clientFeedback->redeemable_uuid) ??
                    ReferralsPromoCodeReservation::find($clientFeedback->redeemable_uuid);
        $clientFeedback->redeemableCode = $reservation->redeemable_code;
        

        return view('focus.client_feedback.show', compact('clientFeedback'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\promotions\ClientFeedback  $clientFeedback
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientFeedback $clientFeedback)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\promotions\ClientFeedback  $clientFeedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClientFeedback $clientFeedback)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\promotions\ClientFeedback  $clientFeedback
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        if (!access()->allow('delete-client-feedback')) return response('', 403);


        $clientFeedback = ClientFeedback::find($id);

        if ($clientFeedback->file_path) {
            \Storage::disk('public')->delete($clientFeedback->file_path);
        }

        $clientFeedback->delete();

        return new RedirectResponse(route('biller.client-feedback.index'), ['flash_success' => "Feedback deleted successfully."]);
    }

    public function download(Request $request, $id)
    {
//        if (!access()->allow('view-company-notice-board')) return redirect()->back();

        $feedback = ClientFeedback::find($id);

         $paths = $feedback->file_paths; // accessor returns an array
        abort_if(empty($paths), 404, 'No attachments found.');

        $index = (int) $request->query('index', 0);
        $path  = $paths[$index] ?? null;
        abort_if(!$path, 404, 'Attachment not found for the given index.');

        $disk = Storage::disk('public'); // use the same disk you stored to
        abort_if(!$disk->exists($path), 404, "File not found at path: {$path}");

        return $disk->download($path);
    }

    public function sendSms($ins, $phoneNumber, $content){


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
            }else{
                $send_sms->user_id = $ins;
                $send_sms->ins = $ins;

                $send_sms->save();
                (new RosesmsRepository($ins))->bulk_sms($phoneNumber, $content, $send_sms);
            }


            DB::commit();
        }
        catch (Exception $ex){

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

    }

}
