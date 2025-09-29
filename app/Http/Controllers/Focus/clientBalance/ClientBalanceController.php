<?php

namespace App\Http\Controllers\Focus\clientBalance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\customer\CustomersController;
use App\Models\customer\Customer;
use App\Models\transaction\Transaction;
use App\Repositories\Focus\customer\CustomerRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClientBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {

            $customerBalances = Customer::orderBy('company')
                ->get()
                ->map(function ($c) {

                    $invoices = (new CustomersController(new CustomerRepository()))->statement_invoices($c);
                    $aging_cluster = (new CustomersController(new CustomerRepository()))->aging_cluster($c, $invoices);

                    $total_aging = 0;

                    $keys = ['0To30Days', '31To60Days', '61To90Days', '91To120Days', '120+Days'];
                    $vals = [];
                    for ($u = 0; $u < count($aging_cluster); $u++) {

                        array_push($vals, number_format($aging_cluster[$u], 2));
                        $total_aging += $aging_cluster[$u];
                    }

                    $agingDays = array_combine($keys, $vals);
                    $prefix = prefixesArray(['customer'], auth()->user()->ins);

                    return array_merge(
                        [
                            'tid' => '<a class="font-weight-bold" href="' . route('biller.customers.show', $c) . '">' . ($prefix ? $prefix[0] . '-' : 'CMR-') . $c->id . '</a>',
                            'name' => $c->company,
                        ],
                        $agingDays,
                        [
                            'agingTotal' => number_format($total_aging, 2),
                            'unallocated' => number_format($c->on_account, 2),
                            'balance' => ($total_aging == 0 && $c->on_account > 0) ? number_format($c->on_account, 2) : number_format($total_aging - $c->on_account, 2),
                        ]
                    );
                });


            return Datatables::of($customerBalances)
                ->rawColumns(['tid', 'balance'])
                ->editColumn('balance', function ($bal) {

                    return '<span style="font-size: 16px"><b>' . $bal['balance'] . '</b></span>';
                })
                ->make(true);
        }

        return view('focus.clientBalance.index');
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
