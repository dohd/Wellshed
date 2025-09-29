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

namespace App\Http\Controllers\Focus\client_user;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\client_user\ClientUserRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClientUsersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ClientUserRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ClientUserRepository $repository ;
     */
    public function __construct(ClientUserRepository $repository)
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
            ->addColumn('customer', function ($client_user) {
                return @$client_user->customer->company;
            })
            ->addColumn('branch', function ($client_user) {
                return @$client_user->branch->name;
            })
            ->addColumn('user', function ($client_user) {
                return @$client_user->user->full_name;
            })
            ->addColumn('email', function ($client_user) {
                return @$client_user->user->email;
            })
            ->addColumn('actions', function ($client_user) {
                return $client_user->action_buttons;
            })
            ->make(true);
    }
}
