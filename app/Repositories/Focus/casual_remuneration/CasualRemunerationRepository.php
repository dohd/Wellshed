<?php

namespace App\Repositories\Focus\casual_remuneration;

use App\Exceptions\GeneralException;
use App\Models\casual_labourer_remuneration\CasualLabourersRemuneration;
use App\Models\casual_labourer_remuneration\CLRAllocation;
use App\Models\casual_labourer_remuneration\CLRWage;
use App\Models\casual_labourer_remuneration\CLRWageItem;
use App\Models\labour_allocation\LabourAllocation;
use App\Repositories\BaseRepository;
use DB;
use Exception;
use Illuminate\Validation\ValidationException;

/**
 * Class CasualRemunerationRepository
 */
class CasualRemunerationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = CasualLabourersRemuneration::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        $q = $this->query()->withCount(['casualLabourers']);

        return $q->get();
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
        // dd($input);
        DB::beginTransaction();

        // create remuneration
        $remuneration = CasualLabourersRemuneration::create([
            'clr_number' => uniqid('CLR-'),
            'title' => $input['title'],
            'date' => date_for_database($input['date']),
            'description' => @$input['description'],
            'total_amount' => numberClean($input['total_amount']),
        ]);

        // create remuneration allocations
        foreach (@$input['labour_allocation_id'] ?: [] as $id){
            CLRAllocation::create([
                'clrla_number' => uniqid('CLRLA-', true),
                'clr_number' => $remuneration->clr_number,
                'labour_allocation_id' => $id,
            ]);
        }

        // create casual wages
        foreach ($input['casual_labourer_id'] as $key => $id) {
            CLRWage::create([
                'clrcl_number' => uniqid('CLRCL-', true),
                'clr_number' => $remuneration->clr_number,
                'casual_labourer_id' => $id,
                'labour_allocation_id' => $input['casual_la_id'][$key],
                'wage' => numberClean($input['wage'][$key]),
                'hours' => $input['hours'][$key],
                'remuneration' => numberClean($input['wage_total'][$key]),
                'regular_hrs' => numberClean($input['regular_hrs'][$key]),
                'overtime_hrs' => numberClean($input['overtime_hrs'][$key]),
                'ot_multiplier' => numberClean($input['ot_multiplier'][$key]),
                'overtime_total' => numberClean($input['overtime_total'][$key]),
                'regular_total' => numberClean($input['regular_total'][$key]),
                'wage_subtotal' => numberClean($input['wage_subtotal'][$key]),
                'wage_total' => numberClean($input['wage_total'][$key]),
            ]);
        }

        // create dynamic wage items
        if (array_filter(@$input['wage_item_total'] ?: [])) {
            $clIdChunks = array_chunk($input['wage_item_cl_id'], count($input['casual_la_id']));
            $wageItemIdChunks = array_chunk($input['wage_item_id'], count($input['casual_la_id']));
            $wageItemTotalChunks = array_chunk($input['wage_item_total'], count($input['casual_la_id']));
            foreach ($clIdChunks as $key => $chunk) {
                foreach ($chunk as $key1 => $id) {
                    CLRWageItem::create([
                        'clr_number' => $remuneration->clr_number,
                        'casual_labourer_id' => $id,
                        'labour_allocation_id' => $input['casual_la_id'][$key1],
                        'wage_item_id' => $wageItemIdChunks[$key][$key1],
                        'wage_item_total' => numberClean($wageItemTotalChunks[$key][$key1]),
                    ]);
                }
            }
        }

        DB::commit();
        return $remuneration;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($remuneration, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $result = $remuneration->update([
            'title' => $input['title'],
            'date' => date_for_database($input['date']),
            'description' => @$input['description'],
            'total_amount' => numberClean($input['total_amount']),
            'period_from' => @$input['period_from']? date_for_database($input['period_from']) : null,
            'period_to' => @$input['period_to']? date_for_database($input['period_to']) : null,
        ]);

        // create remuneration allocations
        $remuneration->labourAllocations()->detach();
        foreach (@$input['labour_allocation_id'] ?: [] as $id){
            CLRAllocation::create([
                'clrla_number' => uniqid('CLRLA-', true),
                'clr_number' => $remuneration->clr_number,
                'labour_allocation_id' => $id,
            ]);
        }

        // create casual wages
        $remuneration->casualLabourers()->detach();
        foreach ($input['casual_labourer_id'] as $key => $id) {
            CLRWage::create([
                'clrcl_number' => uniqid('CLRCL-', true),
                'clr_number' => $remuneration->clr_number,
                'casual_labourer_id' => $id,
                'labour_allocation_id' => $input['casual_la_id'][$key],
                'wage' => numberClean($input['wage'][$key]),
                'hours' => $input['hours'][$key],
                'remuneration' => numberClean($input['wage_total'][$key]),
                'regular_hrs' => numberClean($input['regular_hrs'][$key]),
                'overtime_hrs' => numberClean($input['overtime_hrs'][$key]),
                'ot_multiplier' => numberClean($input['ot_multiplier'][$key]),
                'overtime_total' => numberClean($input['overtime_total'][$key]),
                'regular_total' => numberClean($input['regular_total'][$key]),
                'wage_subtotal' => numberClean($input['wage_subtotal'][$key]),
                'wage_total' => numberClean($input['wage_total'][$key]),
            ]);
        }

        // create dynamic wage items
        $remuneration->clrWageItems()->delete();
        if (array_filter(@$input['wage_item_total'] ?: [])) {
            $clIdChunks = array_chunk($input['wage_item_cl_id'], count($input['casual_la_id']));
            $wageItemIdChunks = array_chunk($input['wage_item_id'], count($input['casual_la_id']));
            $wageItemTotalChunks = array_chunk($input['wage_item_total'], count($input['casual_la_id']));
            foreach ($clIdChunks as $key => $chunk) {
                foreach ($chunk as $key1 => $id) {
                    CLRWageItem::create([
                        'clr_number' => $remuneration->clr_number,
                        'casual_labourer_id' => $id,
                        'labour_allocation_id' => $input['casual_la_id'][$key1],
                        'wage_item_id' => $wageItemIdChunks[$key][$key1],
                        'wage_item_total' => numberClean($wageItemTotalChunks[$key][$key1]),
                    ]);
                }
            }
        }

        DB::commit();
        return $result;
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete($casualRemun)
    {
        $bill = $casualRemun->bill;

        $errorMsg = '';
        if ($casualRemun->status == 'APPROVED') $errorMsg = 'Casual Wage has been approved';
        if ($bill && $bill->payments()->exists()) $errorMsg = 'Casual Wage bill has been paid';
        if ($errorMsg) throw ValidationException::withMessages([$errorMsg]);

        DB::beginTransaction();

        if ($bill) {
            $bill->items()->delete();
            $bill->delete();
        }
        
        $casualRemun->casualLabourers()->detach();
        $casualRemun->labourAllocations()->detach();
        $casualRemun->clrWageItems()->delete();
        if ($casualRemun->delete()) {
            DB::commit();
            return true;
        }
    }
}
