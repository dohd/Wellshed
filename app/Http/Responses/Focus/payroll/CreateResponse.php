<?php

namespace App\Http\Responses\Focus\payroll;

use Illuminate\Contracts\Support\Responsable;
use App\Models\hrm\Hrm;
use App\Models\deduction\Deduction;

class CreateResponse implements Responsable
{
    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        
        return view('focus.payroll.create');
    }

    public function calculate_paye($gross_pay)
    {
         //Get PAYE brackets
         $tax = 0;
         $paye_brackets = Deduction::where('deduction_id','3')->get();
         $first_bracket = Deduction::where('deduction_id','3')->first();
         if($gross_pay > $first_bracket->amount_from){
            foreach ($paye_brackets as $i => $bracket) {
                if ($i > 0) {
                    if ($gross_pay > $bracket->amount_from) {
                        $tax += $bracket->rate / 100 * ($gross_pay - $bracket->amount_from);
                        
                    }
                }else {
                    if($gross_pay > $bracket->amount_to){
                        $tax += 25/100 * ($bracket->amount_to - $bracket->amount_from);
                    }
                    $tax += $bracket->rate / 100 * ($bracket->amount_from);
                }
             }
         }
         if($tax > 0)
            return $tax - 2655;
    }

    public function calculate_nssf($gross_pay)
    {
        $nssf_brackets = Deduction::where('deduction_id','2')->get();
        $nssf = 0;
        foreach ($nssf_brackets as $i => $bracket) {
            if($i > 0){
                if($gross_pay > $bracket->amount_from){
                    $nssf = $bracket->rate;
                }
            }else{
                $nssf = $bracket->rate/100 * $gross_pay;
            }
        }
        return $nssf;
    }

    public function calculate_nhif($gross_pay)
    {
        $nhif_brackets = Deduction::where('deduction_id','1')->get();
        $nhif = 0;
        foreach ($nhif_brackets as $i => $bracket) {
                if($gross_pay > $bracket->amount_from && $gross_pay <= $bracket->amount_to){
                    $nhif = $bracket->rate;
                }
        }
        return $nhif;
    }

    public function calculate_shif($gross_pay)
    {

        $shif = bcmul($gross_pay, 0.0275, 2);

        return max($shif, 300.00);
    }


}