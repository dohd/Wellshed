<?php

namespace App\Http\Controllers\Focus\general;

use App\Models\Company\ConfigMeta;
use App\Models\customfield\Customfield;
use App\Models\items\CustomEntry;
use App\Models\misc\Misc;
use App\Repositories\Focus\general\CompanyRepository;
use App\Http\Responses\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\general\ManageCompanyRequest;
use App\Models\Company\Company;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\account\AccountType;
use App\Models\classlist\Classlist;
use App\Models\customer\Customer;
use App\Models\items\OpeningStockItem;
use App\Models\manualjournal\Journal;
use App\Models\opening_stock\OpeningStock;
use App\Models\product\ProductVariation;
use App\Models\project\Project;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;
use App\Models\warehouse\Warehouse;
use DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class CompanyController extends Controller
{
    /**
     * variable to store the repository object
     * @var CompanyRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param CompanyRepository $repository ;
     */
    public function __construct(CompanyRepository $repository)
    {
        $this->repository = $repository;
    }

    public function manage(ManageCompanyRequest $request)
    {
        $company = Company::where('id', '=', auth()->user()->ins)->first();
        $fields = Customfield::where('module_id', '=', 6)->get()->groupBy('field_type');
        $fields_raw = array();
        if (isset($fields['text'])) {
            foreach ($fields['text'] as $row) {
                $data = CustomEntry::where('custom_field_id', '=', $row['id'])->where('module', '=', 6)->where('rid', '=', $company->id)->first();
                $fields_raw['text'][] = array('id' => $row['id'], 'name' => $row['name'], 'default_data' => $data['data']);
            }
        }
        if (isset($fields['number'])) {
            foreach ($fields['number'] as $row) {
                $data = CustomEntry::where('custom_field_id', '=', $row['id'])->where('module', '=', 6)->where('rid', '=', $company->id)->first();
                $fields_raw['number'][] = array('id' => $row['id'], 'name' => $row['name'], 'default_data' => $data['data']);
            }
        }
        $fields_data = custom_fields($fields_raw);

        return  view('focus.general.company', compact('company', 'fields_data'));
    }

    public function update(ManageCompanyRequest $request)
    {
        $request->validate([
            'fiscal_month' => 'required',
            'logo' => 'nullable|mimes:jpeg,png',
            'footer' => 'nullable|mimes:jpeg,png',
            'theme_logo' => 'nullable|mimes:jpeg,png',
            'icon' => 'nullable|mimes:ico',
            'stamp' => 'nullable|mimes:jpeg,png',
        ]);

        $data = $request->only([
            'cname','sms_email_name', 'address', 'city', 'region', 'country', 'postbox', 'taxid', 'logo', 'footer', 'theme_logo','stamp',
            'icon', 'phone', 'email', 'clock_in', 'clock_out', 'etr_code','performance_percent', 'fiscal_month','notification_number','rate', 'review_url',
            'whatsapp_business_url', 'default_quote_type','company_commission','commission_1','commission_2','commission_3','location','website_url',
            'linkedIn_url','twitter_url','facebook_url','instagram_url','tiktok_url',
            'chatgpt_email', 'whatsapp_business_account_id', 'whatsapp_phone_no_id', 'meta_developer_app_id', 'whatsapp_access_token',
        ]);
        $data2 = $request->only(['custom_field']);

        try {
            $result = $this->repository->update(compact('data', 'data2'));
        } catch (\Throwable $th) {
            return errorHandler('Error updating business settings', $th);
        }
        
        return new RedirectResponse(route('biller.business.settings'), ['flash_success' => trans('business.updated')]);
    }

    public function billing_settings(ManageCompanyRequest $request)
    {
        $defaults = \App\Models\Company\ConfigMeta::get()->groupBy('feature_id');

        if (@$request->get('p1')) {
            $data['section'] = true;
        } else {
            $data['section'] = false;
            $company = Company::where('id', '=', auth()->user()->ins)->first();
            $data['warehouses'] = \App\Models\warehouse\Warehouse::all();
            $data['additionals'] = \App\Models\additional\Additional::all();
            $data['currencies'] = \App\Models\currency\Currency::all();
            $data['accounts'] = \App\Models\account\Account::all();
            $data['transaction_categories'] = \App\Models\transactioncategory\Transactioncategory::all();
            $account_types = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 17)->first('value1');
            $account_types = json_decode($account_types->value1, true);
            $account_types = implode(",", $account_types);
            $data['status'] = Misc::where('section', '=', 2)->get();
        }
        return new ViewResponse('focus.general.billing_settings', compact('data', 'defaults', 'account_types'));
    }

    public function billing_settings_update(ManageCompanyRequest $request)
    {
        $data = $this->repository->billing_settings($request);

        if (isset($data['a'])) return new RedirectResponse(route('biller.business.billing_settings') . '?p1=alert', ['flash_success' => trans('business.billing_settings_update')]);
        return new RedirectResponse(route('biller.business.billing_settings'), ['flash_success' => trans('business.billing_settings_update')]);
    }

    public function email_sms_settings(ManageCompanyRequest $request)
    {
        $smtp = \App\Models\Company\EmailSetting::first();
        $sms = \App\Models\Company\SmsSetting::first();
        $url_short = \App\Models\Company\ConfigMeta::where('feature_id', '=', 7)->first();
        return new ViewResponse('focus.general.email_settings', compact('smtp', 'sms', 'url_short'));
    }

    public function email_settings_update(ManageCompanyRequest $request)
    {
        $message = $this->repository->email_settings($request);
        return new RedirectResponse(route('biller.business.email_sms_settings'), ['flash_success' => $message]);
    }

    public function activate(ManageCompanyRequest $request)
    {
        if (single_ton()) {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            if ($request->post()) {
                $out = active($request->post());
                return new RedirectResponse(route('biller.dashboard'), $out);
            }
            return new ViewResponse('focus.general.active');
        }
        return new ViewResponse('focus.general.not_applicable');
    }


    public function billing_preference(ManageCompanyRequest $request)
    {

        if ($request->post()) {
            $data = $this->repository->update_settings($request);
            return new RedirectResponse(route('biller.settings.billing_preference'), ['flash_success' => trans('business.billing_settings_update')]);
        }

        $defaults = \App\Models\Company\ConfigMeta::get()->groupBy('feature_id');

        $company = Company::where('id', '=', auth()->user()->ins)->first();
        $data['warehouses'] = \App\Models\warehouse\Warehouse::all();
        $data['additionals'] = \App\Models\additional\Additional::all();

        return view('focus.general.settings.billing_pref', compact('data', 'defaults'));

    }


    public function payment_preference(ManageCompanyRequest $request)
    {

        if ($request->post()) {
            $data = $this->repository->update_settings($request);
            return new RedirectResponse(route('biller.settings.payment_preference'), ['flash_success' => trans('business.billing_settings_update')]);
        }

        $defaults = \App\Models\Company\ConfigMeta::get()->groupBy('feature_id');

        $data['currencies'] = \App\Models\currency\Currency::all();
        $data['additionals'] = \App\Models\additional\Additional::all();
        $data['accounts'] = \App\Models\account\Account::all();
        return view('focus.general.settings.payment_pref', compact('data', 'defaults'));

    }


    public function accounts(ManageCompanyRequest $request)
    {

        if ($request->post()) {
            $data = $this->repository->update_settings($request);
            return new RedirectResponse(route('biller.settings.accounts'), ['flash_success' => trans('business.billing_settings_update')]);
        }

        $defaults = \App\Models\Company\ConfigMeta::get()->groupBy('feature_id');

        $data['accounts'] = \App\Models\account\Account::all();
        $m_types = ConfigMeta::withoutGlobalScopes()->where('feature_id', '=', 17)->first();
        $account_types = json_decode($m_types->value1, true);
        if (is_array($account_types)) $account_types = implode(",", $account_types);
        $payment_methods = json_decode($m_types->value2, true);
        if (is_array($payment_methods)) $payment_methods = implode(",", $payment_methods);
        $data['transaction_categories'] = \App\Models\transactioncategory\Transactioncategory::all();
        return view('focus.general.settings.accounts', compact('data', 'defaults', 'account_types', 'payment_methods'));

    }

    public function auto_communication(ManageCompanyRequest $request)
    {

        if ($request->post()) {
            $data = $this->repository->update_settings($request);
            return new RedirectResponse(route('biller.settings.auto_communication'), ['flash_success' => trans('business.billing_settings_update')]);
        }

        $defaults = \App\Models\Company\ConfigMeta::get()->groupBy('feature_id');


        return view('focus.general.settings.auto_communication', compact('data', 'defaults'));

    }

    public function notification_email(ManageCompanyRequest $request)
    {

        if ($request->post()) {
            $this->repository->update_settings($request->post());
            return new RedirectResponse(route('biller.settings.notification_email'), ['flash_success' => trans('business.billing_settings_update')]);
        }

        $feature = feature(11);
        $data = json_decode($feature->value2, true);
        $email = $feature->value1;
        return view('focus.general.settings.notification_email', compact('data', 'email'));

    }

    public function localization(ManageCompanyRequest $request)
    {

        if ($request->post()) {
            $data = $this->repository->update_settings($request);
            return new RedirectResponse(route('biller.settings.localization'), ['flash_success' => trans('business.billing_settings_update')]);
        }

        $defaults = \App\Models\Company\ConfigMeta::get()->groupBy('feature_id');


        $data['additionals'] = \App\Models\additional\Additional::all();

        return view('focus.general.settings.localization', compact('data', 'defaults'));

    }

    public function reclassify_transactions(ManageCompanyRequest $request)
    {   
        /**Store Data */
        if ($request->post()) {
            $request->validate(['tr_id' => 'required', 'account_id' => 'required', 'note' => 'required']);
            $input = $request->only('prev_account_id', 'tr_id', 'account_id', 'classlist_id', 'note');
            try {
                DB::beginTransaction();
                // reclassify transactions
                $tr_ids = explode(',', $input['tr_id']);
                $q = Transaction::whereIn('id', $tr_ids);
                $q->update(['account_id' => $input['account_id']]);
                if ($input['classlist_id']) $q->update(['classlist_id' => $input['classlist_id']]);
                // reclassification log
                DB::table('reclassify_transaction_logs')->insert([
                    'prev_account_id' => $input['prev_account_id'],
                    'tr_id' => $input['tr_id'],
                    'account_id' => $input['account_id'],
                    'classlist_id' => $input['classlist_id'],
                    'note' => $input['note'],
                    'user_id' => auth()->user()->id,
                    'ins' => auth()->user()->ins,
                ]);
                
                DB::commit();
            } catch (\Throwable $th) {
                return errorHandler('Error Reclassifying Transactions', $th);
            }
            return new RedirectResponse(route('biller.settings.reclassify_transactions'), ['flash_success' => 'Transactions Reclassified Successfully']);
        }     

        /**Load Form */
        $classlists = Classlist::get();
        $projects = Project::whereHas('grn_items')->orWhereHas('purchase_items')->with('customer')->get();
        $customers = Customer::whereHas('transactions')->get(['id', 'company', 'name']);
        $suppliers = Supplier::whereHas('transactions')->get(['id', 'company', 'name']);
        $accountTypes = AccountType::orderBy('category', 'asc')->get();
        // exempt inventory accounts and journal entries
        $accounts = Account::whereHas('account_type_detail', fn($q) => $q->whereNotIn('system', ['inventory_asset']))
            ->with(['account_type_detail' => fn($q) => $q->select('id', 'system')])
            ->get(['id', 'number', 'holder', 'account_type', 'account_type_id', 'account_type_detail_id'])
            ->map(function($v) {
                $v['balance'] = 0;
                if ($v->transactions->count()) {
                    $debit = $v->transactions->sum('debit');
                    $credit = $v->transactions->sum('credit');
                    if (in_array($v->account_type, ['Asset', 'Expense'])) {
                        $v['balance'] = round($debit - $credit, 2);
                    } else {
                        $v['balance'] = round($credit - $debit, 2);
                    }
                }
                if (in_array($v->account_type, ['Income', 'Expense'])) {
                    $v['category'] = 'profit_and_loss';
                } else {
                    $v['category'] = 'balance_sheet';
                }
                return $v;
            });
            
        return view('focus.general.settings.reclassify_transactions', compact('projects', 'classlists', 'suppliers', 'customers', 'accountTypes', 'accounts'));        
    }

    public function theme(ManageCompanyRequest $request)
    {

        if ($request->post()) {
            $data = $this->repository->update_settings($request);
            return new RedirectResponse(route('biller.settings.theme'), ['flash_success' => trans('business.billing_settings_update')]);
        }

        $defaults = \App\Models\Company\ConfigMeta::get()->groupBy('feature_id');


        $data['additionals'] = \App\Models\additional\Additional::all();

        return view('focus.general.settings.theme', compact('data', 'defaults'));

    }

    public function status(ManageCompanyRequest $request)
    {   
        if ($request->post()) {
            $data = $this->repository->update_settings($request);
            return new RedirectResponse(route('biller.settings.status'), ['flash_success' => trans('business.billing_settings_update')]);
        }

        $defaults = \App\Models\Company\ConfigMeta::get()->groupBy('feature_id');
        $data['additionals'] = \App\Models\additional\Additional::all();
        $data['status'] = Misc::where('section', '=', 2)->get();
        
        return view('focus.general.settings.status', compact('data', 'defaults'));
    }

    /**
     * Opening Stock Resource
     * 
     */
    public function opening_stock(ManageCompanyRequest $request)
    {   
        // store or update opening stock
        if ($request->post()) {
            try {
                $input = $request->only('date', 'note', 'total');
                $input['date'] = date_for_database($input['date']);
                $input['total'] = numberClean($input['total']);
                $input_items = $request->only('qty', 'cost', 'amount', 'productvar_id', 'product_id', 'warehouse_id');
                foreach ($input_items as $key => $value) {
                    $input_items[$key] = explode(';', $value);
                }
    
                DB::beginTransaction();

                // reset opening stock
                if ($input['total'] == 0) {
                    $openingstock = OpeningStock::latest()->first();
                    if ($openingstock) {
                        $productvar_ids = $openingstock->items()->pluck('productvar_id')->toArray();
                        $account = $openingstock->account;
                        if ($account->gen_journal) {
                            $account->gen_journal->items()->delete();
                            $account->gen_journal->transactions()->delete();
                            $account->gen_journal->delete();
                        }
                        $openingstock->items()->delete();
                        // Update Stock Qty
                        updateStockQty($productvar_ids);
                        if ($openingstock->delete()) {
                            DB::commit();
                            return response()->json(['status' => 'Success', 'message' => 'Opening Stock Reset Successfully', 'refresh' => 1]);
                        }
                    }
                }

                // create opening stock
                $openingstock = OpeningStock::whereDate('date', $input['date'])->latest()->first();
                if ($openingstock) $openingstock->update($input);
                else $openingstock = OpeningStock::create($input);

                // opening stock items
                foreach ($input_items as $key => $value) {
                    if (in_array($key, ['qty', 'cost', 'amount']))
                        $input_items[$key] = array_map(fn($v) => numberClean($v), $value);
                }
                $input_items['opening_stock_id'] = array_fill(0, count($input_items['qty']), $openingstock->id);
                $input_items = modify_array($input_items);
                $input_items = array_filter($input_items, fn($v) => $v['qty'] > 0);
                $openingstock->items()->delete();
                OpeningStockItem::insert($input_items);
                
                /** accounting */
                $account = Account::whereHas('account_type_detail', fn($q) => $q->where('system', 'inventory_asset'))->first();
                if (!$account) return response()->json(['status' => 'Error', 'message' => 'Inventory Asset Account required!']);

                $openingstock->update(['account_id' => $account->id]);
                $account->opening_balance = $openingstock->total;
                $account->opening_balance_date = $openingstock->date;
                $account->note = $openingstock->note;
                $account->op_stock_id = $openingstock->id;
                $repository = new \App\Repositories\Focus\account\AccountRepository;
                $journal_data = $repository->opening_balance($account, 'update');
                $journal = new Journal;
                $journal->fill($journal_data);
                $journal->id = $journal_data['id'];
                $journal->refresh();
                $repository->post_ledger_opening_balance($journal);

                // Update Stock Qty
                $productvar_ids = $openingstock->items()->pluck('productvar_id')->toArray();
                // $productvar_ids = ProductVariation::pluck('id')->toArray();
                updateStockQty($productvar_ids);

                if ($openingstock) {
                    DB::commit();
                    return response()->json(['status' => 'Success', 'message' => 'Opening Stock Updated Successfully', 'refresh' => 1]);
                }
            } catch (\Throwable $th) {
                $msg = $th->getMessage() .' {user_id: '. auth()->user()->id . '}' . ' at ' . $th->getFile() . ':' . $th->getLine();
                \Illuminate\Support\Facades\Log::error($msg);

                $msg = 'Error Updating Opening Stock';
                if ($th instanceof \Illuminate\Validation\ValidationException) {
                    $firstError = $th->validator->errors()->first();
                    $msg .= ": {$firstError}";
                }
                return response()->json(['status' => 'Error', 'message' => $msg]);
            }
        }

        $warehouses = Warehouse::wherehas('products')->get(['id', 'title']);
        $openingstock = Openingstock::latest()->first();
        $product_vars = ProductVariation::whereHas('product', fn($q) => $q->whereIn('stock_type', ['general', 'consumable', 'equipment']))
            ->orderBy('warehouse_id', 'ASC')
            ->with(['openingstock_item' => fn($q) => $q->select('id', 'opening_stock_id', 'productvar_id', 'qty', 'cost')->where('opening_stock_id', @$openingstock->id ?: 0)])
            ->with(['warehouse' => fn($q) => $q->select('id', 'title')])
            ->with(['product.unit' => fn($q) => $q->select('id', 'code')])
            ->get(['id', 'parent_id', 'warehouse_id', 'code', 'name']);        
        
        return view('focus.general.settings.opening_stock', compact('product_vars', 'warehouses', 'openingstock'));
    }

    public function crm_hrm_section(ManageCompanyRequest $request)
    {
        if ($request->post()) {
            $data = $this->repository->update_settings($request);
            return new RedirectResponse(route('biller.settings.crm_hrm_section'), ['flash_success' => trans('business.billing_settings_update')]);
        }
        $defaults = \App\Models\Company\ConfigMeta::get()->groupBy('feature_id');
        return view('focus.general.settings.crm_hrm_section', compact('defaults'));
    }

    public function pos_preference(ManageCompanyRequest $request)
    {

        if ($request->post()) {
            $data = $this->repository->update_settings($request);
            return new RedirectResponse(route('biller.settings.pos_preference'), ['flash_success' => trans('business.billing_settings_update')]);
        }

        $defaults = feature(19);

        $conf = json_decode($defaults->value1, true);
        return view('focus.general.settings.pos_pref', compact('defaults', 'conf'));

    }

    public function currency_exchange(ManageCompanyRequest $request)
    {

        if ($request->post()) {
            $data = $this->repository->update_settings($request);
            return new RedirectResponse(route('biller.settings.currency_exchange'), ['flash_success' => trans('business.billing_settings_update')]);
        }

        $defaults = feature(2);

        $conf = json_decode($defaults->value2, true);
        if (single_ton()) $conf['readonly'] = ''; else $conf['readonly'] = 'readonly';
        return view('focus.general.settings.exchange', compact('conf'));

    }

    public function clear_cache()
    {
        try {
            if (single_ton()) {
                Artisan::call('config:clear');
                Artisan::call('cache:clear');
                Artisan::call('route:cache');
                Artisan::call('config:cache');
                return "Cache is cleared";
            }
        } catch (\Throwable $th) {
            //throw $th;
            return "Something went wrong!";
        }
    }

    public function site_down()
    {
        return Artisan::call('down');
    }

    public function dev_manager(ManageCompanyRequest $request)
    {


        if ($request->post()) {

            if ($request->post('dev_mode') == 1) {
                $this->setEnvFly('APP_DEBUG', 'false', 'true');
            } else {
                $this->setEnvFly('APP_DEBUG', 'true', 'false');
            }

            if ($request->post('create_link') == 1) {
               // Artisan::call('storage:link');
                symlink($request->post('from_path'),$request->post('to_path'));

            }
            return view('focus.general.dev');

        } else {
            return view('focus.general.dev');
        }


    }

    private function setEnvFly($env_var, $current_configKey, $new_val)
    {

        //'APP_DEBUG', 'app.debug', 'true'
        //env file path
        file_put_contents(App::environmentFilePath(), str_replace(
            $env_var . '=' . $current_configKey,
            $env_var . '=' . $new_val,
            file_get_contents(App::environmentFilePath())
        ));

        Config::set('app.' . strtolower($env_var), $new_val);


        if (file_exists(App::getCachedConfigPath())) {
            Artisan::call("config:clear");
        }
    }
}