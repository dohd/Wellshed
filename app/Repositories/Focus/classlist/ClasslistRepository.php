<?php

namespace App\Repositories\Focus\classlist;

use App\Exceptions\GeneralException;
use App\Models\classlist\Classlist;
use App\Repositories\BaseRepository;

class ClasslistRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Classlist::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

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
        $classlist = Classlist::create($input);
        return $classlist;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\Classlist $classlist
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Classlist $classlist, array $input)
    {
        $result = $classlist->update($input);
        return $result;
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\lead\Classlist $classlist
     * @throws GeneralException
     * @return bool
     */
    public function delete(Classlist $classlist)
    {
        if ($classlist->invoices()->count()) $error_msg = 'Not Allowed: Class or Subclass is attached to an Invoice';
        if ($classlist->purchase_items()->count()) $error_msg = 'Not Allowed: Class or Subclass is attached to an Expense';
        if ($classlist->purchase_class_budget()->count()) $error_msg = 'Not Allowed: Class or Subclass is attached to an Expense Budget';
        if (isset($error_msg)) throw ValidationException::withMessages([$error_msg]);
    
        return $classlist->delete();
    }
}