<?php

namespace App\Http\Controllers\Focus\mpesa_deposit;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Models\mpesa_deposit\MpesaDeposit;
use App\Models\tenant\Tenant;

class MpesaDepositsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return new ViewResponse('focus.mpesa_deposits.index');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Request $request, MpesaDeposit $mpesa_deposit)
    {
        return redirect()->back();
        return new ViewResponse('focus.mpesa_deposits.view', compact('mpesa_deposit'));
    }

    /**
     * Validate Deposit
     * 
     */
    function validate_deposit(Request $request)
    {
        $result = $request->all();

        try {
            $tenant = Tenant::where('phone', 'LIKE', '%'.$result['BillRefNumber'].'%')->first();
            if (!$tenant) {
                // C2B00012 invalid account number error code
                $data['ResultCode'] = 'C2B00012';
                $data['ResultDesc'] = 'Rejected';
                return response()->json($data);
            }
            
            $deposit = MpesaDeposit::create([
                'owner_id' => $tenant->id,
                'trans_type' => $result['TransactionType'],
                'trans_id' => $result['TransID'],
                'trans_time' => $result['TransTime'],
                'trans_amount' => (float) $result['TransAmount'],
                'bill_ref_number' => $result['BillRefNumber'],
                'msisdn' => substr($result['MSISDN'], 0, 12),
            ]);

            return response()->json([
                'ResultCode' => '0',
                'ResultDesc' => 'Accepted',
                'data' => $result,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    /**
     * Store Deposit
     * 
     */
    function deposit(Request $request)
    {
        $result = $request->all();

        try {
            $data = [
                'trans_id' => $result['TransID'],
                'invoice_number' => $result['InvoiceNumber'],
                'org_account_balance' => (float) $result['OrgAccountBalance'],
                'thirdparty_trans_id' => $result['ThirdPartyTransID'],
                'msisdn' => substr($result['MSISDN'], 0, 12),
                'first_name' => $result['FirstName'],
                'middle_name' => $result['MiddleName'],
                'last_name' => $result['LastName'],
            ];
    
            $deposit = MpesaDeposit::where('trans_id', $data['trans_id'])->first();
            if (!$deposit) trigger_error('Transaction could not be found. Try again later.');
            $deposit->update($data);
            return response()->json($deposit);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
}
