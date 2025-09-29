<?php

namespace App\Repositories\Focus\tenant_ticket;

use App\Models\tenant_ticket\TenantTicket;
use App\Repositories\BaseRepository;

class TenantTicketRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = TenantTicket::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        $q = $this->query()->whereNull('deleted_at');

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
        $ticket = TenantTicket::create($input);
        return $ticket;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param TenantTicket $tenant_ticket
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(TenantTicket $tenant_ticket, array $input)
    {   
        return $tenant_ticket->update($input);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param TenantTicket $tenant_ticket
     * @throws GeneralException
     * @return bool
     */
    public function delete(TenantTicket $tenant_ticket)
    {
        // return $tenant_ticket->delete();
        return $tenant_ticket->update(['deleted_at' => now()]);
    }
}
