<?php

namespace App\Repositories\Focus\client_product;

use App\Exceptions\GeneralException;
use App\Models\client_product\ClientProduct;
use App\Repositories\BaseRepository;
use DB;

/**
 * Class ProductcategoryRepository.
 */
class ClientProductRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = ClientProduct::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('customer_id') && request('contract'), function ($q) {
            $q->where(['customer_id' => request('customer_id'), 'contract' => request('contract')]);
        })->when(request('customer_id'), function ($q) {
            $q->where(['customer_id' => request('customer_id')]);
        });

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
        // dd($input);
        unset($input['description']);
        $input['rate'] = numberClean($input['rate']);
        $result = ClientProduct::create($input);
        if ($result) return $result;

        throw new GeneralException('Error Creating PriceList');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(ClientProduct $client_product, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $input['rate'] = numberClean($input['rate']);

        if ($client_product->contract != $input['contract']);
            ClientProduct::where('contract', $client_product->contract)->update(['contract' => $input['contract']]);

        if ($client_product->update($input)) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(ClientProduct $client_product)
    {
        if ($client_product->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }

    public function mass_delete($input)
    {
        // dd($input);
        $result = null;
        if (request('customer_id') && request('contract')) {
            $result = ClientProduct::where([
                'customer_id' => request('customer_id'),
                'contract' => request('contract'),
            ])->delete();
        } elseif (request('customer_id')) {
            $result = ClientProduct::where(['customer_id' => request('customer_id')])->delete();
        }

        return $result;
    }
}