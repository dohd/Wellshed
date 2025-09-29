<?php

namespace App\Repositories\Focus\boq;

use DB;
use Carbon\Carbon;
use App\Models\boq\BoQ;
use App\Exceptions\GeneralException;
use App\Models\bom\BoM;
use App\Models\bom\BoMItem;
use App\Models\boq\BoQItem;
use App\Repositories\BaseRepository;

/**
 * Class BoQRepository.
 */
class BoQRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = BoQ::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        $q->when(request('project_id'), function($q) {
            $q->whereHas('lead.quotes', function($q){
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
     * @return bool
     */
    public function create(array $input)
    {
        $data = $input['data'];
        DB::beginTransaction();
        $result = BoQ::create($data);

        $data_items = $input['data_items'];
        // dd($data_items);
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'boq_id' => $result->id, 
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'rate' =>  floatval(str_replace(',', '', $v['rate'])),
                'qty' => floatval(str_replace(',', '', $v['qty'])),
                'new_qty' => floatval(str_replace(',', '', $v['new_qty'])),
                'amount' => floatval(str_replace(',', '', $v['amount'])),
            ]);
        }, $data_items);
        BoQItem::insert($data_items);
        if ($result) {
            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.boqs.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param boq $boq
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($boq, array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];   
        foreach ($data as $key => $val) {
            if (in_array($key, ['taxable','total_boq_amount','total_boq_vat', 'total', 'subtotal', 'tax', 'boq_tax','boq_total','boq_subtotal','boq_taxable'])) 
                $data[$key] = numberClean($val);
        }  
        // dd($data);
        
        $result = $boq->update($data);

        $data_items = $input['data_items'];
        $boq_sheet_id = $input['boq_sheet_id'];

        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $boq->items()->where('boq_sheet_id', $boq_sheet_id)->whereNotIn('id', $item_ids)->delete();
        
        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['boq_rate','rate', 'qty', 'new_qty', 'amount','product_subtotal','tax_rate']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $boq_item = BoQItem::firstOrNew(['id' => $item['id']]);
            $boq_item->fill(array_replace($item, ['boq_id' => $boq['id'], 'ins' => $boq['ins']]));
            if (!$boq_item->id) unset($boq_item->id);
            $boq_item->save();
        }

    	if ($result){
            DB::commit();
            return;
        }
            

        throw new GeneralException(trans('exceptions.backend.boqs.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param boq $boq
     * @throws GeneralException
     * @return bool
     */
    public function delete($boq)
    {
        if ($boq->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.boqs.delete_error'));
    }

    public function generate_bom($input)
    {
        DB::beginTransaction();
        try {
            $data = [
                'name' => $input['boq']['name'],
                'boq_id' => $input['boq']['id'],
                'lead_id' => $input['boq']['lead_id'],
                'subtotal' => $input['boq']['subtotal'],
                'tax' => $input['boq']['tax'],
                'taxable' => $input['boq']['taxable'],
                'total' => $input['boq']['total'],
            ];
    
            $result = BoM::create($data);
    
            if (!$result) {
                throw new GeneralException("Error creating BoM");
            }
    
            // Prepare bulk insert array
            $bomItems = [];
            foreach ($input['boq']['items'] as $item) {
                if ($item->product_id > 0 || $item->is_imported == 0 || $item->type == 'title') {
                    $bomItems[] = [
                        'bom_id' => $result->id,
                        'boq_item_id' => $item->id,
                        'boq_sheet_id' => $item->boq_sheet_id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name ?: $item->description,
                        'qty' => $item->qty,
                        'unit_id' => $item->unit_id,
                        'tax_rate' => $item->tax_rate,
                        'rate' => $item->rate,
                        'amount' => $item->amount,
                        'type' => $item->type,
                        'row_index' => $item->row_index,
                        'misc' => $item->misc,
                        'numbering' => $item->numbering,
                        'ins' => auth()->user()->ins,
                        'user_id' => auth()->user()->id,
                        'created_at' => now(), // Add timestamps manually
                        'updated_at' => now(),
                    ];
                }
            }
    
            // Perform batch insert if there are items
            if (!empty($bomItems)) {
                $chunks = array_chunk($bomItems, 1000); // Insert in batches of 1000
                // dd($chunks);
                foreach ($chunks as $chunk) {
                    BoMItem::insert($chunk);
                }
            }
    
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new GeneralException("Error: " . $e->getMessage());
        }
    }
    
}
