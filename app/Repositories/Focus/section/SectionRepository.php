<?php

namespace App\Repositories\Focus\section;

use DB;
use Carbon\Carbon;
use App\Models\section\Section;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MiscRepository.
 */
class SectionRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Section::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
       
        return
            $q->get(['id','name']);
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
        if (Section::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.section.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Misc $misc
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Section $section, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($misc->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.section.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Misc $misc
     * @throws GeneralException
     * @return bool
     */
    public function delete(Section $section)
    {
        if ($misc->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.section.delete_error'));
    }
}
