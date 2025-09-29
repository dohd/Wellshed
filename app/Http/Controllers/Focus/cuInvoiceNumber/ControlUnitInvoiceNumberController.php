<?php

namespace App\Http\Controllers\Focus\cuInvoiceNumber;

use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Models\creditnote\CreditNote;
use App\Models\cuInvoiceNumber\ControlUnitInvoiceNumber;
use App\Models\invoice\Invoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ControlUnitInvoiceNumberController extends Controller
{


    /**
     * Retrieves the current Cu Invoice Number
     * @return string
     */
    public function retrieveCuInvoiceNumber(bool $getCurrent = false)
    {

        $controlUnitInvoiceNumber = ControlUnitInvoiceNumber::first();

        if (!empty($controlUnitInvoiceNumber)){

            $isActive = $controlUnitInvoiceNumber->active;
            if (!boolval($isActive)) return '';
        }

        $cuInvoiceNo = empty($controlUnitInvoiceNumber) ? '' : $controlUnitInvoiceNumber->cu_no;

        if (empty($cuInvoiceNo)){

            try {
                DB::beginTransaction();

                $newCuInvoiceNumber  = new ControlUnitInvoiceNumber();
                $newCuInvoiceNumber->cuin_number = uniqid('CUIN' . auth()->user()->ins . '-');
                $newCuInvoiceNumber->cu_no = 1;
                $newCuInvoiceNumber->save();

                DB::commit();

            } catch (Exception $e){

                DB::rollBack();
                return "Error: '" . $e->getMessage() . " | on File: " . $e->getFile() . "  | & Line: " . $e->getLine();
            }

            $cuInvoiceNo = $newCuInvoiceNumber->cu_no;
        }

        if ($getCurrent) return $cuInvoiceNo;

        return $this->findClearNumber($cuInvoiceNo);
    }

    /**
     * Checks if a CU Invoice Number is cleared for use and returns a text reply.
     * @param Request $request
     * @return array
     */
    public function checkCuInvoiceNumber(Request $request): array
    {

        $proposedNumber = intval(request('cuNo'));

        if (empty($proposedNumber)) return [
            'isClear' => false,
            'message' => ''
        ];


        $isNumberClearForUse = $this->clearForUse($proposedNumber);

        if ($isNumberClearForUse) return [
            'isClear' => $isNumberClearForUse,
            'message' => 'Control Unit Invoice Number is clear for use according to your recorded invoices & credit notes'
        ];
        else return [
            'isClear' => $isNumberClearForUse,
            'message' => 'Control Unit Invoice Number is not cleared for use! The next cleared number is ' . $this->findClearNumber($proposedNumber)
        ];
    }

    /**
     * Checks if a CU Invoice Number is cleared for use.
     * That is, if it is not allocated to an Invoice or a Credit Note
     * @param string $cuInvoiceNumber
     * @return bool
     */
    public function clearForUse(string $cuInvoiceNumber){

        if ($cuInvoiceNumber <= 0) return false;

        $creditNotesCu = CreditNote::all()->pluck('cu_invoice_no');
        $invoicesCu = Invoice::all()->pluck('cu_invoice_no');

        $cuInvNos = array_merge($creditNotesCu->toArray(), $invoicesCu->toArray());

        $lastDigits = collect($cuInvNos)->map(function ($number) {

            $cuPrefix = explode('KRAMW', auth()->user()->business->etr_code)[1];
            if (Str::contains($number,$cuPrefix )) return explode($cuPrefix, $number)[1];

            return -1;
        })->toArray();

        $highestCuInvNo = collect($lastDigits)->max();

        if (!in_array($cuInvoiceNumber, $lastDigits) && $cuInvoiceNumber > $highestCuInvNo){
            return true;
        }

        return false;
    }

    /**
     * Increments the reference CU Invoice Number until one which is not allocated is found
     * @param string $cuInvoiceNumber
     * @return int
     */
    public function findClearNumber(string $cuInvoiceNumber): int
    {

        $unclearedNumbers = [];

        while ($this->clearForUse($cuInvoiceNumber) === false){
            $cuInvoiceNumber++;
            array_push($unclearedNumbers, $cuInvoiceNumber);
        }

        return $cuInvoiceNumber;
    }


    /**
     * This sets the CU invoice number
     * @param int $cuInvoiceNumber
     * @return array
     * @throws Exception
     */
    public function setCuInvoiceNumber(int $cuInvoiceNumber = 0)
    {

        if ($cuInvoiceNumber === 0) $cuInvoiceNumber = request('cuNo');

        $controlUnitInvoiceNumber = ControlUnitInvoiceNumber::first();
        $controlUnitInvoiceNumber->active = request('cuActive');
        $controlUnitInvoiceNumber->save();

        if (!request('cuActive')){

            return [
                'isSet' => true,
                'cu' => 'DEACTIVATED',
                'message' => "CU invoice Number Feature is Now Deactivated"
            ];
        }


        try {
            DB::beginTransaction();

            if (empty(ControlUnitInvoiceNumber::first())) $this->retrieveCuInvoiceNumber();

            if ($this->findClearNumber($cuInvoiceNumber) >= $cuInvoiceNumber){

                $controlUnitInvoiceNumber = ControlUnitInvoiceNumber::first();
                $controlUnitInvoiceNumber->cu_no = $cuInvoiceNumber;
                $controlUnitInvoiceNumber->save();
            }
            else return [
                'isSet' => false,
                'cu' => false,
                'message' => 'The next available CU Invoice Number is ' . $this->findClearNumber($cuInvoiceNumber)
            ];


            DB::commit();

        } catch (Exception $e){

            DB::rollBack();
            return [
                'isSet' => false,
                'cu' => false,
                'message' => "Error: '" . $e->getMessage() . " | on File: " . $e->getFile() . "  | & Line: " . $e->getLine()
            ];
        }

        return [
            'isSet' => true,
            'cu' => $cuInvoiceNumber,
            'message' => "CU invoice Number is Activated and set successfully to " . $cuInvoiceNumber
            ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cuPrefix = explode('KRAMW', auth()->user()->business->etr_code)[1];

        $this->retrieveCuInvoiceNumber(true);
        $currentCuInvNo = ControlUnitInvoiceNumber::first()->cu_no;

        $cuActive = ControlUnitInvoiceNumber::first()->active;

        return new ViewResponse('focus.cu_invoice_number.set', compact('cuPrefix', 'currentCuInvNo', 'cuActive'));

//        return view('focus.cu_invoice_number.set');
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\cuInvoiceNumber\ControlUnitInvoiceNumber  $controlUnitInvoiceNumber
     * @return \Illuminate\Http\Response
     */
    public function show(ControlUnitInvoiceNumber $controlUnitInvoiceNumber)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\cuInvoiceNumber\ControlUnitInvoiceNumber  $controlUnitInvoiceNumber
     * @return \Illuminate\Http\Response
     */
    public function edit(ControlUnitInvoiceNumber $controlUnitInvoiceNumber)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\cuInvoiceNumber\ControlUnitInvoiceNumber  $controlUnitInvoiceNumber
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ControlUnitInvoiceNumber $controlUnitInvoiceNumber)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\cuInvoiceNumber\ControlUnitInvoiceNumber  $controlUnitInvoiceNumber
     * @return \Illuminate\Http\Response
     */
    public function destroy(ControlUnitInvoiceNumber $controlUnitInvoiceNumber)
    {
        //
    }
}
