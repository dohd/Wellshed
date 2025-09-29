<?php

namespace App\Http\Controllers\Focus\bank_feed;

use App\Http\Controllers\Controller;
use App\Models\bank\Bank;
use App\Models\bank\BankFeed;
use App\Models\reconciliation\Reconciliation;
use App\Models\transaction\Transaction;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Log;
use SimpleXMLElement;

class BankFeedsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $RTBalance = 0;
        $bank = Bank::where('username', 'NCBA')->first();
        if ($bank && $bank->feed_begin_balance && $bank->feed_begin_date) {
            $date = dateFormat($bank->feed_begin_date);
            $sql2 = "
                STR_TO_DATE(
                    CONCAT(
                        RIGHT(trans_time, 2), '-', 
                        SUBSTRING(trans_time, 5, 2), '-', 
                        CONCAT('20', SUBSTRING(trans_time, 5, 2))
                    ), '%d-%m-%Y'
                ) > STR_TO_DATE('{$date}', '%d-%m-%Y')
            ";
            $feedSum = BankFeed::selectRaw("SUM(trans_amount) trans_amount")
                ->where(\DB::raw($sql2))
                ->sum('trans_amount');
            $RTBalance = $bank->feed_begin_balance + $feedSum;
        }

        // NCBA Leanventures (Ledger account id - 134)
        $balance = 0;
        $latestRecon = Reconciliation::where('account_id', 134)->orderBy('ending_period', 'DESC')->first();
        $startDate = @$latestRecon->ending_period? date('Y-m-01', strtotime($latestRecon->ending_period)) : '';
        if ($startDate) {
            $balance = Transaction::where('account_id', 134)
                ->where('tr_date', '>=', $startDate)
                ->sum(\DB::raw('debit-credit'));
        }
        
        return view('focus.banks.bank_feed', compact('RTBalance', 'balance'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Get the raw XML payload from the request
            $xmlContent = $request->getContent();
            $xmlContent = $this->sanitizeXmlString($xmlContent);

            // Load the XML string
            $xml = new SimpleXMLElement($xmlContent);
            // Register the namespaces to access elements properly
            $xml->registerXPathNamespace('soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
            // Extract the Body content
            $xmlObject = $xml->xpath('//soapenv:Body/NCBAPaymentNotificationRequest');
            if (!$xmlObject) throw new Exception('No Matching Element Found.');
            // Process the XML data
            $xmlbody = json_decode(json_encode($xmlObject[0]), true); // Convert to array if needed
            // Log::info('Received XML Request:', $xmlbody);

            $bank = Bank::where('username', $xmlbody['User'])->first();
            $isValid = $bank && Hash::check($xmlbody['Password'], $bank->password);
            if (!$isValid) throw new Exception('Unauthorized');
            $bankFeed = BankFeed::create([
                'is_test' => strpos($request->url(), 'test') !== false? 1 : 0,
                'bank_id' => $bank->id,
                'ins' => $bank->ins,
                'hash_val' => $xmlbody['HashVal'],
                'trans_type' => $xmlbody['TransType'],
                'trans_id' => $xmlbody['TransID'],
                'trans_time' => $xmlbody['TransTime'],
                'trans_amount' => numberClean($xmlbody['TransAmount']),
                'account_nr' => $xmlbody['AccountNr'],
                'narrative' => @$xmlbody['Narrative'] ?: '',
                'phone_nr' => @$xmlbody['PhoneNr'] ?: '',
                'customer_name' => $xmlbody['CustomerName'],
                'status' => $xmlbody['Status'],
                'ft_cr_narration' => @$xmlbody['FtCrNarration'] ?: '',
            ]);

            $response = "<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'>
                <soapenv:Header/>
                <soapenv:Body>
                <NCBAPaymentNotificationResult>
                <Result>OK: {$bankFeed->id}</Result>
                </NCBAPaymentNotificationResult>
                </soapenv:Body>
            </soapenv:Envelope>";
            return response($response, 200)->header('Content-Type', 'application/xml');
        } catch (Exception $e) {
            Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            $errorMsg = $e->getMessage();
            $response = "<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'>
                <soapenv:Header/>
                <soapenv:Body>
                <NCBAPaymentNotificationResult>
                <Result>FAIL: {$errorMsg}</Result>
                </NCBAPaymentNotificationResult>
                </soapenv:Body>
            </soapenv:Envelope>";
            return response($response, 400)->header('Content-Type', 'application/xml');
        }
    }

    /**
     * Sanitize xml string without regex
     */
    public function sanitizeXmlString($xmlString) {
        // Remove quotes from the string
        $xmlString = trim($xmlString, '"');
        // Convert to a binary-safe format for processing
        $xmlString = mb_convert_encoding($xmlString, 'UTF-8', 'UTF-8');

        $cleanString = '';
        $length = mb_strlen($xmlString, 'UTF-8');
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($xmlString, $i, 1, 'UTF-8');
            $ord = mb_ord($char, 'UTF-8');
            // Allow valid XML character ranges
            if (
                ($ord === 0x9) || ($ord === 0xA) || ($ord === 0xD) || 
                ($ord >= 0x20 && $ord <= 0xD7FF) || 
                ($ord >= 0xE000 && $ord <= 0xFFFD) || 
                ($ord >= 0x10000 && $ord <= 0x10FFFF)
            ) {
                $cleanString .= $char;
            }
        }
        // Remove backslashes
        $cleanString = stripslashes($cleanString);
        return $cleanString;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
