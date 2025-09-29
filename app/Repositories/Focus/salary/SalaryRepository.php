<?php

namespace App\Repositories\Focus\salary;

use App\Exceptions\GeneralException;
use App\Models\allowance_employee\AllowanceEmployee;
use App\Models\salary\Salary;
use App\Models\salaryHistory\SalaryHistory;
use App\Repositories\BaseRepository;
use DateTime;
use DB;

/**
 * Class salaryRepository.
 */
class SalaryRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Salary::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()->latest()
            ->get()->unique('employee_id');
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create($input)
    {
        $createsalary = Salary::create($input['input']);

        $allarr= $input['employee_allowance'];
        $allarr = array_map(function ($v) use($createsalary) {

            return array_replace($v, [
                'contract_id' => $createsalary->id,
                'user_id'=> auth()->user()->id,
                'ins'=> auth()->user()->ins,
            ]);
        }, $allarr);
        if ($createsalary) {
            AllowanceEmployee::insert($allarr);

            $salaryHistory = new SalaryHistory();
            // Convert to array first, then exclude the desired columns
            $salaryData = collect($createsalary->toArray())->except(['id', 'created_at', 'updated_at'])->toArray();
            $salaryHistory->date = (new DateTime())->format("Y-m-d");
            $salaryHistory->fill($salaryData);
            $salaryHistory->salary_id = $createsalary->id;
            $salaryHistory->job_grade = optional(optional($createsalary->user)->meta)->job_grade ?? 'No Grade Set';
            $salaryHistory->save();

            return $salaryHistory;
        }

        throw new GeneralException(trans('exceptions.backend.salarys.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param salary $salary
     * @param  $input
     * @throws GeneralException
     * @throws \DateMalformedStringException
     * return bool
     */
    public function update($salary, array $input)
    {
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['start_date'], 1))
                $data[$key] = date_for_database($val);
        }

        $oldSalary = clone $salary;

        $salary->fill($data);
        $dirtySalary = $salary->isDirty();
        $salary->save();

        if ($dirtySalary){

            $history = SalaryHistory::where('salary_id', $salary->id)->first();

            if (!$history){

                $firstSalaryHistory = new SalaryHistory();
                // Convert to array first, then exclude the desired columns
                $oldSalaryData = collect($oldSalary)->except(['id', 'created_at', 'updated_at'])->toArray();
                $firstSalaryHistory->fill($oldSalaryData);
                $firstSalaryHistory->date = (new DateTime($oldSalary['updated_at']))->format("Y-m-d");
                $firstSalaryHistory->salary_id = $oldSalary['id'];
                $firstSalaryHistory->job_grade = optional(optional($oldSalary->user)->meta)->job_grade ?? 'No Grade Set';
                $firstSalaryHistory->save();
            }


            $salaryHistory = new SalaryHistory();
            // Convert to array first, then exclude the desired columns
            $salaryData = collect($salary->toArray())->except(['id', 'created_at', 'updated_at'])->toArray();
            $salaryHistory->fill($salaryData);
            $salaryHistory->date = (new DateTime($salary['updated_at']))->format("Y-m-d");
            $salaryHistory->salary_id = $salary->id;
            $salaryHistory->job_grade = optional(optional($salary->user)->meta)->job_grade ?? 'No Grade Set';
            $salaryHistory->save();
        }


        // quote line items
        $data_items = $input['data_items'];
        //dd($data_items);
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $data['ins'],
                'user_id' => $data['user_id'],
                'contract_id' => $salary->id
            ]);
            $data_item = AllowanceEmployee::firstOrNew(['id' => $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();
        }
        if ($salary) {
            DB::commit();
            return true;
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.salarys.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param salary $salary
     * @throws GeneralException
     * @return bool
     */
    public function delete(Salary $salary)
    {
        if ($salary->delete()&& $salary->employee_allowance->each->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.salarys.delete_error'));
    }
}
