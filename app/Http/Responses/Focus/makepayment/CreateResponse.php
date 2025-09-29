<?php

namespace App\Http\Responses\Focus\makepayment;
use Illuminate\Contracts\Support\Responsable;

class CreateResponse implements Responsable
{


      protected $view;

    /**
     * @var array
     */
    protected $with;

    /**
     * @param string $view
     * @param array  $with
     */
    public function __construct($view, $with = [])
    {
        $this->view = $view;
        $this->with = $with;
    }


    public function toResponse($request)
    {
          //$equipment=Equipment::all();
         

            /*return view('focus.makepayment.single_payment')->with([
            'transactions' => $transactions
        ])->with(array('last_id'=>$last_id))->with(bill_helper(3,9));

*/
         return view($this->view)->with($this->with);

        //return view('focus.purchases.create',compact('last_id'));
    }
}