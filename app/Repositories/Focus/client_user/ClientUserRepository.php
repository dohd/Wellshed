<?php

namespace App\Repositories\Focus\client_user;

use App\Http\Controllers\ClientSupplierAuth;
use App\Models\client_user\ClientUser;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Support\Arr;

class ClientUserRepository extends BaseRepository
{
    use ClientSupplierAuth;

    /**
     * Associated Repository Model.
     */
    const MODEL = ClientUser::class;

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
        DB::beginTransaction();
        $client_user_data = Arr::only($input, ['customer_id', 'branch_id', 'location']);
        $user_data = Arr::except($input, array_flip($client_user_data));

        $client_user = ClientUser::create($client_user_data);
        // authorize
        $this->createAuth($client_user, $user_data, 'client_user');

        if ($client_user) {
            DB::commit();
            return $client_user;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param ClientUser $client_user
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(ClientUser $client_user, array $input)
    {   
        DB::beginTransaction();
        $client_user_data = Arr::only($input, ['customer_id', 'branch_id', 'location']);
        $user_data = Arr::except($input, array_flip($client_user_data));

        $result = $client_user->update($client_user_data);
        // authorize
        $this->updateAuth($client_user, $user_data, 'client_user');

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param ClientUser $client_user
     * @throws GeneralException
     * @return bool
     */
    public function delete(ClientUser $client_user)
    {
        // DB::beginTransaction();
        // $this->deleteAuth($client_user, 'client_user');
        // $result = $client_user->delete();
        // if ($result) {
        //     DB::commit();
        //     return true;
        // }
        return $client_user->update(['deleted_at' => now()]);
    }
}
