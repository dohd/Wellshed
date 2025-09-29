<?php

namespace App\Repositories\Focus\send_email;

use DB;
use Carbon\Carbon;
use App\Models\send_email\SendEmail;
use App\Exceptions\GeneralException;
use App\Models\casual\CasualLabourer;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\prospect\Prospect;
use App\Models\supplier\Supplier;
use App\Repositories\BaseRepository;
use App\Repositories\Focus\general\RosemailerRepository;

/**
 * Class SendEmailRepository.
 */
class SendEmailRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = SendEmail::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()->take(100)->orderBy('id','desc')
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
        $emails = [];
        $user_ids = [];
        if($input['user_type'] == 'employee')
        {
            foreach($input['employee_id'] as $employee){
                $user = Hrm::find($employee);
                if($user->meta){
                    $emails[] = $user->email;
                    $user_ids[] = $user->id;
                }
                
            }
        }else if($input['user_type'] == 'customer')
        {
            foreach($input['customer_id'] as $customer_id){
                $customer = Customer::find($customer_id);
                $emails[] = $customer->email;
                $user_ids[] = $customer->id;
                
            }
        }
        else if($input['user_type'] == 'supplier')
        {
            foreach($input['supplier_id'] as $supplier_id){
                $supplier = Supplier::find($supplier_id);
                $emails[] = $supplier->email;
                $user_ids[] = $supplier->id;
            }
        }else if($input['user_type'] == 'labourer')
        {
            foreach($input['labourer_id'] as $labourer_id){
                $labourer = CasualLabourer::find($labourer_id);
                $emails[] = $labourer->email;
                $user_ids[] = $labourer->id;
            }
        }
        else if($input['user_type'] == 'prospect')
        {
            foreach($input['prospect_id'] as $prospect_id){
                $prospect = Prospect::find($prospect_id);
                $emails[] = $prospect->email;
                $user_ids[] = $prospect->id;
            }
        }
        $users = implode(',', $user_ids);
        $email_implode = implode(',', $emails);
        $data = [
            'subject' => $input['subject'],
            'text_email' => $input['text_email'],
            'user_type' => $input['user_type'],
            'delivery_type' => $input['delivery_type'],
            'user_ids' => $users,
            'user_emails' => $email_implode,
        ];
        // dd($data);
        if($input['delivery_type'] == 'now'){
            $mail_to = array_shift($emails);
            $others = $emails;
            //Send EMAILs
            $email_input = [
                'text' => $data['text_email'],
                'subject' => $data['subject'],
                'email' => $others,
                'mail_to' => $mail_to
            ];
            $email = (new RosemailerRepository(auth()->user()->ins))->send_group($email_input['text'], $email_input);
            $email_output = json_decode($email);
            if ($email_output->status === "Success"){

                $email_data = [
                    'text_email' => $email_input['text'],
                    'subject' => $email_input['subject'],
                    'user_emails' => $email_implode,
                    'user_ids' => $users,
                    'user_type' => $input['user_type'],
                    'delivery_type' => $input['delivery_type'],
                    'status' => 'sent'
                ];
                $result = SendEmail::create($email_data);
            }
            
        }else{
            $data['scheduled_date'] = datetime_for_database($input['scheduled_date']);
            $data['status'] = 'not_sent';
            $result = SendEmail::create($data);
        }

        if ($result) {
            DB::commit();
            return true;
        }
        throw new GeneralException('Error Creating an Email');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param send_email $send_email
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($send_email, array $input)
    {
    	DB::beginTransaction();
        $emails = [];
        $user_ids = [];
        if($input['user_type'] == 'employee')
        {
            foreach($input['employee_id'] as $employee){
                $user = Hrm::find($employee);
                if($user->meta){
                    $emails[] = $user->email;
                    $user_ids[] = $user->id;
                }
                
            }
        }else if($input['user_type'] == 'customer')
        {
            foreach($input['customer_id'] as $customer_id){
                $customer = Customer::find($customer_id);
                $emails[] = $customer->email;
                $user_ids[] = $customer->id;
                
            }
        }
        else if($input['user_type'] == 'supplier')
        {
            foreach($input['supplier_id'] as $supplier_id){
                $supplier = Supplier::find($supplier_id);
                $emails[] = $supplier->email;
                $user_ids[] = $supplier->id;
            }
        }else if($input['user_type'] == 'labourer')
        {
            foreach($input['labourer_id'] as $labourer_id){
                $labourer = CasualLabourer::find($labourer_id);
                $emails[] = $labourer->email;
                $user_ids[] = $labourer->id;
            }
        }
        else if($input['user_type'] == 'prospect')
        {
            foreach($input['prospect_id'] as $prospect_id){
                $prospect = Prospect::find($prospect_id);
                $emails[] = $prospect->email;
                $user_ids[] = $prospect->id;
            }
        }
        $users = implode(',', $user_ids);
        $email_implode = implode(',', $emails);
        $data = [
            'subject' => $input['subject'],
            'text_email' => $input['text_email'],
            'user_type' => $input['user_type'],
            'delivery_type' => $input['delivery_type'],
            'user_ids' => $users,
            'user_emails' => $email_implode,
        ];

        $data['scheduled_date'] = datetime_for_database($input['scheduled_date']);
        $data['status'] = 'not_sent';
        $send_email->update($data);

        if ($send_email) {
            DB::commit();
            return true;
        }

        throw new GeneralException('Error Updating an Email');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param send_email $send_email
     * @throws GeneralException
     * @return bool
     */
    public function delete($send_email)
    {
        if ($send_email->delete()) {
            return true;
        }

        throw new GeneralException('Error Deleting an Email');
    }
}
