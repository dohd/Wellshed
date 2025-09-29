<?php

namespace App\Repositories\Focus\client_vendor_tag;

use App\Models\client_vendor_tag\ClientVendorTag;
use App\Repositories\BaseRepository;

class ClientVendorTagRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = ClientVendorTag::class;

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
        $ticket = ClientVendorTag::create($input);
        return $ticket;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param ClientVendorTag $client_vendor_tag
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(ClientVendorTag $client_vendor_tag, array $input)
    {   
        return $client_vendor_tag->update($input);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param ClientVendorTag $client_vendor_tag
     * @throws GeneralException
     * @return bool
     */
    public function delete(ClientVendorTag $client_vendor_tag)
    {
        // return $client_vendor_tag->delete();
        return $client_vendor_tag->update(['deleted_at' => now()]);
    }
}
