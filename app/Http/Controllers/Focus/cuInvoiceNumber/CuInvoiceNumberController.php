<?php

namespace App\Http\Controllers\Focus\cuInvoiceNumber;

use App\Http\Controllers\Controller;
use App\Models\Access\Permission\Permission;
use App\Models\creditnote\CreditNote;
use App\Models\cuInvoiceNumber\CuInvoiceNumber;
use App\Models\invoice\Invoice;
use Exception;
use Illuminate\Support\Facades\DB;

class CuInvoiceNumberController extends Controller
{


    /**
     * sets a new reference for CU Invoice Numbers
     * @param string $cuInvoiceNumber
     * @return mixed
     * @throws Exception
     */
    public function set(){

        $creditNotesCu = CreditNote::all()->pluck('cu_invoice_no');
        $invoicesCu = Invoice::all()->pluck('cu_invoice_no');

        $cuInvNos = array_merge($creditNotesCu->toArray(), $invoicesCu->toArray());

        // '0430405030000000281'
        $cuInvoiceNumber = $this->findClearNumber(max($cuInvNos));

        try {
            DB::beginTransaction();

            DB::table('cu_invoice_numbers')->lockForUpdate()->get();

            $cuInvNo = CuInvoiceNumber::find(1);

            if (empty($cuInvNo)){
                $newCuInvoiceNumber = new CuInvoiceNumber();
                $newCuInvoiceNumber->id = 1;
                $newCuInvoiceNumber->value = $cuInvoiceNumber;
                $newCuInvoiceNumber->save();
            }
            else {
                $cuInvNo->value = $cuInvoiceNumber;
                $cuInvNo->save();
            }

//            CuInvoiceNumber::updateOrInsert(
//                ['id' => 1],
//                ['value' => $cuInvoiceNumber]
//            );

            DB::commit();
        } catch (Exception $e){
            DB::rollBack();
            throw $e;
        }

        return DB::table('cu_invoice_numbers')->first()->value;
    }

    /**
     * Retrieves the current reference CU Invoice Number
     * @return mixed
     */
    public function get(){

        return $cuInvoiceNumber = CuInvoiceNumber::first()->value;
    }


    /**
     * Retrieves the next reference CU Invoice Number
     * @return string
     */
    public function getNext(): string
    {

        $etrCode = explode('KRAMW', auth()->user()->business->etr_code)[1];

        $cuInvoiceNumber = empty(CuInvoiceNumber::first()) ? $etrCode . '001' : CuInvoiceNumber::first()->value;

        return $this->findClearNumber($cuInvoiceNumber);
    }


    /**
     * Checks if a CU Invoice Number is cleared for use.
     * That is, if it is not allocated to an Invoice or a Credit Note
     * @param string $cuInvoiceNumber
     * @return bool
     */
    public function clearForUse(string $cuInvoiceNumber){

        $creditNotesCu = CreditNote::all()->pluck('cu_invoice_no');
        $invoicesCu = Invoice::all()->pluck('cu_invoice_no');

        $cuInvNos = array_merge($creditNotesCu->toArray(), $invoicesCu->toArray());

        if (array_search($cuInvoiceNumber, $cuInvNos) === false){
            return true;
        }

        return false;
    }

    /**
     * Increments the reference CU Invoice Number until one which is not allocated is found
     * @param string $cuInvoiceNumber
     * @return string
     */
    public function findClearNumber(string $cuInvoiceNumber): string
    {

        $unclearedNumbers = [];

        while ($this->clearForUse($cuInvoiceNumber) === false){
            $cuInvoiceNumber = '0' . (intval($cuInvoiceNumber) + 1);
            array_push($unclearedNumbers, $cuInvoiceNumber);
        }

        return $cuInvoiceNumber;
    }

    /**
     * Allocates the CU Invoice Number and Increments it
     * @throws Exception
     */
    public function allocate(){


        try {
            DB::beginTransaction();

            $latestCuInv = DB::table('cu_invoice_numbers')->lockForUpdate()->first();

            DB::table('cu_invoice_numbers')->update(['value' => $this->findClearNumber($latestCuInv->value)]);

            DB::commit();
        } catch (Exception $e){
            DB::rollBack();
            throw $e;
        }

        return CuInvoiceNumber::first()->value;
    }

//    public function createPerms()
//    {
//
//        Permission::create([
//            'name' => 'manage-daily-logs',
//            'display_name' => 'EDL Manage Permission',
//        ]);
//    }

}
