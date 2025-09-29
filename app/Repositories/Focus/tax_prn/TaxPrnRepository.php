<?php

namespace App\Repositories\Focus\tax_prn;

use App\Exceptions\GeneralException;
use App\Models\tax_prn\TaxPrn;
use App\Models\tax_report\TaxReport;
use App\Models\tax_report\TaxReportPrn;
use App\Repositories\BaseRepository;
use DateTime;
use DB;
use Illuminate\Validation\ValidationException;

class TaxPrnRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = TaxPrn::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return TaxPrn $tax_prn
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        if (substr($input['period_from'], 3) != substr($input['period_to'], 3))
            throw ValidationException::withMessages(['Return period must be of the same month']);

        foreach ($input as $key => $val) {
            if ($key == 'amount') $input[$key] = numberClean($val);
            if (in_array($key, ['ackn_date', 'prn_date', 'period_from', 'period_to'])) 
                $input[$key] = date_for_database($val);
            if (in_array($key, ['return_month'])) {
                $date = DateTime::createFromFormat('m-Y', $input[$key]);
                if ($date) $input[$key] = $date->format('m-Y');
                else throw ValidationException::withMessages(['Valid date format required mm-YYYY']);
            }
        }
        $result = TaxPrn::create($input);

        // attach tax_report with prn
        $tax_report_ids = TaxReport::where('return_month', 'LIKE', "%{$result->return_month}%")->pluck('id')->toArray();
        $attached_prns = array_map(fn($v) => [
            'tax_prn_id' => $result->id,
            'tax_report_id' => $v,
            'ins' => auth()->user()->ins,
        ], $tax_report_ids);
        TaxReportPrn::insert($attached_prns);

        DB::commit();
        return $result; 
    }

    /**
     * For updating the respective Model in storage
     *
     * @param TaxPrn $tax_prn
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(TaxPrn $tax_prn, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        if (substr($input['period_from'], 3) != substr($input['period_to'], 3))
            throw ValidationException::withMessages(['Return period must be of the same month']);

        foreach ($input as $key => $val) {
            if ($key == 'amount') $input[$key] = numberClean($val);
            if (in_array($key, ['ackn_date', 'prn_date', 'period_from', 'period_to'])) 
                $input[$key] = date_for_database($val);
            if (in_array($key, ['return_month'])) {
                $date = DateTime::createFromFormat('m-Y', $input[$key]);
                if ($date) $input[$key] = $date->format('m-Y');
                else throw ValidationException::withMessages(['Valid date format required mm-YYYY']);
            }
        }
        $tax_prn->update($input);
        
        // attach tax_report with prn
        $tax_report_ids = TaxReport::where('return_month', 'LIKE', "%{$tax_prn->return_month}%")->pluck('id')->toArray();
        $attached_prns = array_map(fn($v) => [
            'tax_prn_id' => $tax_prn->id,
            'tax_report_id' => $v,
            'ins' => auth()->user()->ins,
        ], $tax_report_ids);
        // update or create
        TaxReportPrn::where('tax_prn_id', $tax_prn->id)->whereNotIn('tax_report_id', $tax_report_ids)->delete();
        foreach ($attached_prns as $item) {
            TaxReportPrn::updateOrCreate(array_splice($item, 0, 2), $item);
        }

        if ($tax_prn) {
            DB::commit();
            return $tax_prn;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param TaxPrn $tax_prn
     * @throws GeneralException
     * @return bool
     */
    public function delete(TaxPrn $tax_prn)
    {
        DB::beginTransaction();

        $tax_prn->tax_reports()->detach();
        if ($tax_prn->delete()) {
            DB::commit();
            return true;
        }
    }
}
