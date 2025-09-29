<?php

namespace App\Repositories\Focus\prospectcallresolved;


use App\Exceptions\GeneralException;
use App\Models\prospect\Prospect;
use App\Models\prospect_calllist\ProspectCallList;
use App\Models\prospectcallresolved\ProspectCallResolved;
use App\Models\prospectcallresolved\ProspectCallResolvedItem;
use App\Models\remark\Remark;
use App\Repositories\BaseRepository;
use DB;
use Carbon\Carbon;

/**
 * Class ProductcategoryRepository.
 */
class ProspectCallResolvedRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = ProspectCallResolved::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
       $start_date = Carbon::parse(request('start_date'))->startOfDay()->toDateTimeString();
       $end_date = Carbon::parse(request('end_date'))->endOfDay()->toDateTimeString();
        $q = $this->query();
        $q->when(request('start_date') && request('end_date'), function ($q) use ($start_date, $end_date) {
            $q->whereBetween('updated_at', [$start_date, $end_date]);
        });
        $q->when(request('user_id'), function ($q){
            $q->whereHas('prospect', function ($q) {
                $q->whereHas('latestRemark', function ($q) {
                    $q->where('user_id', request('user_id'));
                });
            });
        });
        $q->when(request('temperate'), function ($q){
            $q->whereHas('prospect', function ($q) {
                $q->where('temperate', request('temperate'));
            });
        });
        $q->when(request('status'), function ($q){
            $q->whereHas('prospect', function ($q) {
                $q->where('status', request('status'));
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

        //determine if prospect is hot,warm or cold
        DB::beginTransaction();
        $data = $input['data'];
        $temperate = $data['temperate'];
        unset($data['temperate']);
        $result = ProspectCallResolved::create($data);

        //line items
        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result){
            return array_replace($v,[
                'prospect_id' => $result['prospect_id'],
                'prospect_call_resolved_id' => $result['id'],
                'prospect_question_id' => $result['prospect_question_id'],
                'ins' => $result['ins'],
                'user_id' => $result['user_id'],
            ]);
        }, $data_items);
        // dd($temperate);
        ProspectCallResolvedItem::insert($data_items);
        $remarks = $input['remarks'];
        $remark = Remark::create($remarks);
        if($result){
            $id = $data['prospect_id'];
            $prospect = Prospect::find($id);
            $prospect->update([
                'call_status' => 'called',
                'is_called' => 1,
                'temperate' => $temperate,
            ]);
           DB::commit();

           return $result;
        }

        throw new GeneralException('Error Creating Prospect');
    }
    public function notpickedcreate(array $data,array $remarks)
    {
        
        $result = ProspectCallResolved::updateOrCreate(
            ['prospect_id'=>$data['prospect_id']],
            [
                'reminder_date'=>$data['reminder_date'],
                'any_remarks'=>$data['any_remarks'],
            ]
        );
        if($result){
            Remark::create($remarks);
            $id = $data['prospect_id'];
            $prospect = Prospect::find($id);
            if($prospect){
                $prospect->update([
                    'call_status' => 'callednotpicked',
                    'temperate' => 'warm',
                    'is_called' => 1,
                ]);
                
            }
        }
        return $result;

        throw new GeneralException('Error Creating Prospect');
    }
    public function notavailablecreate(array $data,array $remarks)
    {
        
        $result = ProspectCallResolved::updateOrCreate(
            ['prospect_id'=>$data['prospect_id']],
            [
                'any_remarks'=>$data['any_remarks'],
            ]
        );
        if($result){
            Remark::create($remarks);
            $id = $data['prospect_id'];
            $prospect = Prospect::find($id);
            if($prospect){
                $prospect->update([
                    'call_status' => 'callednotavailable',
                    'is_called' => 1,
                    'temperate' => 'cold',
                    'status'=>'lost',
                    'reason'=>$data['any_remarks']
                ]);
                
            }
        }
        return $result;

        throw new GeneralException('Error Creating Prospect');
    }
    public function pickedbusycreate(array $data,array $remarks)
    {
        
        $result = ProspectCallResolved::updateOrCreate(
            ['prospect_id'=>$data['prospect_id']],
            [
                'reminder_date'=>$data['reminder_date'],
                'any_remarks'=>$data['any_remarks'],
            ]
        );
        if($result){
            Remark::create($remarks);
            $id = $data['prospect_id'];
            $prospect = Prospect::find($id);
            if($prospect){
                $prospect->update([
                    'call_status' => 'calledrescheduled',
                    'is_called' => 1,
                    'temperate' => 'warm',
                ]);
                
               

            }
        }
        return $result;

        throw new GeneralException('Error Creating Prospect');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\Prospect $prospect
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($prospectcallresolved, array $input)
    {
       
        DB::beginTransaction();
        $result = $prospectcallresolved->update($input['data']);
        
        if ($result) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\prospect\Prospect $prospect
     * @throws GeneralException
     * @return bool
     */
    public function delete(ProspectCallResolved $prospectcallresolved)
    {   
        if ($prospectcallresolved->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}