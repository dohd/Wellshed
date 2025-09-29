<?php

namespace App\Http\Controllers\Focus\refill_customer;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\refill_customer\RefillCustomerRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class RefillCustomersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var RefillProductRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param RefillProductRepository $repository ;
     */
    public function __construct(RefillCustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('actions', function ($leave) {
                return $leave->action_buttons;
            })
            ->make(true);
    }
}
