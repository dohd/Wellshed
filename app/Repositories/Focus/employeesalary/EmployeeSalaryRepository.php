<?php

namespace App\Repositories\Focus\employeesalary;

use DB;
use Carbon\Carbon;
use App\Models\employeesalary\EmployeeSalary;
use App\Exceptions\GeneralException;
use App\Models\employeeallowance\EmployeeAllowance;
use App\Models\nssf\Nssf;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EmployeeSalaryRepository.
 */
class EmployeeSalaryRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = EmployeeSalary::class;
    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        return $this->query()
            ->get(['id', 'name', 'type', 'is_taxable', 'created_at']);
    }
    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        try {
            $data = $input['input_salary'];
            foreach ($data as $key => $val) {
                if (in_array($key, ['effective_date'], 1))
                    $data[$key] = date_for_database($val);
                if (in_array($key, ['taxable_allowance', 'untaxable_allowance', 'salary'], 1))
                    $data[$key] = numberClean($val);
            }
            $data['contact_end_date'] = Carbon::parse($data['effective_date'])->addMonths($data['contact_duration'])->format('Y-m-d');;
            $data['nssf'] = Nssf::where('id', $data['nssf_id'])->value('rate'); //Get NSSF
            $basic_salary = $data['taxable_allowance'] + $data['salary'];
            $data['nhif'] = nhif_rates($basic_salary);
            $data['net_pay'] = calculate_paye($basic_salary, $data['nhif'], $data['nssf'], $data['untaxable_allowance'])['net_pay'];
            $data['paye'] = calculate_paye($basic_salary, $data['nhif'], $data['nssf'], $data['untaxable_allowance'])['paye'];
            DB::beginTransaction();
            $salaries = EmployeeSalary::where('id', $data['user_id'])->get();
            if (!empty($salaries)) {
                foreach ($salaries as $salary) {
                    $updateothers = EmployeeSalary::find($salary->id);
                    $updateothers->status = 'Inactive';
                    $updateothers->save();
                }
            }
            $result = EmployeeSalary::create($data);
            //calculate net pay
            if ($result) {
                $data_items = $input['input_allowance'];
                $items = [];
                foreach ($data_items['allowance_deduction_category_id']  as $key => $val) {
                    if ($data_items['amount'][$key] > 0) {
                        $items[] = array(
                            'salary_history_id' => $result->id,
                            'amount' => numberClean($data_items['amount'][$key]),
                            'alloance_deduction_category_id' => numberClean($data_items['allowance_deduction_category_id'][$key]),
                        );
                    }
                }
                EmployeeAllowance::insert($items);
                DB::commit();
                return true;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new GeneralException(trans('exceptions.backend.employeesalary.create_error'));
        }
    }
    /**
     * For updating the respective Model in storage
     *
     * @param EmployeeSalary $employeesalary
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(EmployeeSalary $employeesalary, array $input)
    {
        $input = array_map('strip_tags', $input);
        if ($employeesalary->update($input))
            return true;
        throw new GeneralException(trans('exceptions.backend.employeesalary.update_error'));
    }
    /**
     * For deleting the respective model from storage
     *
     * @param EmployeeSalary $employeesalary
     * @throws GeneralException
     * @return bool
     */
    public function delete(EmployeeSalary $employeesalary)
    {
        if ($employeesalary->delete()) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.employeesalary.delete_error'));
    }
}
