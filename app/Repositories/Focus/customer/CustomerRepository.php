<?php

namespace App\Repositories\Focus\customer;

use DB;
use App\Models\customer\Customer;
use App\Exceptions\GeneralException;
use App\Http\Controllers\ClientSupplierAuth;
use App\Models\account\Account;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;
use App\Models\branch\Branch;
use App\Models\Company\Company;
use App\Models\manualjournal\Journal;
use App\Repositories\Accounting;
use App\Repositories\CustomerSupplierBalance;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Class CustomerRepository.
 */
class CustomerRepository extends BaseRepository
{
    use Accounting, CustomerStatement, ClientSupplierAuth, CustomerSupplierBalance;

    /**
     *customer_picture_path .
     *
     * @var string
     */
    protected $customer_picture_path;


    /**
     * Storage Class Object.
     *
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;

    /**
     * Associated Repository Model.
     */
    const MODEL = Customer::class;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->customer_picture_path = 'img' . DIRECTORY_SEPARATOR . 'customer' . DIRECTORY_SEPARATOR;
        $this->storage = Storage::disk('public');
    }

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        $q->orderByRaw("CASE WHEN LOWER(name) = LOWER(?) THEN 1 ELSE 2 END", ['Walk-In'])->orderByRaw('LOWER(name) ASC');

        return $q->get(['id', 'name','company','email', 'phone', 'currency_id', 'address','picture','active','created_at']);
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {
        $user_data = Arr::only($input, ['first_name', 'last_name', 'email', 'password', 'picture']);
        $user_data['email'] = @$input['user_email'];
        unset($input['first_name'], $input['last_name'], $input['user_email'], $input['password_confirmation']);

        if (isset($input['picture'])) $input['picture'] = $this->uploadPicture($input['picture']);
            
        $is_company = Customer::where('company', $input['company'])->exists();
        if ($is_company) throw ValidationException::withMessages(['Company already exists']);
        $email_exists = Customer::where('email', $input['email'])->whereNotNull('email')->exists();
        if ($email_exists) throw ValidationException::withMessages(['Duplicate email']);

        if (@$input['taxid']) {
            $taxid_exists = Customer::where('taxid', $input['taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists) throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $input['taxid']])->whereNotNull('taxid')->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed']);

            if (config('services.efris.base_url')) {
                // handle UGX validation
            } else {
                $taxid = $input['taxid'];
                $taxid_length = strlen($taxid);
                if ($taxid_length == 10) {
                    // Check if tax ID contains exactly 10 digits
                    if (!preg_match("/^[0-9]{10}$/", $taxid)) {
                        throw ValidationException::withMessages(['Tax Pin must be a 10-digit number']);
                    }
                } elseif ($taxid_length == 11) {
                    // Check if tax ID follows the "P123456789A" or "A123456789P" format
                    if (!preg_match("/^[PA][0-9]{9}[A-Z]$/i", $taxid)) {
                        throw ValidationException::withMessages([
                            'Tax Pin should be 10 digits or follow the format: "P123456789A" or "A123456789P"'
                        ]);
                    }
                } else {
                    // Invalid length
                    throw ValidationException::withMessages(['Customer Tax Pin should contain either 10 digits or follow the format: "P123456789A" or "A123456789P"']);
                }
            }
        }        
        
        DB::beginTransaction();

        // create customer
        $input['open_balance'] = numberClean($input['open_balance']);
        $input['open_balance_date'] = date_for_database($input['open_balance_date']);  
        $input['dob'] = date_for_database($input['dob']);  
        // if ($input['ar_account_id']) {
        //     $account = Account::find($input['ar_account_id']);
        //     if ($account) $input['currency_id'] = $account->currency_id;
        // }
        $customer = Customer::create($input);

        // create branches
        $branches = [['name' => 'All Branches'], ['name' => 'Head Office']];
        foreach ($branches as $key => $branch) {
            $branches[$key]['customer_id'] = $customer->id;
            $branches[$key]['ins'] = $customer->ins;
        }
        Branch::insert($branches);

        // opening balance
        if ($customer->open_balance > 0) {
            $tr_data = $this->customer_opening_balance($customer, 'create'); 
            $journal = new Journal($tr_data);
            $journal->id = $tr_data['id'];
            $this->post_customer_opening_balance($journal); 
        }
        // customer authorization
        $this->createAuth($customer, $user_data, 'client');

        if ($customer) {
            DB::commit();
            return $customer;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Customer $customer
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($customer, array $input)
    { 
        $user_data = Arr::only($input, ['first_name', 'last_name', 'password', 'picture']);
        $user_data['email'] = @$input['user_email'];
        unset($input['first_name'], $input['last_name'], $input['user_email'], $input['password_confirmation']);
        if (empty($input['password'])) unset($input['password']);

        if (isset($input['picture'])) {
            $this->removePicture($customer, 'picture');
            $input['picture'] = $this->uploadPicture($input['picture']);
        }
    
        $is_company = Customer::where('id', '!=', $customer->id)->where('company', $input['company'])->exists();
        if ($is_company) throw ValidationException::withMessages(['Company already exists']);
        $email_exists = Customer::where('id', '!=', $customer->id)->where('email', $input['email'])->whereNotNull('email')->exists();
        if ($email_exists) throw ValidationException::withMessages(['Email already in use']);

        if (@$input['taxid']) {
            $taxid_exists = Customer::where('id', '!=', $customer->id)->where('taxid', $input['taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists) throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $input['taxid']])->whereNotNull('taxid')->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed']);

            // Validate tax ID format
            if (config('services.efris.base_url')) {
                // handle UGX validation
            } else {
                $taxid = $input['taxid'];
                $taxid_length = strlen($taxid);
                if ($taxid_length == 10) {
                    // Check if tax ID contains exactly 10 digits
                    if (!preg_match("/^[0-9]{10}$/", $taxid)) {
                        throw ValidationException::withMessages(['Tax Pin must be a 10-digit number']);
                    }
                } elseif ($taxid_length == 11) {
                    // Check if tax ID follows the "P123456789A" or "A123456789P" format
                    if (!preg_match("/^[PA][0-9]{9}[A-Z]$/i", $taxid)) {
                        throw ValidationException::withMessages([
                            'Tax Pin should be 10 digits or follow the format: "P123456789A" or "A123456789P"'
                        ]);
                    }
                } else {
                    // Invalid length
                    throw ValidationException::withMessages(['Customer Tax Pin should contain either 10 digits or follow the format: "P123456789A" or "A123456789P"']);
                }
            }
        }
        $input = array_replace($input, [
            'open_balance' => numberClean($input['open_balance']),
            'credit_limit' => numberClean($input['credit_limit']),
            'open_balance_date' =>  date_for_database($input['open_balance_date']),
            'dob' =>  date_for_database($input['dob'])
        ]);
        if (!+$input['open_balance']) $input['open_balance_date'] = null;

        DB::beginTransaction();

        // update customer
        // if ($input['ar_account_id']) {
        //     $account = Account::find($input['ar_account_id']);
        //     if ($account) $input['currency_id'] = $account->currency_id;
        // }
        $result = $customer->update($input);

        /**accounting */   
        if ($customer->open_balance > 0) {
            $tr_data = $this->customer_opening_balance($customer, 'update'); 
            $journal = new Journal($tr_data);
            $journal->id = $tr_data['id'];
            $this->post_customer_opening_balance($journal);    
        } else {
            $journal = @$customer->journal;
            if ($journal) {
                $invoice = @$journal->invoice;
                if ($invoice) {
                    if (count($invoice->payments)) {
                        foreach ($invoice->payments as $item) $tids[] = @$item->paid_invoice->tid ?: '';
                        throw ValidationException::withMessages(['Customer has attached Payments: ('.implode(', ', $tids).')']);
                    }
                    $invoice->delete();
                }
                $journal->transactions()->delete();
                $journal->delete();
            } 
        }
        
        // customer authorization
        $this->updateAuth($customer, $user_data, 'client');

        if ($result) {
            DB::commit();
            return true;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Customer $customer
     * @return bool
     * @throws GeneralException
     */
    public function delete($customer)
    {
        if ($customer->id == 1) throw ValidationException::withMessages(['Cannot delete default customer']);
        if ($customer->leads()->exists()) throw ValidationException::withMessages(['Customer has attached Tickets']);
        if ($customer->quotes()->exists()) throw ValidationException::withMessages(['Customer has attached Quotes']);
        if ($customer->projects()->exists()) throw ValidationException::withMessages(['Customer has attached Projects']);
        if ($customer->invoices()->exists()) throw ValidationException::withMessages(['Customer has attached Invoices']);

        DB::beginTransaction();

        $this->deleteAuth($customer, 'client');
        $customer->branches()->delete();
        $result = $customer->delete();
        
        if ($result) {
            DB::commit();
            return true;
        }
    }

    /*
    * Upload logo image
    */
    public function uploadPicture($file)
    {
        $image = time() . $file->getClientOriginalName();
        $this->storage->put($this->customer_picture_path . $image, file_get_contents($file->getRealPath()));
        return $image;
    }

    /*
    * Remove logo or favicon icon
    */
    public function removePicture(Customer $customer, $type)
    {
        $path = $this->customer_picture_path;
        $storage_exists = $this->storage->exists($path . $customer->$type);
        if ($customer->$type && $storage_exists) {
            $this->storage->delete($path . $customer->$type);
        }
        return $customer->update([$type => '']);    
    }
}
