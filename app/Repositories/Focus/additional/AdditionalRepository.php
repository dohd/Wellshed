<?php

namespace App\Repositories\Focus\additional;

use App\Models\additional\Additional;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

/**
 * Class AdditionalRepository.
 */
class AdditionalRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Additional::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        return $this->query()->get();
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
        $input['name'] = strip_tags($input['name']);
        $result = Additional::create($input);
        return $result;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Additional $additional
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Additional $additional, array $input)
    {
        $input['name'] = strip_tags($input['name']);
        if ($additional->update($input)) return true;
            
        throw new GeneralException(trans('exceptions.backend.additionals.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Additional $additional
     * @throws GeneralException
     * @return bool
     */
    public function delete(Additional $additional)
    {
        if ($additional->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.additionals.delete_error'));
    }
}