<?php

namespace App\Repositories\Focus\prefix;

use App\Models\prefix\Prefix;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

/**
 * Class PrefixRepository.
 */
class PrefixRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Prefix::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        return $this->query()->get(['id','value','note']);
    }


    /**
     * For updating the respective Model in storage
     *
     * @param Prefix $prefix
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Prefix $prefix, array $input)
    {   
        $input = array_map( 'strip_tags', $input);
        $input['class'] = $prefix->class;
        $input['note'] = $prefix->note;
    	if (auth()->user()->ins == 1) $result = $prefix->update($input);
        else $result = Prefix::create($input);

        return $result;
    }
}
