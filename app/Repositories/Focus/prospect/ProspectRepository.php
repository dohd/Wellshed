<?php

namespace App\Repositories\Focus\prospect;

use App\Models\prospect\Prospect;
use App\Exceptions\GeneralException;
use App\Models\prospect_calllist\ProspectCallList;
use App\Models\remark\Remark;
use App\Repositories\Focus\remark\RemarkRepository;
use App\Repositories\BaseRepository;
use DB;


/**
 * Class ProductcategoryRepository.
 */
class ProspectRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Prospect::class;


    //Remark repository

    private $remark;

    public function __construct(RemarkRepository $remark)
    {
        $this->remark = $remark;
    }
    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query()->when(request('bytitle'), function ($q) {
            $q->where('title', request('bytitle'));
        })->when(request('bytemperate'), function ($q) {
            $q->where('temperate', request('bytemperate'));
        })->when(request('bycallstatus'), function ($q) {
            $q->where('call_status', request('bycallstatus'));
        })->when(request('bystatus'), function ($q) {
            $q->where('status', request('bystatus'));
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
        
        $result = Prospect::create($data);
        $response = $result->refresh();
        //dd($response['id']);

        $calldata = [
                "prospect_id"=>$response['id'],
                "call_date"=>date("Y-m-d H:i:s")
            ];
        
        ProspectCallList::create($calldata);
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
    public function update($prospect, array $input)
    {
       
        DB::beginTransaction();
        $result = $prospect->update($input['data']);
        
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
    public function delete(Prospect $prospect)
    {   
        if ($prospect->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }

    public function mass_delete($input)
    {
        $result = null;
         if(request('bytitle')) {
            
            $prospectsundertitle = Prospect::where(['title' => request('bytitle')])->get()->toArray();

            $matchingRecords = ProspectCallList::whereIn('prospect_id', $prospectsundertitle)->get();
                dd($prospectsundertitle);
            //$result = Prospect::where(['title' => request('bytitle')])->delete();
            if($result){
                
            }
            
        }

        return $result;
    }
}