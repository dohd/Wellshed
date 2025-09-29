<?php

namespace App\Http\Controllers\focus\workshift;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\workshift\WorkshiftRepository;

class WorkshiftTableController extends Controller
{
     /**
     * variable to store the repository object
     * @var WorkshiftRepository
     */
    protected $workshift;

    /**
     * contructor to initialize repository object
     * @param workshiftRepository $assetorder ;
     */
    public function __construct(WorkshiftRepository $workshift)
    {
        $this->workshift = $workshift;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->workshift->getForDataTable();

        return Datatables::of($core)
            ->addIndexColumn()
            ->escapeColumns(['id'])
            ->addColumn('id', function ($workshift) {
                return $workshift->id;
            })
            ->addColumn('name', function ($workshift) {
                $name = $workshift->name;
                return $name;
            })
            
            ->addColumn('created_at', function ($workshift) {
                return $workshift->created_at;
            })
            ->addColumn('actions', function ($workshift) {
                return $workshift->action_buttons;
            })
            ->make(true);
    }
}
