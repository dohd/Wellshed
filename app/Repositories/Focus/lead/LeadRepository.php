<?php

namespace App\Repositories\Focus\lead;

use App\Models\lead\Lead;
use App\Exceptions\GeneralException;
use App\Models\items\Prefix;
use App\Models\potential\Potential;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductcategoryRepository.
 */
class LeadRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Lead::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('classlist_id'), fn($q) => $q->where('classlist_id', request('classlist_id')));

        $q->when((request('status') === 'CLOSED'), fn($q) => $q->where('status', 1));
        $q->when((request('status') === 'OPEN'), fn($q) => $q->where('status', 0));

        $q->when(request('category'), fn($q) => $q->where('category', request('category')));
        $q->when(request('source'), fn($q) => $q->where('lead_source_id', request('source')));

        $q->when(request('start_date') && request('end_date'), function ($q) {
            $dateRange = array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]);
            $q->whereBetween('date_of_request', $dateRange);
        });

        $q->when(request('tender_status'), function($q) {
            $q->whereHas('tender', fn($q) => $q->where('tender_stages', request('tender_status')));
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
    public function create(array $data)
    {
        $data['date_of_request'] = date_for_database($data['date_of_request']);
        $data['expected_income'] = numberClean($data['expected_income']);
        $tid = Lead::max('reference');
        if ($data['reference'] <= $tid) $data['reference'] = $tid+1;

        $lead = Lead::create($data);
        if($data['client_name']){
            $new_data = [
                'client_name' => $data['client_name'],
                'client_email' => $data['client_email'],
                'client_contact' => $data['client_contact'],
                'client_address' => $data['client_address'],
                'lead_id' => $lead->id
            ];
            Potential::create($new_data);
        }
        return $lead;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\Lead $lead
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Lead $lead, array $data)
    {
        DB::beginTransaction();
        
        $data['date_of_request'] = date_for_database($data['date_of_request']);
        $data['expected_income'] = numberClean($data['expected_income']);
        $result = $lead->update($data);
        
        // update related djcs, quotes, projects
        $lead->djcs()->update(['client_id' => $lead->client_id, 'branch_id' => $lead->branch_id]);
        foreach ($lead->quotes as $quote) {
            $quote->update(['customer_id' => $lead->client_id, 'branch_id' => $lead->branch_id]);
            if ($quote->project) $quote->project->update(['customer_id' => $lead->client_id, 'branch_id' => $lead->branch_id]);
        }

        if($lead->potential) {
            if($data['client_name']){
                $potential = Potential::where('lead_id', $lead->id)->first();
                $new_data = [
                    'client_name' => $data['client_name'],
                    'client_email' => $data['client_email'],
                    'client_contact' => $data['client_contact'],
                    'client_address' => $data['client_address'],
                    'lead_id' => $lead->id
                ];
                if($potential){

                    $potential->update($new_data);
                }
            }else{
                $lead->potential->customer_id = $lead->client_id;
                $lead->potential->update();
            }
        }
        
        if ($result) {
            DB::commit();
            return true;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\lead\Lead $lead
     * @throws GeneralException
     * @return bool
     */
    public function delete(Lead $lead)
    {
        $errorMsg = '';
        $tid = gen4tid("Tkt-", $lead->reference);
        if ($lead->djcs()->exists()) $errorMsg = "Ticket {$tid} is attached to a DJC Report!";
        if ($lead->quotes()->exists()) $errorMsg = "Ticket {$tid} is attached to a Quote / PI!";
        if ($lead->tender) $errorMsg = "Ticket {$tid} is attached to a Tender!";
        if ($errorMsg) throw ValidationException::withMessages([$errorMsg]);

        return $lead->delete();
    }
}