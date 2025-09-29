<?php

namespace App\Repositories\Focus\queuerequisition;

use DB;
use Carbon\Carbon;
use App\Models\queuerequisition\QueueRequisition;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use App\Models\project\BudgetItem;

/**
 * Class queuerequisitionRepository.
 */
class QueueRequisitionRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = QueueRequisition::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get(['id','item_name','qty_balance','uom','quote_no','client_branch','status','system_name','product_code','item_qty','created_at']);
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
       $single_input = $input['single_input'];
       $input = $input['input'];
       //dd($single_input['ins']);

       $input = array_map(function ($v) use($single_input) {
        //dd($v);
        return array_replace($v, [
            'ins' => $single_input['ins'],
            'user_id' => $single_input['user_id'],
            'quote_no' => $single_input['quote_id'],
            'client_branch' => $single_input['client_branch'],
        ]);
         }, $input);
         //unset($input['budget_item_id']);
         
         foreach ($input as $inputs) {
            $budget_item = BudgetItem::find($inputs['budget_item_id']);
            $budget_item->new_qty = $budget_item->new_qty - $inputs['qty_balance'];
            $budget_item->update();
         }
        
        // QueueRequisition::insert($input);
        if (QueueRequisition::insert($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.queuerequisitions.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param queuerequisition $queuerequisition
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(QueueRequisition $queuerequisition, array $input)
    {
        //dd($input);
        //$input = array_map($input);
    	if ($queuerequisition->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.queuerequisitions.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param queuerequisition $queuerequisition
     * @throws GeneralException
     * @return bool
     */
    public function delete(Queuerequisition $queuerequisition)
    {
        $budget_item = BudgetItem::find($queuerequisition->budget_item_id);
        if ($budget_item) {
            $budget_item->new_qty = $budget_item->new_qty + $queuerequisition->qty_balance;
            $budget_item->update();
        }
        if ($queuerequisition->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.queuerequisitions.delete_error'));
    }
}
