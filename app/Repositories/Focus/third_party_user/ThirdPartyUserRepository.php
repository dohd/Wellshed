<?php

namespace App\Repositories\Focus\third_party_user;

use DB;
use Carbon\Carbon;
use App\Exceptions\GeneralException;
use App\Models\third_party_user\ThirdPartyUser;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ThirdPartyUserRepository.
 */
class ThirdPartyUserRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = ThirdPartyUser::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get();
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
        if (ThirdPartyUser::create($input)) {
            return true;
        }
        throw new GeneralException('Error Creating Third Party User');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Department $third_party_user
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($third_party_user, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($third_party_user->update($input))
            return true;

        throw new GeneralException('Error Updating Third Party User');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Department $third_party_user
     * @throws GeneralException
     * @return bool
     */
    public function delete($third_party_user)
    {
        if ($third_party_user->delete()) {
            return true;
        }

        throw new GeneralException('Error Deleting Third Party User!!');
    }
}
