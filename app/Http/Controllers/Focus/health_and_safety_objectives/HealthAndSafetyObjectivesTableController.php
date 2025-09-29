<?php

namespace App\Http\Controllers\Focus\health_and_safety_objectives;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\health_and_safety_objectives\HealthAndSafetyObjectivesRepository;
use Yajra\DataTables\Facades\DataTables;

class HealthAndSafetyObjectivesTableController extends Controller
{
    protected $healthAndSafetyObjective;

    public function __construct(HealthAndSafetyObjectivesRepository $healthAndSafetyObjective)
    {
        $this->healthAndSafetyObjective = $healthAndSafetyObjective;
    }

    public function __invoke()
    {
        $core = $this->healthAndSafetyObjective->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($healthAndSafetyObjective) {
                return $healthAndSafetyObjective->name;
            })
            ->addColumn('actions', function ($healthAndSafetyTracking) {
                return $healthAndSafetyTracking->action_buttons;
            })
            ->make(true);
    }
}
