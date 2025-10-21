<?php

namespace App\Http\Controllers\Focus\hrm;

use App\Http\Controllers\Controller;
use App\Models\department\Department;
use App\Models\hrm\HrmMeta;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\hrm\HrmRepository;
use App\Http\Requests\Focus\hrm\ManageHrmRequest;
use Illuminate\Support\Facades\Storage;

/**
 * Class HrmsTableController.
 */
class HrmsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var HrmRepository
     */
    protected $hrm;

    /**
     * contructor to initialize repository object
     * @param HrmRepository $hrm ;
     */
    public function __construct(HrmRepository $hrm)
    {
        $this->hrm = $hrm;
    }

    /**
     * This method return the data of the model
     * @param ManageHrmRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageHrmRequest $request)
    {
        $core = $this->hrm->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($hrm) {
                return gen4tid('EMP-',$hrm->tid);
            })
            ->addColumn('name', function ($hrm) {
                if (request('rel_type') == 3) return '<a class="font-weight-bold" href="' . route('biller.transactions.index') . '?rel_type=3&rel_id=' . $hrm->id . '">' . $hrm->first_name . ' ' . $hrm->last_name . '</a> <small> ' . $hrm->phone . '</small>';
                return '<a class="font-weight-bold" href="' . route('biller.hrms.show', [$hrm->id]) . '">' . $hrm->first_name . ' ' . $hrm->last_name . '</a> <small> ' . $hrm->phone . '</small>';
            })
            ->addColumn('email', function ($hrm) {
                return $hrm->email ?? '';
            })            
            ->addColumn('active', function ($hrm) {
                $c = '';
                if ($hrm->status) $c = 'checked';
                return '<div class="user_active icheckbox_flat-aero ' . $c . '" data-cid="' . $hrm->id . '" data-active="' . $hrm->status . '"></div>';
            })
            ->addColumn('role', function ($hrm) {
                $role = $hrm->role;
                if ($role) return $role->name;
            })
            ->addColumn('customer', function ($hrm) {
                
            })            
            ->addColumn('actions', function ($hrm) {
                return $hrm->action_buttons;
            })
            ->make(true);
    }
}
