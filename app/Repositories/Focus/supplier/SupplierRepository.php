<?php

namespace App\Repositories\Focus\supplier;

use DB;
use App\Models\supplier\Supplier;
use App\Exceptions\GeneralException;
use App\Http\Controllers\ClientSupplierAuth;
use App\Models\Company\Company;
use App\Models\manualjournal\Journal;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use App\Repositories\CustomerSupplierBalance;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Class SupplierRepository.
 */
class SupplierRepository extends BaseRepository
{
    use Accounting, SupplierStatement, ClientSupplierAuth, CustomerSupplierBalance;
    
    /**
     *customer_picture_path .
     *
     * @var string
     */
    protected $person_picture_path;
    /**
     * Storage Class Object.
     *
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;
    /**
     * Associated Repository Model.
     */
    const MODEL = Supplier::class;
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->person_picture_path = 'img' . DIRECTORY_SEPARATOR . 'supplier' . DIRECTORY_SEPARATOR;
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
        $q = $this->query()->withoutGlobalScopes(['currency_id']);
        
        $q->orderByRaw("CASE WHEN LOWER(name) = LOWER(?) THEN 1 ELSE 2 END", ['Walk-In'])->orderByRaw('LOWER(name) ASC');
            
        // Supplier user login filter
        $q->when(auth()->user()->supplier_id, fn($q) => $q->where('id', auth()->user()->supplier_id));

        return $q->get();
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
        $data = $input['data'];
        if (isset($data['picture'])) $data['picture'] = $this->uploadPicture($data['picture']);

        if (@$data['taxid']) {
            $taxid_exists = Supplier::where('taxid', $data['taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists) throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $data['taxid']])->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin not allowed!']);

            $taxid = $data['taxid'];
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

        $data = array_merge($data, $input['payment_data']);
        $data['credit_limit'] = numberClean($data['credit_limit']);
        $account_data = $input['account_data'];
        $data['open_balance'] = numberClean($account_data['open_balance']);
        $data['open_balance_date'] = date_for_database($account_data['open_balance_date']);

        // create supplier
        DB::beginTransaction();
        $supplier = Supplier::create($data);

        // opening balance
        if ($supplier->open_balance > 0) {
            $tr_data = $this->supplier_opening_balance($supplier, 'create'); 
            $journal = new Journal($tr_data);
            $journal->id = $tr_data['id'];
            $this->post_supplier_opening_balance($journal); 
        }
        // supplier authorize
        $this->createAuth($supplier, $input['user_data'], 'supplier');

        if ($supplier) {
            DB::commit();
            return $supplier;
        }
    }


    /**
     * For updating the respective Model in storage
     *
     * @param Supplier $supplier
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($supplier, array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];
        if (isset($data['picture'])) {
            $this->removePicture($supplier, 'picture');
            $data['picture'] = $this->uploadPicture($data['picture']);
        }

        if (@$data['taxid']) {
            $taxid_exists = Supplier::where('id', '!=', $supplier->id)->where('taxid', $data['taxid'])->whereNotNull('taxid')->exists();
            if ($taxid_exists) throw ValidationException::withMessages(['Duplicate Tax Pin']);
            $is_company = Company::where(['id' => auth()->user()->ins, 'taxid' => $data['taxid']])->exists();
            if ($is_company) throw ValidationException::withMessages(['Company Tax Pin is not allowed!']);
            // if (strlen($data['taxid']) != 11) 
            //     throw ValidationException::withMessages(['Supplier Tax Pin should contain 11 characters!']);
            // if (!in_array($data['taxid'][0], ['P','A'])) 
            //     throw ValidationException::withMessages(['Initial character of Tax Pin must be letter "P" or "A"']);
            // $pattern = "/^[0-9]+$/i";
            // if (!preg_match($pattern, substr($data['taxid'],1,9))) 
            //     throw ValidationException::withMessages(['Character between first and last letters must be numbers']);
            // $letter_pattern = "/^[a-zA-Z]+$/i";
            // if (!preg_match($letter_pattern, $data['taxid'][-1])) 
            //     throw ValidationException::withMessages(['Last character of Tax Pin must be a letter!']);
            $taxid = $data['taxid'];
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

        $account_data = $input['account_data'];
        $data = array_replace($data, [
            'open_balance' => numberClean($account_data['open_balance']),
            'open_balance_date' => date_for_database($account_data['open_balance_date']),
            'open_balance_note' => $account_data['open_balance_note'],
        ]);
        if ($data['open_balance'] == 0) $data['open_balance_date'] = null;
        $data = array_merge($data, $input['payment_data']);
        $data['credit_limit'] = numberClean($data['credit_limit']);
        // dd($data);
        $result = $supplier->update($data);

        /**accounting */   
        if ($supplier->open_balance > 0) {
            $tr_data = $this->supplier_opening_balance($supplier, 'update'); 
            $journal = new Journal($tr_data);
            $journal->id = $tr_data['id'];
            $this->post_supplier_opening_balance($journal); 
        } else {
            $journal = @$supplier->journal;
            $bill = @$journal->bill;
            if ($bill) {
                if ($bill->payments()->exists()) {
                    foreach ($bill->payments as $key => $item) {
                        $tids[] = @$item->bill_payment->tid ?: '';
                    }
                    throw ValidationException::withMessages(['Supplier has attached Payments with Nos.: ('.implode(', ', $tids).')']);                 
                }
                $bill->delete();
            }
            if ($journal) {
                $journal->transactions()->delete();
                $journal->delete();
            }
        }

        // supplier authorization
        $this->updateAuth($supplier, $input['user_data'], 'supplier');

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Supplier $supplier
     * @return bool
     * @throws GeneralException
     */
    public function delete($supplier)
    {
        if ($supplier->id == 1) throw ValidationException::withMessages(['Cannot delete default supplier']);
        if ($supplier->bills()->exists()) throw ValidationException::withMessages(['Supplier has attached bills']);
        if ($supplier->purchase()->exists()) throw ValidationException::withMessages(['Supplier has attached Purchase']);
        if ($supplier->transactions()->exists()) throw ValidationException::withMessages(['Supplier has attached Transactions']);
        if ($supplier->payments()->exists()) throw ValidationException::withMessages(['Supplier has attached payments']);
        if ($supplier->purchase_orders()->exists()) throw ValidationException::withMessages(['Supplier has attached purchase orders!']);

        DB::beginTransaction();
        $this->deleteAuth($supplier, 'supplier');
        $result = $supplier->delete();
        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /*
    * Upload logo image
    */
    public function uploadPicture($logo)
    {
        $image_name = $this->person_picture_path . time() . $logo->getClientOriginalName();
        $this->storage->put($image_name, file_get_contents($logo->getRealPath()));
        return $image_name;
    }

    /*
    * remove logo or favicon icon
    */
    public function removePicture(Supplier $supplier, $type)
    {
        if ($supplier->$type) {
            $image = $this->person_picture_path . $supplier->type;
            if ($this->storage->exists($image)) $this->storage->delete($image);
        }
        if ($supplier->update([$type => null])) return true;
    }
}
