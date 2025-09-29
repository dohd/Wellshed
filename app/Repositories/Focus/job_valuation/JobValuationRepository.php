<?php

namespace App\Repositories\Focus\job_valuation;

use DB;
use App\Exceptions\GeneralException;
use App\Models\job_valuation\JobValuation;
use App\Models\job_valuation\JobValuationDoc;
use App\Models\job_valuation\JobValuationExp;
use App\Models\job_valuation\JobValuationItem;
use App\Models\job_valuation\JobValuationJC;
use App\Models\misc\Misc;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class JobValuationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = JobValuation::class;
    
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

        return $q->get();
    }

    // upload file to storage
    public function uploadFile($file)
    {
        $fileName = time() . '-' . $file->getClientOriginalName();
        $filePath = 'files' . DIRECTORY_SEPARATOR . 'valuation_cert' . DIRECTORY_SEPARATOR;
        Storage::disk('public')->put($filePath . $fileName, file_get_contents($file->getRealPath()));
        
        return $fileName;
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return JobValuation $job_valuation
     */
    public function create(array $input)
    {   
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'valuation_date') $input['valuation_date'] = date_for_database($val);
            if ($key == 'completion_date') $input['completion_date'] = date_for_database($val);
            if (in_array($key, ['date'])) $input[$key] = array_map(fn($v) =>  date_for_database($v), $val);
            $arrkeys = [
                'product_qty', 'tax_rate', 'product_tax', 'product_price', 'product_subtotal', 'product_amount', 
                'exp_amount', 'perc_valuated', 'total_valuated', 'exp_perc_valuated', 'exp_total_valuated',
                'exp_valued_bal', 'product_valued_bal', 
            ];
            if (in_array($key, $arrkeys)) $input[$key] = array_map(fn($v) => (float) str_replace(',', '', $v), $val);
            $numKeys = [
                'taxable', 'subtotal', 'tax', 'total', 'exp_total', 'exp_valuated', 'exp_balance', 'exp_valuated_perc', 
                'balance', 'valued_taxable', 'valued_subtotal', 'valued_tax', 'valued_total', 'valued_perc',
                'perc_retention', 'retention',
            ];
            if (in_array($key, $numKeys)) $input[$key] = numberClean($val);
        }

        $valuateData = Arr::only($input, [
            'quote_id', 'customer_id', 'branch_id', 'note', 'tax_id', 'taxable', 'subtotal', 'tax', 'total', 
            'exp_total', 'exp_valuated', 'exp_balance', 'exp_valuated_perc',
            'balance', 'valued_taxable', 'valued_subtotal', 'valued_tax', 'valued_total', 'valued_perc',
            'is_final', 'completion_date', 'dlp_period','dlp_reminder', 'perc_retention', 'retention',
            'retention_note',
        ]);
        $valuateDataItems = Arr::only($input, [
            'quote_item_id', 'numbering', 'row_type', 'row_index', 'product_name', 'unit', 
            'productvar_id', 'product_qty', 'tax_rate', 'product_tax', 'product_price', 'product_subtotal', 'product_amount',  
            'product_valued_bal', 'perc_valuated', 'total_valuated', 
        ]);
        $valuateJcs = Arr::only($input, ['type', 'reference', 'date', 'technician', 'equipment', 'location', 'fault', 'equipment_id']);
        $valuateExps = Arr::only($input, [
            'exp_perc_valuated', 'exp_total_valuated', 'exp_amount', 'exp_valued_bal',  
            'exp_origin_id',  'exp_category', 'exp_uom', 'exp_product_name', 'exp_productvar_id', 'exp_quote_id', 'exp_project_id', 
            'exp_budget_item_id', 'exp_expitem_id', 'exp_budget_line_id', 'exp_casual_remun_id',
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
        $jobValuation = JobValuation::create($valuateData);

        // check if final valuation
        if ($jobValuation->is_final && @$jobValuation->quote->project) {
            $status = Misc::where('section', 2)->where('name','Completed')->first();
            if ($status) $jobValuation->quote->project->update(['status' => $status->id]);
        }

        // insert valuation items
        $valuateDataItems['job_valuation_id'] = array_fill(0, count($valuateDataItems['perc_valuated']), $jobValuation->id);
        $valuateDataItems['ins'] = array_fill(0, count($valuateDataItems['perc_valuated']), $jobValuation->ins);
        $valuateDataItems = modify_array($valuateDataItems);
        $isTotalsExists = array_filter($valuateDataItems, fn($v) => $v['perc_valuated'] > 0 && $v['total_valuated'] > 0);
        if (!$isTotalsExists) throw ValidationException::withMessages(['Line item totals are required']);
        JobValuationItem::insert($valuateDataItems);

        // insert expense items
        if (@$valuateExps['perc_valuated']) {
            $valuateExps['job_valuation_id'] = array_fill(0, count($valuateExps['perc_valuated']), $jobValuation->id);
            $valuateExps['ins'] = array_fill(0, count($valuateExps['perc_valuated']), $jobValuation->ins);
            $valuateExps = modify_array($valuateExps);
            $valuateExps = array_filter($valuateExps, fn($v) => $v['perc_valuated']);
            JobValuationExp::insert($valuateExps);
        }

        // insert jobcards / DNotes
        if (@$valuateJcs['technician']) {
            $valuateJcs['job_valuation_id'] = array_fill(0, count($valuateJcs['technician']), $jobValuation->id);
            $valuateJcs['ins'] = array_fill(0, count($valuateJcs['technician']), $jobValuation->ins);
            $valuateJcs = modify_array($valuateJcs);
            $valuateJcs = array_filter($valuateJcs, fn($v) => $v['technician']);
            JobValuationJC::insert($valuateJcs);            
        }

        //insert documents
        $document_data = modify_array($document_data);
        $document_data = array_map(function ($v) use($jobValuation) {
            $v1 = [
                'job_valuation_id' => $jobValuation->id,
                'caption' => $v['caption'],
                'ins' => $jobValuation->ins
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
        JobValuationDoc::insert($document_data);

        DB::commit();
        return $jobValuation;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param JobValuation $job_valuation
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(JobValuation $job_valuation, array $input)
    {   
        // 
    }

    /**
     * For deleting the respective model from storage
     *
     * @param JobValuation $job_valuation
     * @throws GeneralException
     * @return bool
     */
    public function delete(JobValuation $job_valuation)
    { 
        $invoice = $job_valuation->invoice;
        if ($invoice) throw ValidationException::withMessages(['Valuation is attached to invoice no.: ' . (string) $invoice->tid]);

        $isNextExists = JobValuation::where('id', '>', $job_valuation->id)
            ->where('quote_id', $job_valuation->quote_id)
            ->exists();
        if ($isNextExists) throw ValidationException::withMessages(['Not allowed! Later valuations exists']);

        DB::beginTransaction();
        $job_valuation->docs()->delete();    
        $job_valuation->job_cards()->delete();    
        $job_valuation->items()->delete();
        $job_valuation->valuatedExps()->delete();
        
        if ($job_valuation->delete()) {
            DB::commit();
            return true;
        }
    }
}
