<?php

namespace App\Http\Controllers\Focus\supplierBalance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\supplier\SuppliersController;
use App\Models\customer\Customer;
use App\Models\items\JournalItem;
use App\Models\supplier\Supplier;
use App\Repositories\Focus\supplier\SupplierRepository;
use DateTime;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SupplierBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {

            $supplierOutstanding = Supplier::orderBy('name')
                ->get()
                ->map(function ($s) {

                    $intervals = array();
                    for ($i = 0; $i < 5; $i++) {
                        $from = date('Y-m-d');
                        $to = date('Y-m-d', strtotime($from . ' - 30 days'));
                        if ($i > 0) {
                            $prev = $intervals[$i - 1][1];
                            $from = date('Y-m-d', strtotime($prev . ' - 1 day'));
                            $to = date('Y-m-d', strtotime($from . ' - 28 days'));
                        }
                        $intervals[] = [$from, $to];
                    }

                    // statement on bills
                    $bills = collect();
                    $bills_statement = (new SupplierRepository())->getStatementForDataTable($s->id);
                    foreach ($bills_statement as $row) {
                        if ($row->type == 'bill') $bills->add($row);
                        else {
                            $last_bill = $bills->last();
                            if ($last_bill->bill_id == $row->bill_id) {
                                $last_bill->debit += $row->debit;
                            }
                        }
                    }

                    // aging balance from extracted invoices
                    $aging_cluster = array_fill(0, 5, 0);
                    foreach ($bills as $bill) {
                        $due_date = new DateTime($bill->date);
                        $debt_amount = $bill->credit - $bill->debit;
                        // over payment
                        if ($debt_amount < 0) {
                            // $supplier->on_account += $debt_amount * -1;
                            $debt_amount = 0;
                        }
                        // due_date between 0 - 120 days
                        foreach ($intervals as $i => $dates) {
                            $start = new DateTime($dates[0]);
                            $end = new DateTime($dates[1]);
                            if ($start >= $due_date && $end <= $due_date) {
                                $aging_cluster[$i] += $debt_amount;
                                break;
                            }
                        }
                        // due_date in 120+ days
                        if ($due_date < new DateTime($intervals[4][1])) {
                            $aging_cluster[4] += $debt_amount;
                        }
                    }

                    $agingTotal = number_format(array_sum($aging_cluster), 2);

                    // supplier debt balance
                    $account_balance = collect($aging_cluster)->sum() - $s->on_account;

                    $keys = ['0To30Days', '31To60Days', '61To90Days', '91To120Days', '120+Days'];
                    $aging_cluster = array_map(fn($ac) => number_format($ac, 2), $aging_cluster);
                    $aging_cluster = array_combine($keys, $aging_cluster);

                    $prefix = prefixesArray(['supplier'], auth()->user()->ins);

                    return array_merge(
                        [
                            'tid' => '<a class="font-weight-bold" href="' . route('biller.suppliers.show', $s) . '">' . ($prefix ? $prefix[0] . '-' : 'CMR-') . $s->id . '</a>',
                            'name' => $s->name,
                        ],
                        $aging_cluster,
                        compact('agingTotal'),
                        [
                            'unallocated' => number_format($s->on_account, 2),
                            'balance' => number_format($account_balance, 2)
                        ]
                    );
                });

            return Datatables::of($supplierOutstanding)
                ->rawColumns(['tid', 'balance'])
                ->editColumn('balance', function ($bal) {

                    return '<span style="font-size: 16px"><b>' . $bal['balance'] . '</b></span>';
                })
                ->make(true);
        }

        return view('focus.supplierOutstanding.index');
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
