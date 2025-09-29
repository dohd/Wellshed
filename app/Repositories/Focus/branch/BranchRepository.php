<?php

namespace App\Repositories\Focus\branch;

use App\Models\branch\Branch;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductcategoryRepository.
 */
class BranchRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Branch::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        $q = $this->query();
        $q->whereNotIn('name', ['Head Office', 'All Branches']);

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
        $input = array_map('strip_tags', $input);
        $c = Branch::create($input);
        if ($c->id) return $c->id;
        throw new GeneralException('Error Creating Branch');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Branch $branch, array $input)
    {
        $input = array_map('strip_tags', $input);
        if ($branch->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Branch $branch)
    {
        if ($branch->contract_equipments()->exists()) throw ValidationException::withMessages(['Branch has attached Equipments']);
        if ($branch->taskschedule_equipments()->exists()) throw ValidationException::withMessages(['Branch has attached TaskSchedule']);
        if ($branch->service_contract_items()->exists()) throw ValidationException::withMessages(['Branch has attached Service Contract Items']);
        if ($branch->contract_services()->exists()) throw ValidationException::withMessages(['Branch has attached Contract Services']);
        if ($branch->equipments()->exists()) throw ValidationException::withMessages(['Branch has attached Equipments']);
        if ($branch->leads()->exists()) throw ValidationException::withMessages(['Branch has attached Leads']);
        return $branch->delete();

        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}
