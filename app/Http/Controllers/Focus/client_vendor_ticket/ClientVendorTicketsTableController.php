<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\client_vendor_ticket;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\client_vendor_ticket\ClientVendorTicketRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClientVendorTicketsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(ClientVendorTicketRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('customer_id', function ($ticket) {
                return @$ticket->customer->company;
            })
            ->addColumn('category', function ($ticket) {
                return @$ticket->category->name;
            })
            ->editColumn('tid', function ($ticket) {
                return gen4tid('TKT-', $ticket->tid);
            })
            ->editColumn('date', function ($ticket) {
                return date('d-M-Y', strtotime($ticket->date));
            })
            ->addColumn('progress', function ($ticket) {
                return @$ticket->tag->name;
            })
            ->editColumn('status', function ($ticket) {
                $variant = 'badge-secondary';
                if ($ticket->status == 'Closed') $variant = 'badge-success';
                return '<span class="badge '. $variant .'">'. $ticket->status .'</span>';
            })
            ->addColumn('actions', function ($ticket) {
                return $ticket->action_buttons;
            })
            ->make(true);
    }
}
