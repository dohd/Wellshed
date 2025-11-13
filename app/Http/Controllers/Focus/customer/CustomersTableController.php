<?php

namespace App\Http\Controllers\Focus\customer;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\customer\CustomerRepository;
use App\Http\Requests\Focus\customer\ManageCustomerRequest;
use Illuminate\Support\Facades\Request;

/**
 * Class CustomersTableController.
 */
class CustomersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var CustomerRepository
     */
    protected $customer;
    protected $balance = 0;

    /**
     * contructor to initialize repository object
     * @param CustomerRepository $customer ;
     */
    public function __construct(CustomerRepository $customer)
    {
        $this->customer = $customer;
    }

    /**
     * This method return the data of the model
     * @param ManageCustomerRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
            
        $core = $this->customer->getForDataTable();
        // foreach ($core as $key => $customer) {
        //     $customer->update(['tid' => $key+1]);
        // }

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function($customer) {
                return gen4tid('CRM-', $customer->tid);
            })
            ->editColumn('name', function($customer) {
                $customerName = $customer->name; 
                return '<a class="font-weight-bold" href="' . route('biller.customers.show', $customer) . '">' . $customerName . '</a>';
            })
            ->addColumn('company', function ($customer) {
                $company = $customer->company;                
                return '<a class="font-weight-bold" href="' . route('biller.customers.show', $customer) . '">' . $company . '</a>';
            })
            ->addColumn('balance', function ($customer) {
                $bal = $customer->paymentReceipts->sum(fn($v) => $v->debit - $v->credit);
                return numberFormat($bal);
            })
            ->addColumn('actions', function ($customer) {
                $customer->action_buttons;
            })
            ->make(true);
    }
}
