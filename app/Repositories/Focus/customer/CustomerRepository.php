<?php

namespace App\Repositories\Focus\customer;

use DB;
use App\Models\customer\Customer;
use App\Exceptions\GeneralException;
use App\Http\Controllers\ClientSupplierAuth;
use App\Jobs\NotifyCustomerRegistration;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;
use App\Models\customer\CustomerAddress;
use App\Models\hrm\Hrm;
use App\Models\payment_receipt\PaymentReceipt;
use App\Models\subpackage\SubPackage;
use App\Models\subscription\Subscription;
use App\Models\target_zone\CustomerZoneItem;
use App\Repositories\Accounting;
use App\Repositories\CustomerSupplierBalance;
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

        $q->with(['paymentReceipts' => fn($q) => $q->select('id', 'customer_id', 'debit', 'credit')]);

        return $q;
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
        // dd($input);
        DB::beginTransaction();

        $ins = auth()->user()->ins;

        // create customer
        $customer = Customer::create([
            'tid' => Customer::max('tid')+1,
            'segment' => $input['segment'],
            'company' => $input['company'],
            'name' => $input['company'] ?? $input['full_name'],
            'email' => $input['email'],
            'phone' => $input['phone_no'],
            'ins' => $ins,
        ]); 
        if ($input['onetime_fee'] === 'exclude') {
            $customer->update(['has_onetime_fee' => null]);
        }   

        // create user
        $emailExists = Hrm::where('email', $input['email'])->exists();
        if ($emailExists) return errorHandler('Email: ' . $input['email'] . ' is already taken!');

        $user = Hrm::create([
            'tid' => Hrm::max('tid')+1,
            'first_name' => $input['first_name'] ?? $input['company'],
            'last_name' => $input['last_name'],
            'username' => $input['company'] ?? $input['full_name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'login_access' => 1,
            'status' => 1,
            'confirmed' => 1,
            'customer_id' => $customer->id,
            'ins' => $ins,
        ]);

        // create subscription
        $subscr = Subscription::create([
            'customer_id' => $customer->id,
            'sub_package_id' => $input['sub_package_id'],
            'start_date' => now(),
            'end_date' => date('Y-m-d H:i:s', strtotime('+1 month')),
            'ins' => $ins,
        ]);

        // create address
        $addressData = request()->only('building_name', 'floor_no', 'door_no', 'additional_info');
        $addressData['ins'] = $ins;
        $customerAddr = CustomerAddress::create($addressData);

        // create zone items
        foreach ($input['target_zone_item_id'] as $id) {
            $customerZoneItems[] = CustomerZoneItem::create([
                'target_zone_item_id' => $id,
                'target_zone_id' => $input['target_zone_id'],
                'customer_id' => $customer->id,
                'customer_address_id' => $customerAddr->id,
            ]);
        }

        // debit charge for the subscription plan 
        $package = SubPackage::findOrFail(request('sub_package_id'));
        $amount = $package->price + $package->onetime_fee;
        if ($input['onetime_fee'] === 'exclude') {
            $amount = $package->price;
        }
        $notes = stripos($package->name, 'Plan') !== false? $package->name : "{$package->name} Plan";
            
        $receipt = PaymentReceipt::create([
            'entry_type' => 'debit',
            'customer_id' => $customer->id,
            'date' => now()->toDateString(),
            'notes' => $notes,
            'amount' => $amount,
            'debit' => $amount,
            'subscription_id' => $subscr->id,
            'ins' => $ins,
        ]);

        DB::commit();

        if ($user) NotifyCustomerRegistration::dispatch($user,$input['password'],$ins);

        return $customer;
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
        // dd($input);
        DB::beginTransaction();

        $ins = auth()->user()->ins;

        // create customer
        $customer->update([
            'segment' => $input['segment'],
            'company' => $input['company'],
            'name' => $input['company'] ?? $input['full_name'],
            'email' => $input['email'],
            'phone' => $input['phone_no'],
        ]); 
        if ($input['onetime_fee'] === 'exclude') {
            $customer->update(['has_onetime_fee' => null]);
        }   

        // create user
        $emailExists = Hrm::where('customer_id', '!=', $customer->id)
            ->where('email', $input['email'])->exists();
        if ($emailExists) throw ValidationException::withMessages(['email' => 'Email: ' . $input['email'] . ' is already taken!']);

        $customer->hrm->update([
            'first_name' => $input['first_name'] ?? $input['company'],
            'last_name' => $input['last_name'],
            'username' => $input['company'] ?? $input['full_name'],
            'email' => $input['email'],
        ]);

        // create address
        $addressData = request()->only('building_name', 'floor_no', 'door_no', 'additional_info');
        $customer->mainAddress->update($addressData);

        // create zone items
        foreach ($input['target_zone_item_id'] as $id) {
            CustomerZoneItem::where('customer_id', $customer->id)->update([
                'target_zone_item_id' => $id,
                'target_zone_id' => $input['target_zone_id'],
            ]);
        }
        
        DB::commit();

        return $customer;
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
        dd($customer->id);
    }
}
