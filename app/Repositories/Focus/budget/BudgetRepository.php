<?php

namespace App\Repositories\Focus\budget;

use App\Exceptions\GeneralException;
use App\Models\project\Budget;
use App\Models\project\BudgetItem;
use App\Models\project\BudgetSkillset;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class BudgetRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Budget::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('project_id'), function ($q) {
            $q->whereHas('quote', function ($q) {
                $q->whereHas('project', fn($q) => $q->where('projects.id', request('project_id')));
            });
        });
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return Budget $budget
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();
        
        foreach ($input as $key => $val) {
            if (in_array($key, ['quote_total', 'budget_total', 'labour_total'])) 
                $input[$key] = numberClean($val);
        }     
        $data = Arr::only($input, ['quote_total', 'budget_total', 'labour_total', 'note', 'quote_id']);       
        $result = Budget::create($data);

        // budget items
        $data_items = Arr::only($input, [
            'numbering', 'row_index', 'a_type', 'product_id', 'product_name',            
            'product_qty', 'unit', 'new_qty',  'price','misc'
        ]);
        $data_items = modify_array($data_items);
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'budget_id' => $result->id,
                'price' => $v['price'] > 0 ? numberClean($v['price']) : 0
            ]);
        }, $data_items); 
        BudgetItem::insert($data_items);

        // budget labour items
        $data_skillset = Arr::only($input, ['skillitem_id', 'skill', 'charge', 'hours', 'no_technician']);
        $data_skillset = modify_array($data_skillset);
        foreach ($data_skillset as $item) {
            if (!$item['skill']) continue;
            $item = array_replace($item, [
                'charge' => numberClean($item['charge']),
                'budget_id' => $result->id,
                'quote_id' => $result->quote_id
            ]);
            $skillset = BudgetSkillset::firstOrNew(['id' => $item['skillitem_id']]);
            $skillset->fill($item);
            if (!$skillset->id) unset($skillset->id);
            unset($skillset->skillitem_id);
            $skillset->save();
        }
        
        if ($result) {
            DB::commit();
            return $result; 
        }
            
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Budget $budget
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(Budget $budget, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if (in_array($key, ['quote_total', 'budget_total', 'labour_total'])) 
                $input[$key] = numberClean($val);
        }     
        $data = Arr::only($input, ['quote_total', 'budget_total', 'labour_total', 'note', 'quote_id']);    
        $result = $budget->update($data);

        // budget items
        $data_items = Arr::only($input, [
            'numbering', 'row_index', 'a_type', 'product_id', 'product_name',            
            'product_qty', 'unit', 'new_qty',  'price', 'item_id'
        ]);
        $data_items = modify_array($data_items); 
        // new or update item
        $budget->items()->whereNotIn('id', array_map(fn($v) => $v['item_id'], $data_items))->delete();
        foreach($data_items as $item) {
            $item = array_replace($item, [
                'price' => numberClean($item['price']),
                'new_qty' => numberClean($item['new_qty']),
                'budget_id' => $budget->id,
            ]);
            $new_item = BudgetItem::firstOrNew(['id' => $item['item_id']]);
            $new_item->fill($item);
            if (!$new_item->id) unset($new_item->id);
            unset($new_item->item_id);
            $new_item->save();
        }

        // budget labour items
        $data_skillset = Arr::only($input, ['skillitem_id', 'skill', 'charge', 'hours', 'no_technician']);
        $data_skillset = modify_array($data_skillset);
        // create or update items
        $budget->skillsets()->whereNotIn('id', array_map(fn($v) => $v['skillitem_id'], $data_skillset))->delete();
        foreach($data_skillset as $item) {
            if (!$item['skill']) continue;
            $new_item = BudgetSkillset::firstOrNew([
                'id' => $item['skillitem_id'],
                'budget_id' => $budget->id,
                'quote_id' => $budget->quote_id,
                'charge' => numberClean($item['charge']),
            ]);
            $new_item->fill($item);
            if (!$new_item->id) unset($new_item->id);
            unset($new_item->skillitem_id);
            $new_item->save();
        }
        
        if ($result) {
            DB::commit();
            return $budget;
        }        

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Budget $budget
     * @throws GeneralException
     * @return bool
     */
    public function delete(Budget $budget)
    {   
        DB::beginTransaction();
        if ($budget->items->contains(function ($item) {
            return $item->stock_issue_items()->exists();
        })) {
            throw ValidationException::withMessages(['Budget Item has attached Issuance Items']);
        }
        if ($budget->items->contains(function ($item) {
            return $item->material_requst_items()->exists();
        })) {
            throw ValidationException::withMessages(['Budget Item has attached Material Requisition Items']);
        }
        $budget->items()->delete();
        if ($budget->delete()) {
            DB::commit();
            return true;
        }            
    }
}
