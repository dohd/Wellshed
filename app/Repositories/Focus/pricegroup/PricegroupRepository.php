<?php

namespace App\Repositories\Focus\pricegroup;

use DB;
use Carbon\Carbon;
use App\Models\pricegroup\Pricegroup;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WarehouseRepository.
 */
class PricegroupRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Pricegroup::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
       //  ->join('blogs', 'customers.id', '=', 'blogs.id');
        return $this->query()
            ->get();
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
        $input = array_map( 'strip_tags', $input);
        if (!isset($input['is_client'])) $input['is_client'] = 0;
        $is_exist = Pricegroup::where(['ref_id' => $input['ref_id'], 'is_client' => $input['is_client']])->count();
        if ($is_exist) return false;

        if (Pricegroup::create($input)) return true;
            
        throw new GeneralException(trans('exceptions.backend.pricegroups.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Warehouse $warehouse
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Pricegroup $pricegroup, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($pricegroup->update($input)) return true;
            
        throw new GeneralException(trans('exceptions.backend.pricegroups.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Warehouse $warehouse
     * @throws GeneralException
     * @return bool
     */
    public function delete(Pricegroup $pricegroup)
    {
        if ($pricegroup->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.pricegroups.delete_error'));
    }
}
