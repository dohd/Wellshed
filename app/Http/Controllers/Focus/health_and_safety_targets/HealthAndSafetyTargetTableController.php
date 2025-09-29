<?php

namespace App\Http\Controllers\Focus\health_and_safety_targets;

use App\Http\Controllers\Controller;
use App\Models\health_and_safety_targets\HealthAndSafetyTarget;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class HealthAndSafetyTargetTableController extends Controller
{
    protected $healthAndSafetyTarget;

    public function __construct(HealthAndSafetyTarget $healthAndSafetyTarget)
    {
        $this->healthAndSafetyTarget = $healthAndSafetyTarget;
    }

    public function __invoke()
    {
        $core = $this->healthAndSafetyTarget->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($healthAndSafetyTarget) {
                return $healthAndSafetyTarget->name;
            })
            ->addColumn('actions', function ($healthAndSafetyTarget) {
                return $healthAndSafetyTarget->action_buttons;
            })
            ->make(true);
    }
}
