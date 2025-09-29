<?php

namespace App\Http\Controllers\Focus\labour_allocation;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Models\casual\CasualLabourer;

/**
 * Class CasualWagesTableController.
 */
class CasualWagesTableController extends Controller
{
    public function getCasuals() {
        // check required params
        $reqParams = request()->only('labour_allocation_id', 'clr_number', 'period_from', 'period_to');
        if (!array_filter($reqParams)) return collect();

        // collect casuals from varying labour allocations
        $casuals = collect();
        foreach (request('labour_allocation_id', []) as $laId) {
            $q = CasualLabourer::query();
            $q->whereHas('labourAllocations', function ($q) use($laId) {
                $q->whereIn('id', [$laId]);
                $q->where('is_payable', 1);
            });
            $q->with([
                'wageItems',
                'casualWeeklyHrs' => function ($q) use($laId) {
                    $q->when(request('labour_allocation_id'), function($q) use($laId) {
                        $q->whereIn('labour_allocation_id', [$laId]);
                    });
                },
                'labourAllocations' => function ($q) use($laId) {
                    $q->when(request('labour_allocation_id'), function($q) use($laId) {
                        $q->whereIn('id', [$laId]);
                        $q->where('is_payable', 1);
                    });
                },
                'clrWageItems' => fn ($q) => $q->where('clr_number', request('clr_number')),
                'clrWages' => fn ($q) => $q->where('clr_number', request('clr_number')),
            ]);
            $casualsBatch = $q->get()
            ->map(function ($casual) use($laId) {
                $wage = +$casual->rate;
                if (!$wage && $casual->job_category) {
                    $wage = +$casual->job_category->rate;
                }
                $casual['wage'] = $wage;
                $casual['regular_hrs'] = $casual->labourAllocations->sum('hrs');
                $casual['overtime_hrs'] = $casual->labourAllocations->sum('overtime_hrs');
                // filter weekly hours
                $casual->casualWeeklyHrs = $casual->casualWeeklyHrs->filter(function($v) use($casual) {
                    return $v->casual_labourer_id == $casual->id;
                });
                if ($casual->casualWeeklyHrs->count()) {
                    $casual['regular_hrs'] = $casual->casualWeeklyHrs->whereNull('is_overtime')->sum('total_reg_hrs');
                    $casual['overtime_hrs'] = $casual->casualWeeklyHrs->whereNotNull('is_overtime')->sum('total_ot_hrs');
                }
                $casual['labour_allocation_id'] = (int) $laId;
                return $casual;
            });
            $casuals = $casuals->merge($casualsBatch);
        }        

        return $casuals;
    }


    protected $overtime_hrs = 0;

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->getCasuals();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('labour_allocation_id', function($casual) {
                return $casual->labour_allocation_id;
            })
            ->editColumn('id_number', function ($casual) {
                return $casual->id_number . '<input type="hidden" name="casual_la_id[]" value="'.$casual->labour_allocation_id.'" class="la-id">';
            })            
            ->editColumn('phone_number', function ($casual) {
                $phoneNo = $casual->phone_number;
                $char = substr($phoneNo, 0, 1);
                if ($char === '0') $phoneNo = '254' . substr($phoneNo, 1);
                return $phoneNo;
            })
            ->addColumn('overtime_hrs', function($casual) {
                $overtimeHrs = $casual['overtime_hrs'];
                return $overtimeHrs . '<input type="hidden" name="overtime_hrs[]" value="'.$overtimeHrs.'" class="overtime-hrs">';
            })
            ->addColumn('regular_hrs', function($casual) {
                return $casual['regular_hrs'].'<input type="hidden" name="regular_hrs[]" value="'.$casual['regular_hrs'].'" class="regular-hrs">';
            })
            ->editColumn('total_hrs', function ($casual) {
                $totalHrs = $casual['regular_hrs'] + $casual['overtime_hrs'];
                return '<span class="total-hrs-txt">'.$totalHrs.'</span>'.'<input type="hidden" name="hours[]" class="total-hrs" value="'.$totalHrs.'">';
            })
            ->editColumn('wage', function ($casual) {
                $clrCasualLabourer = null;
                if (request('clr_number')) {
                    $clrCasualLabourer = $casual->clrWages->first();
                }
                if (!empty(request('isShowing'))) return number_format($clrCasualLabourer->wage, 2);
                if ($clrCasualLabourer) {
                    return '<input type="hidden" name="casual_labourer_id[]" value="'.$casual["id"].'">'.
                    '<input type="text" name="wage[]" class="wage form-control" value="' .$clrCasualLabourer->wage.'" required>';
                } else {
                    return '<input type="hidden" name="casual_labourer_id[]" value="'.$casual["id"].'">'.
                    '<input type="text" name="wage[]" class="wage form-control" value="'.$casual["wage"].'" required>';
                }
            })
            ->addColumn('ot_multiplier', function($casual) {
                $overtimeHrs = $this->overtime_hrs;
                $weekdayTotal = 0;
                $weekendTotal = 0;
                $wageItem = $casual->wageItems->where('earning_type', 'overtime')->first();
                $overtimeLog = $casual->casualWeeklyHrs->whereNotNull('is_overtime')->first();
                if ($wageItem && $overtimeLog) {
                    foreach ($overtimeLog->getAttributes() as $key => $value) {
                        // holidays
                        // ** set holiday logic **
                        
                        if (in_array($key, ['mon', 'tue', 'wed', 'thu', 'fri'])) {
                            $weekdayTotal += $value * $wageItem->weekday_ot;
                        } elseif ($key == 'sat') {
                             $weekendTotal += $value * $wageItem->weekend_sat_ot;
                        } elseif ($key == 'sun') {
                            $weekendTotal += $value * $wageItem->weekend_sun_ot;
                        }
                    }
                    $this->overtime_hrs = $weekdayTotal+$weekendTotal;
                    $overtimeHrs = $this->overtime_hrs;
                }
                return '<span class="ot-multiplier-txt">'.$overtimeHrs.'</span><input type="hidden" name="ot_multiplier[]" value="'.$overtimeHrs.'" class="ot-multiplier">';
            })
            ->addColumn('wage_total', function($casual) {
                // remuneration column
                return '<span class="wage-total-txt"></span><input type="hidden" name="wage_total[]" class="wage-total">';
            })
            ->addColumn('wage_subtotal', function($casual) {
                return '<span class="wage-subtotal-txt"></span><input type="hidden" name="wage_subtotal[]" class="wage-subtotal">';
            })
            ->addColumn('overtime_total', function($casual) {
                return '<span class="overtime-total-txt"></span><input type="hidden" name="overtime_total[]" class="overtime-total">';
            })
            ->addColumn('regular_total', function($casual) {
                return '<span class="regular-total-txt"></span><input type="hidden" name="regular_total[]" class="regular-total">';
            })
            ->make(true);
    }
}
