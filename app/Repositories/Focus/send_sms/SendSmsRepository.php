<?php

namespace App\Repositories\Focus\send_sms;

use DB;
use App\Exceptions\GeneralException;
use App\Models\casual\CasualLabourer;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\prospect\Prospect;
use App\Models\send_sms\SendSms;
use App\Models\supplier\Supplier;
use App\Repositories\BaseRepository;
use App\Repositories\Focus\general\RosesmsRepository;

/**
 * Class SendSmsRepository.
 */
class SendSmsRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = SendSms::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        $q->when(request('status'), function ($q){
            $q->whereHas('sms_response',function ($q){
                if (request('status') === 'sent') {
                    $q->whereHas('sms_callbacks');// Ensure there are callbacks (sent)
                } elseif (request('status') === 'not_sent') {
                    $q->whereDoesntHave('sms_callbacks'); // Ensure no callbacks exist (not sent)
                }
            });
        });

        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('created_at', array_map(fn($v) => date_for_database($v), [request('start_date'), request('end_date')]));
        });

        return $q->take(100)->get();
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
        $phone_numbers = [];
        $user_ids = [];

        $pattern = '/^(0[17]\d{8}|254[17]\d{8})$/';
        if($input['user_type'] == 'employee')
        {
            foreach($input['employee_id'] as $employee){
                $user = Hrm::find($employee);
                if($user->meta){
                    $cleanedNumber = preg_replace('/\D/', '', $user->meta->primary_contact);
                    if (preg_match($pattern, $cleanedNumber)) {

                        if (preg_match('/^01\d{8}$/', $cleanedNumber)) {
                            $phone_numbers[] = '254' . substr($cleanedNumber, 1); // Replace '0' with '254'
                        } else {
                            $phone_numbers[] = $cleanedNumber;
                        }
                        $user_ids[] = $user->id;
                    }
                }
                
            }
        }else if($input['user_type'] == 'customer')
        {
            foreach($input['customer_id'] as $customer_id){
                $customer = Customer::find($customer_id);
                $cleanedNumber = preg_replace('/\D/', '', $customer->phone);
                if (preg_match($pattern, $cleanedNumber)) {
                    if (preg_match('/^01\d{8}$/', $cleanedNumber)) {
                        $phone_numbers[] = '254' . substr($cleanedNumber, 1); // Replace '0' with '254'
                    } else {
                        $phone_numbers[] = $cleanedNumber;
                    }
                    $user_ids[] = $customer->id;
                }
                
            }
        }
        else if($input['user_type'] == 'supplier')
        {
            foreach($input['supplier_id'] as $supplier_id){
                $supplier = Supplier::find($supplier_id);
                $cleanedNumber = preg_replace('/\D/', '', $supplier->phone);
                if (preg_match($pattern, $cleanedNumber)) {
                    if (preg_match('/^01\d{8}$/', $cleanedNumber)) {
                        $phone_numbers[] = '254' . substr($cleanedNumber, 1); // Replace '0' with '254'
                    } else {
                        $phone_numbers[] = $cleanedNumber;
                    }
                    $user_ids[] = $supplier->id;
                }
            }
        }else if($input['user_type'] == 'labourer')
        {
            foreach($input['labourer_id'] as $labourer_id){
                $labourer = CasualLabourer::find($labourer_id);
                $cleanedNumber = preg_replace('/\D/', '', $labourer->phone_number);
                if (preg_match($pattern, $cleanedNumber)) {
                    if (preg_match('/^01\d{8}$/', $cleanedNumber)) {
                        $phone_numbers[] = '254' . substr($cleanedNumber, 1); // Replace '0' with '254'
                    } else {
                        $phone_numbers[] = $cleanedNumber;
                    }
                    $user_ids[] = $labourer->id;
                }
            }
        }
        else if($input['user_type'] == 'prospect')
        {
            foreach($input['prospect_id'] as $prospect_id){
                $prospect = Prospect::find($prospect_id);
                $cleanedNumber = preg_replace('/\D/', '', $prospect->phone);
                if (preg_match($pattern, $cleanedNumber)) {
                    if (preg_match('/^01\d{8}$/', $cleanedNumber)) {
                        $phone_numbers[] = '254' . substr($cleanedNumber, 1); // Replace '0' with '254'
                    } else {
                        $phone_numbers[] = $cleanedNumber;
                    }
                    $user_ids[] = $prospect->id;
                }
            }
        }
        $contacts = implode(',', $phone_numbers);
        $users = implode(',', $user_ids);
        $data = [
            'subject' =>$input['company_name'].' '.$input['subject'],
            'user_type' =>$input['user_type'],
            'delivery_type' =>$input['delivery_type'],
            'message_type' => 'bulk',
            'phone_numbers' => $contacts,
            'sent_to_ids' => $users,
            'characters' => $input['characters'],
            'cost' => $input['cost'],
            'user_count' => $input['user_count'],
            'total_cost' => $input['total_cost'],

        ];
        if($input['delivery_type'] == 'now'){
            $result = SendSms::create($data);
            $sms = (new RosesmsRepository(auth()->user()->ins))->bulk_sms($data['phone_numbers'], $data['subject'], $result);
            // dd($sms);
            // $sms_response = $sms->getData(true);
            // if($sms_response['status'] == 'success'){
            //     return redirect()->back()->with('flash_success', 'Message Sent Successfully');
            // }else if($sms_response['status'] == 'error'){
            //     return redirect()->back()->with('flash_error', $sms_response['message']);
            // }
        }else{
            $data['scheduled_date'] = datetime_for_database($input['schedule_date']);
            $result = SendSms::create($data);
        }
        // dd($contacts);

        if ($result) {
            DB::commit();
            return true;
        }
        throw new GeneralException("Error creating Sms message");
    }

    /**
     * For updating the respective Model in storage
     *
     * @param SendSms $send_sms
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($send_sms, array $input)
    {
        // dd($input);
        DB::beginTransaction();
        $phone_numbers = [];
        $user_ids = [];
        $pattern = '/^(07\d{8}|2547\d{8})$/';
        if($input['user_type'] == 'employee')
        {
            foreach($input['employee_id'] as $employee){
                $user = Hrm::find($employee);
                if($user->meta){
                    $cleanedNumber = preg_replace('/\D/', '', $user->meta->primary_contact);
                    if (preg_match($pattern, $cleanedNumber)) {
                        $phone_numbers[] = $cleanedNumber;
                        $user_ids[] = $user->id;
                    }
                }
                
            }
        }else if($input['user_type'] == 'customer')
        {
            foreach($input['customer_id'] as $customer_id){
                $customer = Customer::find($customer_id);
                $cleanedNumber = preg_replace('/\D/', '', $customer->phone);
                if (preg_match($pattern, $cleanedNumber)) {
                    $phone_numbers[] = $cleanedNumber;
                    $user_ids[] = $customer->id;
                }
                
            }
        }
        else if($input['user_type'] == 'supplier')
        {
            foreach($input['supplier_id'] as $supplier_id){
                $supplier = Supplier::find($supplier_id);
                $cleanedNumber = preg_replace('/\D/', '', $supplier->phone);
                if (preg_match($pattern, $cleanedNumber)) {
                    $phone_numbers[] = $cleanedNumber;
                    $user_ids[] = $supplier->id;
                }
            }
        }else if($input['user_type'] == 'labourer')
        {
            foreach($input['labourer_id'] as $labourer_id){
                $labourer = CasualLabourer::find($labourer_id);
                $cleanedNumber = preg_replace('/\D/', '', $labourer->phone_number);
                if (preg_match($pattern, $cleanedNumber)) {
                    $phone_numbers[] = $cleanedNumber;
                    $user_ids[] = $labourer->id;
                }
            }
        }
        else if($input['user_type'] == 'prospect')
        {
            foreach($input['prospect_id'] as $prospect_id){
                $prospect = Prospect::find($prospect_id);
                $cleanedNumber = preg_replace('/\D/', '', $prospect->phone);
                if (preg_match($pattern, $cleanedNumber)) {
                    $phone_numbers[] = $cleanedNumber;
                    $user_ids[] = $prospect->id;
                }
            }
        }
        $contacts = implode(',', $phone_numbers);
        $users = implode(',', $user_ids);
        $data = [
            'subject' =>$input['company_name'].' '.$input['subject'],
            'user_type' =>$input['user_type'],
            'delivery_type' =>$input['delivery_type'],
            'message_type' => 'bulk',
            'phone_numbers' => $contacts,
            'sent_to_ids' => $users,
            'characters' => $input['characters'],
            'cost' => $input['cost'],
            'user_count' => $input['user_count'],
            'total_cost' => $input['total_cost'],

        ];
        $data['scheduled_date'] = datetime_for_database($input['schedule_date']);
        $send_sms->update($data);

        if ($send_sms) {
            DB::commit();
            return true;
        }

        throw new GeneralException("Error updating sms message");
    }

    /**
     * For deleting the respective model from storage
     *
     * @param SendSms $send_sms
     * @throws GeneralException
     * @return bool
     */
    public function delete($send_sms)
    {
        if ($send_sms->delete()) {
            return true;
        }

        throw new GeneralException('Error deleting sms message');
    }
}
