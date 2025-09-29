<?php

namespace App\Repositories\Focus\verification;

use App\Exceptions\GeneralException;
use App\Models\items\VerificationItem;
use App\Models\quote\Quote;
use App\Models\verification\Verification;
use App\Models\verification\VerificationJc;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class VerificationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Verification::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('customer_id'), fn($q) => $q->where('customer_id', request('customer_id')));
        $q->when(request('lpo_id'), function ($q) {
            $q->whereHas('quote', fn($q) => $q->where('lpo_id', request('lpo_id')));
        });
        $q->when(request('project_id'), function ($q) {
            $q->whereHas('quote', function ($q) {
                $q->whereHas('project_quote', fn($q) => $q->where('project_id', request('project_id')));
            });
        });
            
        return $q->get();
    }

    public function getForVerificationQuoteDataTable()
    {
        $q = Quote::query()->whereColumn('total', '>', 'verified_total');
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return Verification $verification
     */
    public function create(array $input)
    {
        // dd($input);
        foreach ($input as $key => $value) {
            if (in_array($key, ['verification_date'])) $input[$key] = date_for_database($value);
            if (in_array($key, ['taxable', 'subtotal', 'tax', 'total']))
                $input[$key] = numberClean($value);
            if (in_array($key, ['product_subtotal', 'product_tax', 'product_total'])) {
                if (is_array($value)) $input[$key] = array_map(fn($v) => floatval(str_replace(',', '', $v)), $value);                 ;
            }
            if (in_array($key, ['date'])) {
                if (is_array($value)) $input[$key] = array_map(fn($v) => date_for_database($v), $value);
            }
        }

        $data = Arr::only($input, ['quote_id', 'customer_id', 'branch_id', 'note', 'taxable', 'subtotal', 'tax', 'total']);
        $data_items = Arr::only($input, [
            'numbering', 'product_name', 'unit', 'tax_rate', 'product_qty', 'product_subtotal', 'product_tax', 'product_total', 'remark', 
            'row_index', 'a_type', 'product_id', 'quote_item_id'
        ]);
        $jc_data_items = Arr::only($input, [
            'type', 'reference', 'date', 'technician', 'equipment', 'location', 'fault', 'equipment_id'
        ]);
        $data_items = modify_array($data_items);
        $jc_data_items = modify_array($jc_data_items);
        
        DB::beginTransaction();

        // verification
        $verification = Verification::create($data);

        // verification items
        $data_items = array_map(function($v) use($verification) {
            return array_replace($v, ['parent_id' => $verification->id,]);            
        }, $data_items);
        VerificationItem::insert($data_items);

        // verification jobcards/dnotes
        $jc_data_items = array_filter($jc_data_items, fn($v) => $v['reference']);
        $jc_data_items = array_map(function($v) use($verification) {
            return array_replace($v, ['parent_id' => $verification->id,]);            
        }, $jc_data_items);
        VerificationJc::insert($jc_data_items);

        // update quote
        $quote = $verification->quote;
        if ($quote) {
            $limit = false;
            if ($quote->taxable > 0) {
                if (round($quote->taxable) < round($quote->verified_taxable+$verification->taxable))
                    $limit = true;
            } elseif ($quote->amount > 0) {
                if (round($quote->amount) < round($quote->verified_amount+$verification->subtotal))
                    $limit = true;
            }
            if ($limit) throw ValidationException::withMessages(['Cannot verify more than the quote amount']);

            $quote->update([
                'verified' => 'Yes', 
                'verification_date' => date('Y-m-d'),
                'verified_by' => auth()->user()->id,
                'gen_remark' => $verification->note,
            ]);
            $quote->increment('verified_amount', ($verification->subtotal));
            $quote->increment('verified_tax', ($verification->tax));
            $quote->increment('verified_total', ($verification->total));
            $quote->increment('verified_taxable', ($verification->taxable));
        }

        if ($verification) {
            DB::commit();
            return $verification;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Verification $verification
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(Verification $verification, array $input)
    {
        // dd($input);
        foreach ($input as $key => $value) {
            if (in_array($key, ['verification_date'])) $input[$key] = date_for_database($value);
            if (in_array($key, ['taxable', 'subtotal', 'tax', 'total']))
                $input[$key] = numberClean($value);
            if (in_array($key, ['product_subtotal', 'product_tax', 'product_total'])) {
                if (is_array($value)) $input[$key] = array_map(fn($v) => floatval(str_replace(',', '', $v)), $value);                 ;
            }
            if (in_array($key, ['date'])) {
                if (is_array($value)) $input[$key] = array_map(fn($v) => date_for_database($v), $value);
            }
        }

        $data = Arr::only($input, ['quote_id', 'customer_id', 'branch_id', 'note', 'taxable', 'subtotal', 'tax', 'total']);
        $data_items = Arr::only($input, [
            'item_id', 'numbering', 'product_name', 'unit', 'tax_rate', 'product_qty', 'product_subtotal', 'product_tax', 'product_total', 'remark', 
            'row_index', 'a_type', 'product_id', 'quote_item_id'
        ]);
        $jc_data_items = Arr::only($input, [
            'jcitem_id', 'type', 'reference', 'date', 'technician', 'equipment', 'location', 'fault', 'equipment_id'
        ]);
        $data_items = modify_array($data_items);
        $jc_data_items = modify_array($jc_data_items);

        DB::beginTransaction();

        // part verification
        $prev_verification = $verification;
        $is_updated = $verification->update($data);

        // part verification items
        $item_ids = array_map(fn($v) => $v['item_id'], $data_items);
        $verification->items()->whereNotIn('id', $item_ids)->delete();
        // update or create verified item
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'id' => $item['item_id'],
                'parent_id' => $verification->id,
            ]);
            $db_item = VerificationItem::firstOrNew(['id' => $item['id']]);
            $db_item->fill($item);
            if (!$db_item->id) unset($db_item->id);
            unset($db_item->item_id);
            $db_item->save();
        }

        // part-verification jobcards/dnotes
        $jc_data_items = array_filter($jc_data_items, fn($v) => $v['jcitem_id']);
        $verification->jc_items()->whereNotIn('id', $jc_data_items)->delete();
        // update or create verified jc_item
        foreach ($jc_data_items as $item) {
            $item = array_replace($item, [
                'id' => $item['jcitem_id'],
                'parent_id' => $verification->id,
            ]);
            $db_item = VerificationJc::firstOrNew(['id' => $item['id']]);
            $db_item->fill($item);
            if (!$db_item->id) unset($db_item->id);
            unset($db_item->jcitem_id);
            $db_item->save();
        }

        // update related quote
        $quote = $verification->quote;
        if ($quote) {
            // reverse
            $quote->decrement('verified_amount', $prev_verification->subtotal);
            $quote->decrement('verified_tax', $prev_verification->tax);
            $quote->decrement('verified_total', $prev_verification->total);
            $quote->decrement('verified_taxable', $prev_verification->taxable);
            
            // update
            $limit = false;
            if ($quote->taxable > 0) {
                if (round($quote->taxable) < round($quote->verified_taxable+$verification->taxable))
                    $limit = true;
            } elseif ($quote->amount > 0) {
                if (round($quote->amount) < round($quote->verified_amount+$verification->subtotal))
                    $limit = true;
            }
            if ($limit) throw ValidationException::withMessages(['Cannot verify more than the quote amount']);
            $quote->update([
                'verified' => 'Yes', 
                'verification_date' => date('Y-m-d'),
                'verified_by' => auth()->user()->id,
                'gen_remark' => $verification->note,
            ]);
            $quote->increment('verified_amount', numberClean($verification->subtotal));
            $quote->increment('verified_tax', numberClean($verification->tax));
            $quote->increment('verified_total', numberClean($verification->total));
            $quote->increment('verified_taxable', numberClean($verification->taxable));
        }

        if ($is_updated) {
            DB::commit();
            return $verification;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Verification $verification
     * @throws GeneralException
     * @return bool
     */
    public function delete(Verification $verification)
    {   
        DB::beginTransaction();

        $is_exist = Verification::where('quote_id', $verification->quote_id)->exists();
        if ($is_exist) {
            $verifications = Verification::where('quote_id', $verification->quote_id)->latest();
            if (@$verifications->first()->id == $verification->id) {
                $verification = @$verifications[0];
                if ($verification->quote) {
                    $verification->quote->update([
                        'verified' => 'Yes', 
                        'verification_date' => date('Y-m-d'),
                        'verified_by' => auth()->user()->id,
                        'gen_remark' => $verification->note,
                        'verified_amount' => numberClean($verification->subtotal),
                        'verified_tax' => numberClean($verification->tax), 
                        'verified_total' => numberClean($verification->total),
                        'verified_taxable' => numberClean($verification->taxable),
                    ]);
                }
            }
        } else {
            if ($verification->quote) {
                $verification->quote->update([
                    'verified' => 'No', 
                    'verification_date' => '',
                    'verified_by' => '',
                    'gen_remark' => '',
                    'verified_amount' => 0,
                    'verified_tax' => 0,
                    'verified_total' => 0,
                    'verified_taxable' => 0,
                ]);
            }
        }

        if ($verification->delete()) {
            DB::commit();
            return true;
        }
    }
}
