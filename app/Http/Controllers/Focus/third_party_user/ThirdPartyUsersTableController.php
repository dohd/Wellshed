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
namespace App\Http\Controllers\Focus\third_party_user;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Focus\third_party_user\ThirdPartyUserRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class ThirdPartyUsersTableController.
 */
class ThirdPartyUsersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var third_party_userRepository
     */
    protected $third_party_user;

    /**
     * contructor to initialize repository object
     * @param third_party_userRepository $third_party_user ;
     */
    public function __construct(ThirdPartyUserRepository $third_party_user)
    {
        $this->third_party_user = $third_party_user;
    }

    /**
     * This method return the data of the model
     * @param Managethird_party_userRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->third_party_user->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($third_party_user) {
                 return $third_party_user->name;
            })
            ->addColumn('phone', function ($third_party_user) {
                return $third_party_user->phone;
            })
            ->addColumn('created_at', function ($third_party_user) {
                return Carbon::parse($third_party_user->created_at)->toDateString();
            })
            ->addColumn('actions', function ($third_party_user) {
                return $third_party_user->action_buttons;
            })
            ->make(true);
    }
}
