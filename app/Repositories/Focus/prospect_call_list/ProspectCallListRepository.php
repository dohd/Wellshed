<?php

namespace App\Repositories\Focus\prospect_call_list;

use App\Exceptions\GeneralException;

use App\Models\prospect_calllist\ProspectCallList;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use DB;



/**
 * Class ProductcategoryRepository.
 */
class ProspectCallListRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = ProspectCallList::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
       
        $q = $this->query()->when(request('id'), function ($q) {
            $q->where('call_id', request('id'));
        })
        ->whereHas('prospect_status')
        ->whereHas('call_list', fn($q) => $q->where('employee_id', auth()->user()->id))
        ->whereDate('call_date','=', Carbon::today()->toDateString());
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
        
        $result = ProspectCallList::create($data);
        
        return $result;

        throw new GeneralException('Error Creating Prospect');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\ProspectCallList $prospectcalllistcalllist
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($prospectcalllist, array $input)
    {
       
        DB::beginTransaction();
        $result = $prospectcalllist->update($input['data']);
        
        if ($result) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\prospectcalllist\Prospect $prospectcalllist
     * @throws GeneralException
     * @return bool
     */
    public function delete($prospectcalllist)
    {   
        if ($prospectcalllist->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}