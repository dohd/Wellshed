<?php

namespace App\Repositories\Focus\customer_enrollment;

use DB;
use Carbon\Carbon;
use App\Exceptions\GeneralException;
use App\Models\customer_enrollment\CustomerEnrollment;
use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\ReferralsPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomerEnrollmentRepository.
 */
class CustomerEnrollmentRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = CustomerEnrollment::class;

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
        DB::beginTransaction();
        $input['product_categories'] = implode(',', $input['product_categories'] ?? []);
        $input['products'] = implode(',', $input['products'] ?? []);
        foreach ($input as $key => $val) {
            if(in_array($key, ['dob']))
                $input[$key] = date_for_database($val);
        }
        $reservation = ThirdPartiesPromoCodeReservation::where('redeemable_code', $input['redeemable_code'])->first() ??
                CustomersPromoCodeReservation::where('redeemable_code', $input['redeemable_code'])->first() ??
                ReferralsPromoCodeReservation::where('redeemable_code', $input['redeemable_code'])->first();
        $input['reserve_uuid'] = $reservation->uuid;
        $response = CustomerEnrollment::create($input);
        if ($response) {
            DB::commit();
            $reservation->status = 'used';
            $reservation->update();
            return $response;
        }
        throw new GeneralException(trans('exceptions.backend.CustomerEnrollments.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param CustomerEnrollment $customer_enrollment
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($customer_enrollment, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($customer_enrollment->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.CustomerEnrollments.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param CustomerEnrollment $customer_enrollment
     * @throws GeneralException
     * @return bool
     */
    public function delete($customer_enrollment)
    {
        if ($customer_enrollment->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.CustomerEnrollments.delete_error'));
    }
}
