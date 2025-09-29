<?php

namespace App\Repositories\Focus\ticket_category;

use App\Exceptions\GeneralException;
use App\Models\ticket_category\TicketCategory;
use App\Repositories\BaseRepository;

class TicketCategoryRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = TicketCategory::class;

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
     * @return TicketCategory $ticket_category
     */
    public function create(array $input)
    {
        $category = TicketCategory::create($input);
        return $category;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param TicketCategory $ticket_category
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(TicketCategory $ticket_category, array $input)
    {
        return $ticket_category->update($input);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param TicketCategory $ticket_category
     * @throws GeneralException
     * @return bool
     */
    public function delete(TicketCategory $ticket_category)
    {
        return $ticket_category->delete();
    }
}
