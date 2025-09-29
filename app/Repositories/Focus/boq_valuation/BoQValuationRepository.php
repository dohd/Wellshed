<?php

namespace App\Repositories\Focus\boq_valuation;

use DB;
use App\Exceptions\GeneralException;
use App\Models\boq_valuation\BoQValuation;
use App\Models\boq_valuation\BoQValuationExp;
use App\Models\boq_valuation\BoQValuationItem;
use App\Models\boq_valuation\BoQValuationJC;
use App\Models\boq_valuation\BoQValuationDoc;
use App\Models\misc\Misc;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Storage;

/**
 * Class BoQValuationRepository.
 */
class BoQValuationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = BoQValuation::class;

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
            $q->whereHas('project', fn($q) => $q->where('project_id', request('project_id')));
        });
        return $q->orderBy('id','desc')->get();
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
        foreach ($input as $key => $val) {
            if ($key == 'valuation_date') $input['valuation_date'] = date_for_database($val);
            if ($key == 'completion_date') $input['completion_date'] = date_for_database($val);
            if (in_array($key, ['date'])) $input[$key] = array_map(fn($v) =>  date_for_database($v), $val);
            $itemskeys = [
                'product_qty', 'tax_rate', 'product_tax', 'product_price', 'product_subtotal', 'product_amount', 
                'exp_amount', 'perc_valuated', 'total_valuated', 'exp_perc_valuated', 'exp_total_valuated',
                'exp_valued_bal', 'product_valued_bal', 
            ];
            if (in_array($key, $itemskeys)) $input[$key] = array_map(fn($v) => (float) str_replace(',', '', $v), $val);
            $dataKeys = [
                'taxable', 'subtotal', 'tax', 'total', 'exp_total', 'exp_valuated', 'exp_balance', 'exp_valuated_perc', 
                'balance', 'valued_taxable', 'valued_subtotal', 'valued_tax', 'valued_total', 'valued_perc',
                'perc_retention', 'retention',
            ];
            if (in_array($key, $dataKeys)) $input[$key] = numberClean($val);
        }

        $valuateData = Arr::only($input, [
            'boq_id', 'customer_id', 'branch_id', 'note', 'tax_id', 'taxable', 'subtotal', 'tax', 'total', 
            'exp_total', 'exp_valuated', 'exp_balance', 'exp_valuated_perc',
            'balance', 'valued_taxable', 'valued_subtotal', 'valued_tax', 'valued_total', 'valued_perc',
            'is_final', 'completion_date', 'dlp_period','dlp_reminder', 'perc_retention', 'retention',
            'retention_note','project_closure_date','project_id','quote_id'
        ]);
        $valuateDataItems = Arr::only($input, [
            'boq_item_id', 'numbering', 'row_type', 'row_index', 'product_name', 'unit', 
            'productvar_id', 'product_qty', 'tax_rate', 'product_tax', 'product_price', 'product_subtotal', 'product_amount',  
            'product_valued_bal', 'perc_valuated', 'total_valuated', 
        ]);
        $valuateJcs = Arr::only($input, ['type', 'reference', 'date', 'technician', 'equipment', 'location', 'fault', 'equipment_id']);
        $valuateExps = Arr::only($input, [
            'exp_perc_valuated', 'exp_total_valuated', 'exp_amount', 'exp_valued_bal',  
            'exp_origin_id', 'exp_category', 'exp_uom', 'exp_product_name', 'exp_productvar_id', 
            'exp_budget_item_id', 'exp_expitem_id', 'exp_budget_line_id', 'exp_casual_remun_id',
            'exp_boq_id', 'exp_project_id', 
        ]);
        $document_data = Arr::only($input, ['caption','document_name']);
        $keys = array_map(fn($v) => preg_replace('/exp_/', '', $v), array_keys($valuateExps));
        $valuateExps = array_combine($keys, array_values($valuateExps));

        $employee_ids = @$input['employee_ids']? implode(',', $input['employee_ids']) : null;
        $input['employee_ids'] = $employee_ids;

        // dd(compact('valuateData', 'valuateDataItems', 'valuateJcs', 'valuateExps'), $input);
        
        DB::beginTransaction();

        // create job valuation
        $valuateData['date'] = $input['valuation_date'];
        $valuateData['employee_ids'] = $input['employee_ids'];
        $boqValuation = BoQValuation::create($valuateData);

        // check if final valuation
        if (
            $boqValuation->is_final &&
            $boqValuation->boq &&
            $boqValuation->boq->lead &&
            $boqValuation->boq->lead->quotes->isNotEmpty()
        ) {
            $status = Misc::where('section', 2)->where('name', 'Completed')->first();

            if ($status) {
                foreach ($boqValuation->boq->lead->quotes as $quote) {
                    if ($quote->project) {
                        $quote->project->update(['status' => $status->id]);
                    }
                }
            }
        }

        // insert valuation items
        $valuateDataItems['boq_valuation_id'] = array_fill(0, count($valuateDataItems['perc_valuated']), $boqValuation->id);
        $valuateDataItems['ins'] = array_fill(0, count($valuateDataItems['perc_valuated']), $boqValuation->ins);
        $valuateDataItems = modify_array($valuateDataItems);
        $isTotalsExists = array_filter($valuateDataItems, fn($v) => $v['boq_item_id'] > 0);
        if (!$isTotalsExists) throw ValidationException::withMessages(['Line item totals are required']);
        $valuateDataItems = array_filter($valuateDataItems, fn($v) => $v['boq_item_id'] > 0);
        // dd($valuateDataItems);
        BoQValuationItem::insert($valuateDataItems);

        // insert expense items
        if (@$valuateExps['perc_valuated']) {
            $valuateExps['boq_valuation_id'] = array_fill(0, count($valuateExps['perc_valuated']), $boqValuation->id);
            $valuateExps['ins'] = array_fill(0, count($valuateExps['perc_valuated']), $boqValuation->ins);
            // dd($valuateExps);
            $valuateExps = modify_array($valuateExps);
            $valuateExps = array_filter($valuateExps, fn($v) => $v['perc_valuated']);
            BoQValuationExp::insert($valuateExps);
        }

        // insert jobcards / DNotes
        if (@$valuateJcs['technician']) {
            $valuateJcs['boq_valuation_id'] = array_fill(0, count($valuateJcs['technician']), $boqValuation->id);
            $valuateJcs['ins'] = array_fill(0, count($valuateJcs['technician']), $boqValuation->ins);
            $valuateJcs = modify_array($valuateJcs);
            $valuateJcs = array_filter($valuateJcs, fn($v) => $v['technician']);
            BoQValuationJC::insert($valuateJcs);            
        }

        //insert documents
        $document_data = modify_array($document_data);
        $document_data = array_map(function ($v) use($boqValuation) {
            $v1 = [
                'boq_valuation_id' => $boqValuation->id,
                'caption' => $v['caption'],
                'ins' => $boqValuation->ins
            ];
            // fail safe for upload
            try {
                $v1['document_name'] = $this->uploadFile($v['document_name']);
            } catch (\Exception $e) {
                // 
            }
            return $v1;
        }, $document_data);
        $document_data = array_filter($document_data, fn($v) => $v['caption']);
        BoQValuationDoc::insert($document_data);

        DB::commit();
        return $boqValuation;
    }

    public function uploadFile($file)
    {
        $fileName = time() . '-' . $file->getClientOriginalName();
        $filePath = 'files' . DIRECTORY_SEPARATOR . 'valuation_cert' . DIRECTORY_SEPARATOR;
        Storage::disk('public')->put($filePath . $fileName, file_get_contents($file->getRealPath()));
        
        return $fileName;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Department $department
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($boq_valuation, array $input)
    {
        

        throw new GeneralException('Error Updating BoQ Valuation');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Department $department
     * @throws GeneralException
     * @return bool
     */
    public function delete($boq_valuation)
    {
        $invoice = $boq_valuation->invoice;
        if ($invoice) throw ValidationException::withMessages(['Valuation is attached to invoice no.: ' . (string) $invoice->tid]);

        $isNextExists = BoQValuation::where('id', '>', $boq_valuation->id)
            ->where('boq_id', $boq_valuation->boq_id)
            ->exists();
        if ($isNextExists) throw ValidationException::withMessages(['Not allowed! Later valuations exists']);

        DB::beginTransaction();
        $boq_valuation->docs()->delete();    
        $boq_valuation->job_cards()->delete();    
        $boq_valuation->items()->delete();
        $boq_valuation->valuatedExps()->delete();
        
        if ($boq_valuation->delete()) {
            DB::commit();
            return true;
        }
    }
}
