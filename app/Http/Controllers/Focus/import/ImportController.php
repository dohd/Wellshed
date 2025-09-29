<?php

namespace App\Http\Controllers\Focus\import;

use App\Http\Requests\Focus\report\ManageReports;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Models\boq\BoQ;
use App\Models\boq\BoQSheet;
use App\Models\equipmentcategory\EquipmentCategory;
use App\Models\project\Budget;
use App\Models\quote\Quote;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * ImportController
 */
class ImportController extends Controller
{
    // temp upload
    private $upload_temp;

    public function __construct()
    {
        $this->upload_temp = Storage::disk('public');
    }

    /**
     * index page for import
     */
    public function index(ManageReports $request, $type)
    {
        $labels = [
            'customer' => trans('import.import_customers'),
            'supplier' => 'Import Suppliers',
            'prospect' => 'Import Prospects',
            'products' => trans('import.import_products'),
            'accounts' => trans('import.import_accounts'),
            'equipments' => 'Import Equipments',
            'client_pricelist' => 'Import Client Pricelist',
            'supplier_pricelist' => 'Import Supplier Pricelist',
            'invoices' => 'Import Invoices',
            'invoice_payments' => 'Import Invoice Payments',
            'casuals' => 'Import Casual Labourers',
            'boqs' => 'Import Bill of Materials',
            'material_take_off' => 'Import Material Take Off',
            'tasks' => 'Project Tasks',
        ];
        $data = ['title' => $labels[$type], 'type' => $type];

        if ($type == 'products') {
            $data['warehouses'] = \App\Models\warehouse\Warehouse::get(['id', 'title']);
            $data['product_categories'] = \App\Models\productcategory\Productcategory::get(['id', 'title']);
        } elseif ($type == 'invoices') {
            $data['accounts'] = \App\Models\account\Account::whereHas('accountType', fn($q) => $q->whereIn('name', ['Income', 'Other Income']))->get();
        } elseif ($type == 'invoice_payments') {
            $data['accounts'] = \App\Models\account\Account::whereHas('accountType', fn($q) => $q->where('system', 'bank'))->get(['id', 'holder']);
        } elseif ($type == 'supplier') {
            $local_acc = \App\Models\account\Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['payable', 'loan']))
            ->whereHas('currency', fn($q) => $q->where('rate', 1))
            ->first(['id', 'holder', 'currency_id']);
            $accounts = \App\Models\account\Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['payable', 'loan']))
                ->whereHas('currency', fn($q) => $q->where('rate', '>', 1))
                ->get(['id', 'holder', 'currency_id']);
            $payroll_accounts = \App\Models\account\Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['salaries_payable', 'payroll_taxes_payable', 'health_insurance_payable', 'retirement_contribution_payable', 'other_payroll_payable']))
                ->whereHas('currency', fn($q) => $q->where('rate', 1))
                ->get(['id', 'holder', 'currency_id']);    
            $data['accounts'] = collect(array_filter([$local_acc]))->merge($accounts)->merge($payroll_accounts);
        } elseif ($type == 'customer') {
            $local_acc = \App\Models\account\Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['receivable', 'loan']))
            ->whereHas('currency', fn($q) => $q->where('rate', 1))
            ->first(['id', 'holder']);
            $accounts = \App\Models\account\Account::whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['receivable', 'loan']))
                ->whereHas('currency', fn($q) => $q->where('rate', '>', 1))
                ->get(['id', 'holder']);
            $data['accounts'] = collect(array_filter([$local_acc]))->merge($accounts);
        } elseif ($type == 'boqs') {
            $data['boqs'] = BoQ::orderBy('id','desc')->get();
            $data['boq_sheets'] = BoQSheet::orderBy('id','desc')->get();
        } elseif ($type == 'material_take_off') {
            $data['quotes'] = Quote::whereDoesntHave('budget')->orderBy('id','desc')->get();
            $data['budgets'] = Budget::orderBy('id','desc')->get();
        } elseif ($type == 'tasks') {
            $data['project_id'] = request('project_id');
        }
        
        return new ViewResponse('focus.import.index', compact('data'));
    }

    /**
     * Download sample template
     */
    public function sample_template($file_name)
    {
        // Define the file path on the server
        $file_path = storage_path('app/public/sample/' . $file_name . '.csv');
        
        // Check if the file exists; if not, generate it
        if (!file_exists($file_path)) {
            if ($file_name == 'equipment_categories') {
                $categories = EquipmentCategory::all();
                
                // Open the file in write mode
                $fw = fopen($file_path, 'w');
                fputcsv($fw, ['equipment_category_id', 'name']);
                
                // Write data to the CSV file
                foreach ($categories as $row) {
                    fputcsv($fw, [$row->id, $row->name]);
                }
                
                // Close the file
                fclose($fw);
            } else {
                throw ValidationException::withMessages(['template' => 'Template file does not exist!']);
            }
        }
    
        // Return the CSV file as a response
        return response()->file($file_path, ['Content-Type' => 'text/csv']);
    }
    

    /**
     * Process and Store imported data
     */
    public function store(Request $request, $type)
    {
        $request->validate(['import_file' => 'required|max:' . config('master.file_size')]);

        $data = $request->except(['_token']) + compact('type');

        $extension = File::extension($request->import_file->getClientOriginalName());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) 
            throw ValidationException::withMessages(['File extension unsupported!']);

        $file = $request->file('import_file');
        $file_name = $file->getClientOriginalName();
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬]/', $file_name))
            throw ValidationException::withMessages(['Remove special characters from file name!']);

        $file_name = preg_replace('/\s+/', '', $file_name);
        $filename = date('Ymd_his') . rand(9999, 99999) . $file_name;

        // temp storage
        $path = 'temp' . DIRECTORY_SEPARATOR;
        $is_success = $this->upload_temp->put($path . $filename, file_get_contents($file->getRealPath()));
        if (!$is_success) throw ValidationException::withMessages(['Something went wrong try again later!']);

        // parse csv file
        $temp_file_path = Storage::disk('public')->path($path . $filename);
        $ext = pathinfo($temp_file_path, PATHINFO_EXTENSION);
        if (file_exists($temp_file_path) && $ext == 'csv') {
            // read csv to memory
            $i = 0;
            $csv_data = [];
            $uploaded_csv_file = fopen($temp_file_path, 'r+');
            while ($row = fgetcsv($uploaded_csv_file)) {
                foreach ($row as $key => $value) {
                    $csv_data[$i][$key] = $value; 
                }
                $i++;
            }
            fclose($uploaded_csv_file);
            // update memory data to csv file
            fclose(fopen($temp_file_path,'w'));
            $uploaded_csv_file = fopen($temp_file_path, 'r+');
            foreach ($csv_data as $i => $row) {
                fputcsv($uploaded_csv_file, $row); 
            }
            fclose($uploaded_csv_file);
        }

        // process file
        $data['ins'] = auth()->user()->ins;
        $models = [
            'customer' => new \App\Imports\CustomersImport($data),
            'supplier' => new \App\Imports\SuppliersImport($data),
            'products' => new \App\Imports\ProductsImport($data),
            'prospect' => new \App\Imports\ProspectsImport($data),
            'accounts' => new \App\Imports\AccountsImport($data),
            'equipments' => new \App\Imports\EquipmentsImport($data),
            'client_pricelist' => new \App\Imports\ClientPricelistImport($data),
            'supplier_pricelist' => new \App\Imports\SupplierPricelistImport($data),
            'invoices' => new \App\Imports\InvoicesImport($data),
            'invoice_payments' => new \App\Imports\InvoicePaymentsImport($data),
            'casuals' => new \App\Imports\CasualsImport($data),
            'boqs' => new \App\Imports\BoQsImport($data),
            'material_take_off' => new \App\Imports\MaterialTakeOffsImport($data),
            'tasks' => new \App\Imports\TasksImport($data),
        ];

        $file_path = $path . $filename;
        $model = $models[$data['type']];

        DB::beginTransaction();
        
        try {
            // set maximum php execution time
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            Excel::import($model, Storage::disk('public')->path($file_path));

            $row_count = $model->getRowCount();
            if (!$row_count) throw ValidationException::withMessages(["Unexpected failure. Please check duplicate entries and try again"]);
            Storage::disk('public')->delete($file_path);

            DB::commit();
            return redirect()->back()->with('flash_success', " {$row_count} rows imported successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::disk('public')->delete($file_path);
            return errorHandler(trans('import.import_process_failed'), $e);
        }
    }    
}
