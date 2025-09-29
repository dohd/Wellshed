<?php

namespace App\Repositories\Focus\tenant;

use App\Exceptions\GeneralException;
use App\Models\Access\Role\Role;
use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\additional\Additional;
use App\Models\bank\Bank;
use App\Models\Company\Company;
use App\Models\Company\EmailSetting;
use App\Models\Company\SmsSetting;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\department\Department;
use App\Models\hrm\HrmMeta;
use App\Models\invoice\Invoice;
use App\Models\items\InvoiceItem;
use App\Models\items\Prefix;
use App\Models\misc\Misc;
use App\Models\productvariable\Productvariable;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Models\tenant\Tenant;
use App\Models\tenant_package\TenantPackage;
use App\Models\tenant_package\TenantPackageItem;
use App\Models\term\Term;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use Artisan;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Log;

class TenantRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Tenant::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        // id 1 and 2 are reserved for administrative accounts
        $q = $this->query()
            ->where('id', '>', 2)
            ->whereNull('deleted_at')
            ->when(request('statusFilter'), function ($q) {

                if (request('statusFilter') === 'onboarding') {
                    $q->where('billing_status', 'onboarding');
                }
                else $q->where('status', request('statusFilter'));
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
        DB::beginTransaction();

        $package_data = Arr::only($input, [
            'customer_id', 'subscr_term', 'date', 'package_id', 'cost', 
            'total_cost', 'package_item_id', 'vat_rate', 'vat', 'net_cost',
            'no_of_users','subscription_rate'
        ]);
        foreach ($package_data as $key => $value) {
            if (in_array($key, ['date'])) $package_data[$key] = date_for_database($value);
            if (in_array($key, ['cost', 'total_cost', 'vat_rate', 'vat', 'net_cost','subscription_rate'])) {
                $package_data[$key] = numberClean($value);
            }
        }
        $tenant_data = array_diff_key($input, $package_data);
        $tenant_data['billing_date'] = date_for_database($tenant_data['billing_date']);

        // cutoff date set 7days after billing date
        if (!isset($tenant_data['cutoff_date'])) {
            $tenant_data['cutoff_date'] = $tenant_data['billing_date'];
            $graceDays = (int) @$tenant_data['grace_days'];
            if ($graceDays) {
                $tenant_data['cutoff_date'] = date('Y-m-d H:i:s', strtotime($tenant_data['billing_date'] . " +{$graceDays} days"));
            }
            if (strtotime($tenant_data['billing_date']) == strtotime($tenant_data['cutoff_date'])) {
                $tenant_data['cutoff_date'] = date('Y-m-d H:i:s', strtotime($tenant_data['billing_date'] . " +7 days"));
            }
        }

        $main = Tenant::where('is_main',1)->first();
        $tenant_data['company_commission'] = $main->company_commission;
        $tenant_data['commission_1'] = $main->commission_1;
        $tenant_data['commission_2'] = $main->commission_2;
        $tenant_data['commission_3'] = $main->commission_3;

        // create tenant
        $tenant = Tenant::create($tenant_data);
        
        // create tenant package details
        $package_data['company_id'] = $tenant->id;
        $package_data['due_date'] = Carbon::parse(date('Y-m-d'))->addMonths(@$package_data['subscr_term'])->format('Y-m-d');
        unset($package_data['package_item_id']);
        $tenant_package = TenantPackage::create($package_data);

        $input['package_item_id'] = @$input['package_item_id'] ?: [];
        foreach ($input['package_item_id'] as $key => $value) {
            $input['package_item_id'][$key] = [
                'tenant_package_id' => $tenant_package->id,
                'package_item_id' => $value,
            ];
        }
        TenantPackageItem::insert($input['package_item_id']);

        // update tenant user
        $user = User::where('tenant_customer_id', $tenant_package->customer_id)->first();
        if (!$user)  return 'Default User must be created under customer module';
        $user->update(['ins' => $tenant->id, 'updated_by' => auth()->user()->id]);
        $role = Role::withoutGlobalScopes()->where('created_by', $user->id)->first();
        $role->update(['ins' => $tenant->id, 'updated_by' => auth()->user()->id]);            
        HrmMeta::create([
            'user_id' => $user->id,
            'employee_no' => 0,
            'id_number' => 'None',
            'primary_contact' => 'None',
            'secondary_contact' => 'None',
            'gender' => 'None',
            'marital_status' => 'None',
            'id_front' => 'None',
            'id_back' => 'None',
            'home_county' => 'None',
            'home_address' => 'None',
            'residential_address' => 'None',
            'award' => 'None',
            'position' => 'None',
            'specify' => 'None',
        ]);

        $password = random_password();
        $user->password = $password;
        $user->save();
        

        // send email and sms
        try {
            DB::transaction(function () use($tenant, $tenant_package, $password, $user) {
                $company = Company::find(auth()->user()->ins);
                $sms_server = SmsSetting::where('ins', auth()->user()->ins)->first();
                $link = request()->getSchemeAndHttpHost();
                $email_input = [
                    'text' => "Dear {$tenant->sms_email_name},\r\n" 
                        ."Your account was successfully created at {$link} \r\nEmail: {$user->email} \r\nPassword: {$password} \r\n\r\n"
                        ."Regards, \r\n {$company->sms_email_name}",
                    'subject' => "{$tenant->sms_email_name} Login Credentials",
                    'mail_to' => $user->email,
                    'customer_name' => $user->first_name . ' ' . $user->last_name,
                ];
                $customer = Customer::find($tenant_package->customer_id);

                $text = $email_input['text'];
                $cost_per_160 = 0.6;
                $charCount = strlen($text);
                $blocks = ceil($charCount / 160); // Round up to account for partial 160-character blocks

                if($sms_server){
                    $data = [
                        'subject' => $text,
                        'user_type' => 'customer',
                        'delivery_type' => 'now',
                        'message_type' => 'single',
                        'phone_numbers' => $customer->phone,
                        'sent_to_ids' => $customer->id,
                        'characters' => $charCount,
                        'cost' => $cost_per_160,
                        'user_count' => 1,
                        'total_cost' => $cost_per_160 * $blocks, // Calculate total cost based on blocks
                    ];
                    $result = SendSms::create($data);
                    (new RosesmsRepository(auth()->user()->ins))->textlocal($customer->phone, $text, $result);
                }
                $email = (new RosemailerRepository(auth()->user()->ins))->send($email_input['text'], $email_input);
                $email_output = json_decode($email);
                if ($email_output->status === "Success"){
                    $email_data = [
                        'text_email' => $email_input['text'],
                        'subject' => $email_input['subject'],
                        'user_emails' => $email_input['mail_to'],
                        'user_ids' => $user['id'],
                        'user_type' => 'customer',
                        'delivery_type' => 'now',
                        'status' => 'sent'
                    ];
                    SendEmail::create($email_data);
                }
            });
        } catch (\Exception $e) {
            \Log::error('Tenant Email and SMS error: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
        }

        $authPermissions = [];
        $package = optional(optional(optional($tenant)->package)->service)->package;
        if (count($package)) $authPermissions = $package->first()->permissions->pluck('id')->toArray();

        $this->assignNewRoleWithPermissions($authPermissions, $user, 'Company ERP Accounts Administrator');

        // set tenant common configuration
        $this->setCommonConfig($tenant, $user);

        // create PME invoice
        $this->createPMEInvoice($tenant);

        if ($tenant) {
            DB::commit();
            return $tenant;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Tenant $tenant, array $input)
    {
        DB::beginTransaction();

        $package_data = Arr::only($input, [
            'customer_id', 'subscr_term', 'date', 'package_id', 'cost', 
            'total_cost', 'package_item_id', 'vat_rate', 'vat', 'net_cost',
            'no_of_users','subscription_rate',
        ]);
        foreach ($package_data as $key => $value) {
            if (in_array($key, ['date'])) $package_data[$key] = date_for_database($value);
            if (in_array($key, ['cost', 'total_cost', 'vat_rate', 'vat', 'net_cost','subscription_rate'])) {
                $package_data[$key] = numberClean($value);
            }
        }
        $package_data['due_date'] = Carbon::parse(date('Y-m-d'))->addMonths(@$package_data['subscr_term'])->format('Y-m-d');

        $tenant_data = array_diff_key($input, $package_data);

        // set cutoff date to 7days after billing date
        $tenant_data['cutoff_date'] = $tenant_data['billing_date'];
        $graceDays = (int) @$tenant_data['grace_days'];
        if ($graceDays) {
            $tenant_data['cutoff_date'] = date('Y-m-d H:i:s', strtotime($tenant_data['billing_date'] . " +{$graceDays} days"));
        }
        if (strtotime($tenant_data['billing_date']) == strtotime($tenant_data['cutoff_date'])) {
            $tenant_data['cutoff_date'] = date('Y-m-d H:i:s', strtotime($tenant_data['billing_date'] . " +7 days"));
        }
        $main = Tenant::where('is_main',1)->first();
        $tenant_data['company_commission'] = $main->company_commission;
        $tenant_data['commission_1'] = $main->commission_1;
        $tenant_data['commission_2'] = $main->commission_2;
        $tenant_data['commission_3'] = $main->commission_3;

        // update tenant
        $result = $tenant->update($tenant_data);

        // update tenant package details
        unset($package_data['package_item_id']);
        $tenant_package = $tenant->package;
        if ($tenant_package) {
            $tenant_package->update($package_data);
            $tenant_package->items()->delete();
            $input['package_item_id'] = @$input['package_item_id'] ?: [];
            foreach ($input['package_item_id'] as $key => $value) {
                $input['package_item_id'][$key] = [
                    'tenant_package_id' => $tenant_package->id,
                    'package_item_id' => $value,
                ];
            }
            TenantPackageItem::insert($input['package_item_id']);
        }

        // update tenant user
        $user = User::where('tenant_customer_id', $tenant_package->customer_id)->first();
        if (!$user) throw ValidationException::withMessages(['Default User must be created under customer module']);
        $user->update(['ins' => $tenant->id, 'updated_by' => auth()->user()->id]);
        $role = Role::withoutGlobalScopes()->where('created_by', $user->id)->first();
        if ($role) $role->update(['ins' => $tenant->id, 'updated_by' => auth()->user()->id]);            
        
        $user_meta = HrmMeta::where('user_id', $user->id)->first();
        if (!$user_meta) {
            HrmMeta::create([
                'user_id' => $user->id,
                'employee_no' => 0,
                'id_number' => 'None',
                'primary_contact' => 'None',
                'secondary_contact' => 'None',
                'gender' => 'None',
                'marital_status' => 'None',
                'id_front' => 'None',
                'id_back' => 'None',
                'home_county' => 'None',
                'home_address' => 'None',
                'residential_address' => 'None',
                'award' => 'None',
                'position' => 'None',
                'specify' => 'None',
            ]);
        }

        $this->createPMEInvoice($tenant);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Tenant $tenant)
    {
        return $tenant->update(['deleted_at' => now()]);
    }

    public function assignNewRoleWithPermissions(array $permissionIds, $user, string $newRoleName)
    {
        DB::transaction(function () use ($permissionIds, $user, $newRoleName) {
            // Delete the user's previous roles and permissions
            DB::table('role_user')->where('user_id', $user->id)->delete();
            DB::table('permission_user')->where('user_id', $user->id)->delete();

            // Create a new role
            $roleId = DB::table('roles')->insertGetId([
                'name' => $newRoleName,
                'all' => 0,
                'sort' => 0,
                'status' => 1,
                'ins' => $user->ins,
            ]);

            // Assign permissions to the role
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_user')->insert([
                    'user_id' => $user->id,
                    'permission_id' => $permissionId
                ]);
            }

            // Assign the role to the user
            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $roleId
            ]);
        });
    }

    /**
     * Replicate Common Configuration
     */
    public function setCommonConfig($tenant, $user)
    {
        $results = [];
        $params = ['user_id' => $user->id, 'ins' => $tenant->id];
        $models = [
            'accounts' => Account::query(),
            'tr_categories' => Transactioncategory::query(),
            'prod_units' => Productvariable::query(),
            'currencies' => Currency::query(),
            'vat_rates' => Additional::query(),
            'miscs' => Misc::query(),
            'prefixes' => Prefix::query(),
            'departments' => Department::query(),
            'sms_setting' => SmsSetting::query(),
            'email_setting' => EmailSetting::query(),
        ];
        foreach ($models as $key => $model) {
            $items = [];
            $collection = $model->get();
            foreach ($collection as $i => $item) {
                $item->fill($params);
                $item = $item->toArray();
                if (isset($item['has_sub_accounts'])) unset($item['has_sub_accounts']);
                if ($key == 'accounts') {
                    unset($item['opening_balance'],$item['opening_balance_date']); 
                    if (!isset($item['system'])) $item['note'] = null; 
                }
                if (in_array($key, ['sms_setting', 'email_setting'])) {
                    unset($item['user_id']);
                }
                unset($item['id'], $item['created_at'], $item['updated_at']);
                $items[] = $item;
            }
            $result = $model->insert($items);
            if ($result) $results[$key] = $items;            
        }

        // update currency_id on replicated accounts
        $originCurrencies = Currency::withoutGlobalScopes()->where('ins', auth()->user()->ins)->get();
        $newAccounts = Account::withoutGlobalScopes()->where('ins', $params['ins'])->whereNotNull('currency_id')->get();
        foreach ($newAccounts as $newAccount) {
            foreach ($originCurrencies as $originCurrency) {
                $newCurrency = Currency::withoutGlobalScopes()->where('ins', $params['ins'])
                    ->where('code', $originCurrency->code)
                    ->where('rate', $originCurrency->rate)
                    ->first();
                if ($newCurrency) {
                    $newAccount->update(['currency_id' => $newCurrency->id]);
                    break;
                }
            }
        }
        
        return $results;
    }    

    /**
     * Create PME Subscription Invoice
     */
    public function createPMEInvoice($tenant)
    {
        try {
            $bank = Bank::where(['enable' => 'yes'])->first();
            $term = Term::where('title', 'LIKE', '%No Terms%')->first();
            $currency = Currency::where(['code' => 'KES'])->first();
            $incomeAccount = Account::where('holder', 'LIKE', '%Project Management%')
            ->whereHas('account_type_detail', fn($q) => $q->where('system_rel', 'income'))
            ->first();

            $errorMsg = '';
            if (!$bank) $errorMsg = 'Default enabled bank required';
            if (!$term) $errorMsg = 'Default `No Terms` required';
            if (!$currency) $errorMsg = 'Default KES currency required';
            if (!$incomeAccount) $errorMsg = 'Income account ledger required';
            if ($errorMsg) throw ValidationException::withMessages([$errorMsg]);

            $customer = $tenant->customer;

            // Clear previous open invoices
            $invoices = $customer->invoices()->doesntHave('payments')
            ->where([
                'invoicedate' => date_for_database($customer->tenant->billing_date),
                'notes' => 'Project Management ERP Subscription Fee',
            ])
            ->get();
            foreach ($invoices as $invoice) {
                $invoice->transactions()->delete();
                $invoice->products()->delete();
                $invoice->delete();
            }
                
            // create new invoice
            $tenantPackage = $customer->tenant_package;
            $invoice = Invoice::create([
                'tid' => Invoice::max('tid')+1,
                'customer_id' => $customer->id,
                'invoicedate' => $customer->tenant->billing_date,
                'invoiceduedate' => $customer->tenant->billing_date,
                'tax_id' => $tenantPackage->vat_rate,
                'bank_id' => $bank->id,
                'validity' => 0,
                'account_id' => $incomeAccount->id,
                'currency_id' => $currency->id,
                'fx_curr_rate' => $currency->rate,
                'term_id' => $term->id,
                'notes' => 'Project Management ERP Subscription Fee',
                'status' => 'due',
                'taxable' => $tenantPackage->net_cost,
                'subtotal' => $tenantPackage->net_cost,
                'tax' => $tenantPackage->vat,
                'total' => $tenantPackage->total_cost,
            ]);
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'numbering' => 1,
                'description' => $invoice['notes'],
                'product_qty' => 1,
                'product_price' => $invoice['taxable'],
                'product_tax' => $invoice['tax'],
                'product_subtotal' => $invoice['subtotal'],
                'tax_rate' => $invoice['tax_id'],
                'product_amount' => $invoice['total'],
                'unit' => 'ITEM',
            ]);
            // accounting
            (new \App\Repositories\Focus\invoice\InvoiceRepository)->post_invoice($invoice);
                
            Log::info(now() . ' Successful Software Invoice ID: ' . $invoice->id);
        } catch (\Throwable $th) {
            Log::error(now() . ' Failed Software Invoice Customer ID: ' . $customer->id);
            throw $th;
        }
    }
}
