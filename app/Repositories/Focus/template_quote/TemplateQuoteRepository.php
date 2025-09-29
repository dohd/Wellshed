<?php

namespace App\Repositories\Focus\template_quote;

use App\Models\items\QuoteItem;
use App\Models\items\VerifiedItem;

use App\Models\quote\Quote;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

use App\Models\lead\Lead;
use App\Models\project\BudgetSkillset;
use App\Models\verifiedjcs\VerifiedJc;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\quote\EquipmentQuote;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\labour_allocation\LabourAllocationItem;
use App\Models\template_quote\TemplateQuote;
use App\Models\template_quote\TemplateQuoteItem;

/**
 * Class QuoteRepository.
 */
class TemplateQuoteRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = TemplateQuote::class;

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
     * @return $quote
     */
    public function create(array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'reference_date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax']))
                $data[$key] = numberClean($val);
        }   
        // increament tid
        // $tid = 0;
        // if (isset($data['bank_id'])) {
        //     $tid = Quote::where('ins', $data['ins'])
        //         ->where('bank_id', '>', 0)->max('tid');
        // } else {
        //     $tid = Quote::where('ins', $data['ins'])
        //         ->where('bank_id', 0)->max('tid');
        // }
        // if ($data['tid'] <= $tid) $data['tid'] = $tid+1;

        // close lead
        // Lead::find($data['lead_id'])->update(['status' => 1, 'reason' => 'won']);
        $result = TemplateQuote::create($data);

        // quote line items
        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'quote_id' => $result->id, 
                'ins' => $result->ins,
                'product_price' =>  floatval(str_replace(',', '', $v['product_price'])),
                'product_subtotal' => floatval(str_replace(',', '', $v['product_subtotal'])),
                'buy_price' => floatval(str_replace(',', '', $v['buy_price'])),
                'estimate_qty' => floatval(str_replace(',', '', $v['estimate_qty'])),
                'product_amount' => floatval(str_replace(',', '', $v['product_price'])) * floatval(str_replace(',', '', $v['product_qty'])),
            ]);
        }, $data_items);

        TemplateQuoteItem::insert($data_items);

        // quote labour items
        // $skill_items = $input['skill_items'];
        // $skill_items = array_map(function ($v) use($result) {
        //     return array_replace($v, ['quote_id' => $result->id]);
        // }, $skill_items);
        // BudgetSkillset::insert($skill_items);
        // quote Equipments
        // $equipments = $input['equipments'];
        // $equipments = array_map(function ($v) use($result) {
        //     return array_replace($v, [
        //         'quote_id' => $result->id,
        //         'ins' => $result->ins,
        //         'user_id' => auth()->user()->id,
        //     ]);
        // }, $equipments);
        // EquipmentQuote::insert($equipments);
        
        if ($result) {
            DB::commit();
            return $result;
        }
        
        throw new GeneralException('Error Creating Quote');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Quote $quote
     * @param  $input
     * @throws GeneralException
     * @return $quote
     */
    public function update($quote, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'reference_date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax'])) 
                $data[$key] = numberClean($val);
        }   
        // update lead status
        // if ($quote->lead_id != $data['lead_id']) {
        //     $quote->lead->update(['status' => 0, 'reason' => 'new']);
        //     Lead::find($data['lead_id'])->update(['status' => 1, 'reason' => 'won']);
        // }
        
        $result = $quote->update($data);

        $data_items = $input['data_items'];
        // remove omitted items
        // $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        // $quote->products()->whereNotIn('id', $item_ids)->delete();

        TemplateQuoteItem::where('quote_id',$quote->id)->delete();

        // create or update items
        // foreach($data_items as $item) {
        //     foreach ($item as $key => $val) {
        //         if (in_array($key, ['product_price', 'product_subtotal', 'buy_price', 'estimate_qty']))
        //             $item[$key] = floatval(str_replace(',', '', $val));
        //     }
        //     $quote_item = TemplateQuoteItem::firstOrNew(['id' => $item['id']]);
        //     $quote_item->fill(array_replace($item, ['quote_id' => $quote['id'], 'ins' => $quote['ins']]));
        //     if (!$quote_item->id) unset($quote_item->id);
        //     $quote_item->save();
        // }
        $data_items = array_map(function ($v) use($quote) {
            return array_replace($v, [
                'quote_id' => $quote->id, 
                'ins' => $quote->ins,
                'product_price' =>  floatval(str_replace(',', '', $v['product_price'])),
                'product_subtotal' => floatval(str_replace(',', '', $v['product_subtotal'])),
                'buy_price' => floatval(str_replace(',', '', $v['buy_price'])),
                'estimate_qty' => floatval(str_replace(',', '', $v['estimate_qty'])),
                'product_amount' => floatval(str_replace(',', '', $v['product_price'])) * floatval(str_replace(',', '', $v['product_qty'])),
            ]);
        }, $data_items);
        TemplateQuoteItem::insert($data_items);

        // $skill_items = $input['skill_items'];
        // // remove omitted items
        // $skill_ids = array_map(function ($v) { return $v['skill_id']; }, $skill_items);
        // $quote->skill_items()->whereNotIn('id', $skill_ids)->delete();
        // // create or update items
        // foreach($skill_items as $item) {
        //     $skillset = BudgetSkillset::firstOrNew(['id' => $item['skill_id']]);         
        //     $skillset->fill(array_replace($item, ['quote_id' => $quote->id]));
        //     if (!$skillset->id) unset($skillset->id);
        //     unset($skillset->skill_id);
        //     $skillset->save();
        // }
        
        // $equipments = $input['equipments'];
        // // remove omitted items
        // $item_ids = array_map(function ($v) { return $v['eqid']; }, $equipments);
        // $quote->equipments()->whereNotIn('id', $item_ids)->delete();
        
        // // create or update items
        // foreach($equipments as $item) {
        //     $equipment = EquipmentQuote::firstOrNew(['id' => $item['eqid']]);         
        //     $equipment->fill(array_replace($item, ['quote_id' => $quote->id]));
        //     if (!$equipment->id) unset($equipment->id);
        //     unset($equipment->eqid);
        //     //dd($equipment);
        //     $equipment->save();
        // }

        if ($result) {
            DB::commit();
            return $quote;      
        }
               
        throw new GeneralException('Error Updating Quote');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Quote $quote
     * @throws GeneralException
     * @return bool
     */
    public function delete($quote)
    {
        DB::beginTransaction();
        // $quote->delete();

        // $type = $quote->bank_id ? 'PI' : 'Quote';
        // if ($quote->project_quote) 
        //     throw ValidationException::withMessages([$type . ' is attached to a project!']);
            
        if ($quote->delete()) {
            // if ($quote->lead) $quote->lead->update(['status' => 0, 'reason' => 'new']);
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.quotes.delete_error'));
    }


    /**
     * Verify Budgeted Project Quote
     */
    public function verify(array $input)
    {
        //  dd($input);
        DB::beginTransaction();

        /** update quote verification */
        $data = $input['data'];
        $quote = Quote::find($data['id']);
        $result = $quote->update([
            'verified' => 'Yes', 
            'verification_date' => date('Y-m-d'),
            'verified_by' => auth()->user()->id,
            'gen_remark' => $data['gen_remark'],
            'project_closure_date' => date_for_database($data['project_closure_date']),
            'verified_amount' => numberClean($data['subtotal']),
            'verified_total' => numberClean($data['total']),
            'verified_tax' => numberClean($data['tax']), 
        ]);

        /** verified products */
        $data_items = $input['data_items'];
        // update or create verified item
        $item_ids = array_map(fn($v) => $v['item_id'], $data_items);
        VerifiedItem::where('quote_id', $data['id'])->whereNotIn('id', $item_ids)->delete();
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'quote_id' => $data['id'],
                'product_qty' => numberClean($item['product_qty']),
                'product_price' => floatval(str_replace(',', '', $item['product_price'])),
                'product_subtotal' => floatval(str_replace(',', '', $item['product_subtotal'])),
                'ins' => auth()->user()->ins
            ]);
            $verify_item = VerifiedItem::firstOrNew(['id' => $item['item_id']]);
            $verify_item->fill($item);
            if (!$verify_item->id) unset($verify_item->id);
            unset($verify_item->item_id);
            $verify_item->save();
        }

        /** job  cards */
        $job_cards = $input['job_cards'];   
        // duplicate jobcard reference
        $references = array_map(fn($v) => $v['reference'], $job_cards);
        $references = VerifiedJc::whereIn('reference', $references)->pluck('reference')->toArray();
        // update or create verified jobcards
        $item_ids = array_map(fn($v) => $v['jcitem_id'], $job_cards);
        VerifiedJc::where('quote_id', $data['id'])->whereNotIn('id', $item_ids)->delete(); 
        foreach ($job_cards as $item) {
            // skip duplicate reference
            if (in_array($item['reference'], $references) && !$item['jcitem_id']) continue;
            $item = array_replace($item, [
                'quote_id' => $data['id'],
                'date' => date_for_database($item['date']),
            ]);
            $jobcard = VerifiedJc::firstOrNew(['id' => $item['jcitem_id']]);
            $jobcard->fill($item);
            if (!$jobcard->id) unset($jobcard->id);
            unset($jobcard->jcitem_id);
            $jobcard->save();
        }

        /**labour allocation */
        $labour_items = $input['labour_items'];
        foreach ($labour_items as $item) {
            if (!$quote->project) continue;
            
            if ($item['job_employee']) {
                $jobcard_no = trim($item['job_jobcard_no']);
                $verified_jc = VerifiedJc::where('quote_id', $quote->id)->where('reference', $jobcard_no)->first();
                $employee_ids = array_filter(explode(',', $item['job_employee']));
                $job_hrs = numberClean($item['job_hrs']);
                // if ($job_hrs > 14) continue;
                
                foreach ($employee_ids as $id) {
                    $labour_data = [
                        'employee_id' => $id,
                        'project_id' => $quote->project->id,
                        'date' => date_for_database($item['job_date']),
                        'ref_type' => $item['job_ref_type'],
                        'job_card' => $jobcard_no,
                        'note' => $item['job_note'],
                        'hrs' => $job_hrs,
                        'type' => $item['job_type'],
                        'is_payable' => $item['job_is_payable'],
                        'verified_jc_id' => @$verified_jc->id,
                        'user_id' => auth()->user()->id,
                        'ins' => auth()->user()->ins,
                    ];
                    // date validation
                    if (strtotime($labour_data['date']) > strtotime(date('Y-m-d'))) continue;
                    // $one_week_earlier = strtotime(date('Y-m-d')) - (7 * 24 * 60 * 60);
                    // if (strtotime($labour_data['date']) < $one_week_earlier) continue; 

                    // save allocation
                    $labour_allocation = LabourAllocation::updateOrCreate([
                        'employee_id' => $id,
                        'job_card' => $labour_data['job_card'],
                    ], $labour_data);
                    // save allocation items
                    unset($labour_data['project_id'], $labour_data['verified_jc_id']);
                    $labour_data['labour_id'] = $labour_allocation->id;
                    LabourAllocationItem::updateOrCreate([
                        'employee_id' => $id,
                        'job_card' => $labour_data['job_card'],
                    ],$labour_data);
                }
            }
        }

        if ($result) {
            DB::commit();
            return $quote;      
        }
    }
}
