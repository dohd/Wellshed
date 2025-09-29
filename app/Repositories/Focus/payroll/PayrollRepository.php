<?php

namespace App\Repositories\Focus\payroll;

use DateTime;
use DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Models\payroll\Payroll;
use App\Models\payroll\PayrollItemV2;
use App\Exceptions\GeneralException;
use App\Models\items\UtilityBillItem;
use App\Models\supplier\Supplier;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\BaseRepository;
use App\Repositories\Accounting;

/**
 * Class payrollRepository.
 */
class PayrollRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = Payroll::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        
        $q->when(request('month'), function ($q) {            
            $q->where('payroll_month', request('month'))
            ->orWhere('payroll_month', date('M Y', strtotime(request('month') . '-01')));
        });

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws \Exception
     * @throws GeneralException
     */
    public function create(array $input)
    {
        $year = Carbon::createFromFormat('Y-m', $input['payroll_month'])->format('Y');
        $month = Carbon::createFromFormat('Y-m', $input['payroll_month'])->format('m');
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = Carbon::createFromDate($year, $month, $startDate->daysInMonth);
        //$working_days = $startDate->diffInWeekdays($endDate);
        $working_days = $startDate->diffInDaysFiltered(function (Carbon $date) {
            return $date->isWeekday() || $date->isSaturday();
        }, $endDate);
        $total_month_days = $startDate->daysInMonth;
        //dd();
        $input['working_days'] = $working_days;
        $input['total_month_days'] = $total_month_days;
        $input['total_month_days'] = $total_month_days;
        $input['payroll_month'] = (new DateTime($input['payroll_month']))->format('M Y');
        //dd($input);
        $input = array_map( 'strip_tags', $input);
        $res = Payroll::create($input);
        if ($res) {
            return $res->id;
        }
        throw new GeneralException(trans('exceptions.backend.payrolls.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param payroll $payroll
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Payroll $payroll, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($payroll->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.payrolls.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param payroll $payroll
     * @throws GeneralException
     * @return bool
     */
    public function delete(Payroll $payroll)
    {
        if ($payroll->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.payrolls.delete_error'));
    }
    public function create_basic(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'salary_total'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->salary_total = $data['salary_total'];
        $result->processing_date = date_for_database($data['processing_date']);
        $result->update();

        //dd($result);
        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
                'payroll_id' => $result->id,
            ]);
        }, $data_items);
        //dd($data_items);
        PayrollItemV2::insert($data_items);
        
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.purchasedatas.create_error'));
    }
    public function create_allowance(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'allowance_total'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->allowance_total = $data['allowance_total'];
        $result->update();

        //dd($result);
        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->id,
            ]);
           // dd($item);
            $data_item = PayrollItemV2::firstOrNew(['id'=> $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();
        }
        
        
        
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.purchasedatas.create_error'));
    }
    public function create_deduction(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'deduction_total','total_nssf'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->deduction_total = $data['deduction_total'];
        $result->total_nssf = $data['total_nssf'];
        $result->update();

        //dd($result);
        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->id,
            ]);
           // dd($item);
            $data_item = PayrollItemV2::firstOrNew(['id'=> $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();
        }
        
        
        
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.payroll.create_error'));
    }

    public function create_nhif(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'total_nhif'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->total_nhif = $data['total_nhif'];
        $result->update();

        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.payroll.create_error'));
    }
   
    public function create_paye(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'paye_total'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->paye_total = $data['paye_total'];
        $result->update();

        //dd($result);
        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->id,
            ]);
           // dd($item);
            $data_item = PayrollItemV2::firstOrNew(['id'=> $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();
        }
        
        
        
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.payroll.create_error'));
    }
    public function create_other_deduction(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'other_benefits_total',
                'other_deductions_total',
                'other_allowances_total'
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->other_benefits_total = $data['other_benefits_total'];
        $result->other_deductions_total = $data['other_deductions_total'];
        $result->other_allowances_total = $data['other_allowances_total'];
        $result->update();

        //dd($result);
        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->id,
            ]);
           // dd($item);
            $data_item = PayrollItemV2::firstOrNew(['id'=> $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();
        }
        
        
        
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.payroll.create_error'));
    }
    public function create_summary(array $input)
    {
         
        DB::beginTransaction();
       // dd($input);
        $data = $input['data'];
        foreach ($data as $key => $val) {
            $rate_keys = [
                'total_netpay',
                
            ];
        }
        $result = Payroll::find($data['payroll_id']);
        $result->total_netpay = $data['total_netpay'];
        $result->update();

        //dd($result);
        $data_items = $input['data_items'];
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $result->ins,
                'user_id' => $result->id,
            ]);
           // dd($item);
            $data_item = PayrollItemV2::firstOrNew(['id'=> $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();
        }
        
        
        
        
        if ($result) {
            DB::commit();
            return $result;   
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.payroll.create_error'));
    }

    /**
     * Create Payroll Bill
     * 
     * @param Payroll $payroll
     */
    public function create_payroll_bill(Payroll $payroll)
    {
        $error_msg = '';
        $salaries_payable = Supplier::whereHas('ap_account', fn($q) => $q->whereHas('account_type_detail', fn($q) => $q->where('system', 'salaries_payable')))->first();
        if (!$salaries_payable) $error_msg = 'Salaries Payable (Supplier) required';
        $payroll_taxes_payable = Supplier::whereHas('ap_account', fn($q) => $q->whereHas('account_type_detail', fn($q) => $q->where('system', 'payroll_taxes_payable')))->first();
        if (!$payroll_taxes_payable) $error_msg = 'Payroll Taxes Payable (Supplier) required';
        $health_ins_payable = Supplier::whereHas('ap_account', fn($q) => $q->whereHas('account_type_detail', fn($q) => $q->where('system', 'health_insurance_payable')))->first();
        if (!$health_ins_payable) $error_msg = 'Health Insurance Payable (Supplier) required';
        $retirement_contrib_payable = Supplier::whereHas('ap_account', fn($q) => $q->whereHas('account_type_detail', fn($q) => $q->where('system', 'retirement_contribution_payable')))->first();
        if (!$retirement_contrib_payable) $error_msg = 'Retirement Contribution Payable (Supplier) required';
        $other_payroll_payable = Supplier::whereHas('ap_account', fn($q) => $q->whereHas('account_type_detail', fn($q) => $q->where('system', 'other_payroll_payable')))->first(); 
        if (!$other_payroll_payable && +$payroll->total_ahl) $error_msg = 'Other Payroll Payable (Supplier) required';
        if ($error_msg) throw ValidationException::withMessages([$error_msg]);

        $total_gross_pay = $payroll->total_gross_pay;
        $bill_data = [
            'tid' => UtilityBill::max('tid')+1,
            'supplier_id' => $salaries_payable->id,
            'document_type' => 'payroll',
            'ref_id' => $payroll->id,
            'date' => $payroll->processing_date,
            'due_date' => $payroll->processing_date,
            'subtotal' => $payroll->total_net_pay,
            'total' => $payroll->total_net_pay,
            'note' => 'Net Pay',
            'payroll_id' => $payroll->id,
        ];
        // Net Pay
        if ($payroll->total_net_pay > 0) {
            $bill = UtilityBill::create($bill_data);
            UtilityBillItem::create([
                'bill_id' => $bill->id,
                'note' => 'Gross Wage',
                'qty' => 1,
                'subtotal' => $bill->total,
                'total' => $bill->total,
            ]);
            $total_gross_pay -= round($payroll->total_net_pay,2);
            $this->post_payroll($bill, 'net_pay');
        }
        // Employee Deductions (P.A.Y.E)
        if ($payroll->total_paye > 0) {
            $bill = UtilityBill::create(array_replace($bill_data, [
                'tid' => UtilityBill::max('tid')+1,
                'supplier_id' => $payroll_taxes_payable->id,
                'subtotal' => $payroll->total_paye,
                'total' => $payroll->total_paye,
                'note' => 'Income TAX: P.A.Y.E',
            ]));
            UtilityBillItem::create([
                'bill_id' => $bill->id,
                'note' => 'Gross Wage',
                'qty' => 1,
                'subtotal' => $bill->total,
                'total' => $bill->total,
            ]);
            $total_gross_pay -= round($payroll->total_paye,2);
            $this->post_payroll($bill, 'paye');
        }
        // Employee Deductions (Statutories: NSSF, NHIF, SHIF, AHL, Other Deductions)
        $deductions = array_filter([
            $payroll->total_nssf, $payroll->total_nhif, $payroll->total_shif, 
            $payroll->total_ahl, $payroll->total_other_deductions
        ], fn($v) => floatval($v) > 0);
            
        if ($deductions) {
            // Social Security
            if ($payroll->total_nssf > 0) {
                $bill = UtilityBill::create(array_replace($bill_data, [
                    'tid' => UtilityBill::max('tid')+1,
                    'supplier_id' => $retirement_contrib_payable->id,
                    'subtotal' => $payroll->total_nssf,
                    'total' => $payroll->total_nssf,
                    'note' => 'Employee Deduction: Retirement Contribution',
                ]));
                UtilityBillItem::create([
                    'bill_id' => $bill->id,
                    'note' => 'Gross Wage',
                    'qty' => 1,
                    'subtotal' => $bill->total,
                    'total' => $bill->total,
                ]);
                $total_gross_pay -= round($payroll->total_nssf,2);
                $this->post_payroll($bill, 'nssf');
            }
            // Health Insurance
            $health_ins_total = $payroll->total_nhif > 0? $payroll->total_nhif : ($payroll->total_shif > 0? $payroll->total_shif : null);
            if ($health_ins_total > 0) {
                $tr_type = $payroll->total_nhif > 0? 'nhif' : ($payroll->total_shif > 0? 'shif' : null);
                $bill = UtilityBill::create(array_replace($bill_data, [
                    'tid' => UtilityBill::max('tid')+1,
                    'supplier_id' => $health_ins_payable->id,
                    'subtotal' => $health_ins_total,
                    'total' => $health_ins_total,
                    'note' => 'Employee Deduction: Health Insurance',
                ]));
                UtilityBillItem::create([
                    'bill_id' => $bill->id,
                    'note' => 'Gross Wage',
                    'qty' => 1,
                    'subtotal' => $health_ins_total,
                    'total' => $health_ins_total,
                ]);
                $total_gross_pay -= round($health_ins_total,2);
                $this->post_payroll($bill, $tr_type);
            }
            // Affordable Housing Levy
            if ($payroll->total_ahl > 0) {
                $bill = UtilityBill::create(array_replace($bill_data, [
                    'tid' => UtilityBill::max('tid')+1,
                    'supplier_id' => $other_payroll_payable->id,
                    'subtotal' => $payroll->total_ahl,
                    'total' => $payroll->total_ahl,
                    'note' => 'Employee Deduction: Housing Levy',
                ]));
                UtilityBillItem::create([
                    'bill_id' => $bill->id,
                    'note' => 'Gross Wage',
                    'qty' => 1,
                    'subtotal' => $payroll->total_ahl,
                    'total' => $payroll->total_ahl,
                ]);
                $total_gross_pay -= round($payroll->total_ahl,2);
                $this->post_payroll($bill, 'ahl');
            }

            // Other Deductions
            if ($payroll->total_other_deductions > 0) {
                $bill = UtilityBill::create(array_replace($bill_data, [
                    'tid' => UtilityBill::max('tid')+1,
                    'supplier_id' => $other_payroll_payable->id,
                    'subtotal' => $payroll->total_other_deductions,
                    'total' => $payroll->total_other_deductions,
                    'note' => 'Employee Deduction: Housing Levy',
                ]));
                UtilityBillItem::create([
                    'bill_id' => $bill->id,
                    'note' => 'Gross Wage',
                    'qty' => 1,
                    'subtotal' => $payroll->total_other_deductions,
                    'total' => $payroll->total_other_deductions,
                ]);
                $total_gross_pay -= round($payroll->total_other_deductions,2);
                $this->post_payroll($bill, 'other_deductions');
            }
        }
        // validate gross pay balance
        $grossPayBal = $total_gross_pay - $payroll->total_advances;
        if ($grossPayBal != 0) {
            $grossPayBal = numberFormat($grossPayBal);
            throw ValidationException::withMessages(["Computation Error: Gross pay balance is {$grossPayBal} instead of 0"]);
        }
        // Employer Taxes (NSSF, NHIF, SHIF, NITA, HELB)
        if ($deductions || $payroll->total_nita > 0 || $payroll->total_helb > 0) {
            // Social Security
            if ($payroll->total_nssf > 0) {
                $bill = UtilityBill::create(array_replace($bill_data, [
                    'tid' => UtilityBill::max('tid')+1,
                    'supplier_id' => $payroll_taxes_payable->id,
                    'subtotal' => $payroll->total_nssf,
                    'total' => $payroll->total_nssf,
                    'note' => 'Employer Deduction: Retirement Contribution',
                ]));
                UtilityBillItem::create([
                    'bill_id' => $bill->id,
                    'note' => $bill->note,
                    'qty' => 1,
                    'subtotal' => $bill->total,
                    'total' => $bill->total,
                ]);
                $this->post_payroll($bill, 'employer_nssf');
            }
            // Health Insurance
            $health_ins_total = $payroll->total_nhif > 0? $payroll->total_nhif : ($payroll->total_shif > 0? $payroll->total_shif : null);
            if ($health_ins_total > 0) {
                $tr_type = $payroll->total_nhif > 0? 'employer_nhif' : ($payroll->total_shif > 0? 'employer_shif' : null);
                $bill = UtilityBill::create(array_replace($bill_data, [
                    'tid' => UtilityBill::max('tid')+1,
                    'supplier_id' => $payroll_taxes_payable->id,
                    'subtotal' => $health_ins_total,
                    'total' => $health_ins_total,
                    'note' => 'Employer Deduction: Health Insurance',
                ]));
                UtilityBillItem::create([
                    'bill_id' => $bill->id,
                    'note' => $bill->note,
                    'qty' => 1,
                    'subtotal' => $health_ins_total,
                    'total' => $health_ins_total,
                ]);
                $this->post_payroll($bill, $tr_type);
            }
            // Industrial Training
            if ($payroll->total_nita > 0) {
                $bill = UtilityBill::create(array_replace($bill_data, [
                    'tid' => UtilityBill::max('tid')+1,
                    'supplier_id' => $payroll_taxes_payable->id,
                    'subtotal' => $payroll->total_nita,
                    'total' => $payroll->total_nita,
                    'note' => 'Employer Deduction: Industrial Training',
                ]));
                UtilityBillItem::create([
                    'bill_id' => $bill->id,
                    'note' => $bill->note,
                    'qty' => 1,
                    'subtotal' => $bill->total,
                    'total' => $bill->total,
                ]);
                $this->post_payroll($bill, 'employer_nita');
            }
            // Higher Education Loan
            if ($payroll->total_helb > 0) {
                $bill = UtilityBill::create(array_replace($bill_data, [
                    'tid' => UtilityBill::max('tid')+1,
                    'supplier_id' => $payroll_taxes_payable->id,
                    'subtotal' => $payroll->total_helb,
                    'total' => $payroll->total_helb,
                    'note' => 'Employer Deduction: Higher Education Loan',
                ]));
                UtilityBillItem::create([
                    'bill_id' => $bill->id,
                    'note' => $bill->note,
                    'qty' => 1,
                    'subtotal' => $bill->total,
                    'total' => $bill->total,
                ]);
                $this->post_payroll($bill, 'employer_helb');
            }
        }
        // Employee Advances
        if ($payroll->total_advances > 0) {
            $bill = UtilityBill::create(array_replace($bill_data, [
                'tid' => UtilityBill::max('tid')+1,
                'supplier_id' => $salaries_payable->id,
                'subtotal' => $payroll->total_advances,
                'total' => $payroll->total_advances,
                'note' => 'Salary Advance',
            ]));
            UtilityBillItem::create([
                'bill_id' => $bill->id,
                'note' => $bill->note,
                'qty' => 1,
                'subtotal' => $payroll->total_advances,
                'total' => $payroll->total_advances,
            ]);
            $this->post_payroll($bill, 'advances');
        }
    }
}
