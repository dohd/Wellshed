<?php

namespace App\Repositories\Focus\health_and_safety_objectives;

use DB;
use Carbon\Carbon;
use App\Models\term\Term;
use App\Exceptions\GeneralException;
use App\Models\health_and_safety_objectives\HealthAndSafetyObjective;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TermRepository.
 */
class HealthAndSafetyObjectivesRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = HealthAndSafetyObjective::class;

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
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {
        if (HealthAndSafetyObjective::create($input)) {
            return true;
        }
        throw new GeneralException('Error creating health and safety objective');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Term $term
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($healthAndSafetyObjective, array $input)
    {
         
        if ($healthAndSafetyObjective->update($input))
            return true;

        throw new GeneralException('Error updating health and safety objective');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Term $term
     * @return bool
     * @throws GeneralException
     */
//     public function delete(Term $term)
//     {
//         $flag = true;
//         if (!$term->type) {
//             $available = Term::whereType(0)->get(['id'])->count('*');
//             if ($available < 2) {
//                 $flag = false;
//             }
//         }

//         if ($flag) {
//             if ($term->delete()) {
//                 return true;
//             }
//         }
//         return false;

//     }
}
