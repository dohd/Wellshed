<?php

namespace App\Http\Controllers\Focus\stockIssuance;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\Access\User\User;
use App\Models\hrm\Hrm;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\stock_issuance\StockIssuanceApproval;
use App\Models\stock_issuance\StockIssuanceRequest;
use App\Models\stock_issuance\StockIssuanceRequestItems;
use App\Models\warehouse\Warehouse;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StockIssuanceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return new ViewResponse('focus.stockIssuanceRequest.index');
    }

    public function getSirDataTable(Request $request)
    {

        $sirs = StockIssuanceRequest::join('users', 'stock_issuance_requests.requested_by', 'users.id')
            ->leftJoin('projects', 'stock_issuance_requests.project', 'projects.id')
            ->select(
                'sir_number',
                DB::raw('CONCAT(first_name, " ", last_name) as requested_by'),
                'projects.name as project',
                'stock_issuance_requests.status as status',
                'stock_issuance_requests.date as date',
            )
            ->orderBy('date');


        return Datatables::of($sirs->get())
            ->addColumn('action', function ($model) {

                $view = ' <a href="' . route('biller.stock-issuance-request.show',$model->sir_number) . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i  class="fa fa-eye"></i></a> ';

                $edit = ' <a href="' . route('biller.stock-issuance-request.edit', $model->sir_number) . '" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-pencil"></i></a> ';

                $delete = '<a href="' . route('biller.stock-issuance-request.destroy', $model->sir_number) . '"
                            class="btn btn-danger round" data-method="delete"
                            data-trans-button-cancel="' . trans('buttons.general.cancel') . '"
                            data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '"
                            data-trans-title="' . trans('strings.backend.general.are_you_sure') . '"
                            data-toggle="tooltip"
                            data-placement="top"
                            title="Delete"
                            >
                                <i  class="fa fa-trash"></i>
                            </a>';

                return $view . $edit . $delete;
            })
            ->rawColumns(['action'])
            ->make(true);

    }

    private function getEmployeesAndProducts(): array
    {

        $employees = Hrm::
        join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->select(
                'users.id as id',
                DB::raw('CONCAT(first_name, " ", last_name) AS employee_name'),
                'roles.name as role'
            )->get();

//        $products = ProductVariation::all();

        $products = ProductVariation::select(
            'id',
            'name',
            'warehouse_id as warehouse',
            'productcategory_id as category',
            'code',
        )
            ->get();


        foreach ($products as $pItem) {

            $wH = Warehouse::find($pItem['warehouse']);
            $cat = Productcategory::find($pItem['category']);

            $warehouse = 'N/A';
            $category = 'N/A';

            if (!empty($wh)) $warehouse = $wH->name;
            if (!empty($cat)) $category = $cat->title;

            $pItem['warehouse'] = $warehouse;
            $pItem['category'] = $category;
        }


        return compact('employees', 'products');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return ViewResponse
     */
    public function create()
    {

        $employees = ($this->getEmployeesAndProducts())['employees'];
        $products = ($this->getEmployeesAndProducts())['products'];

        return new ViewResponse('focus.stockIssuanceRequest.create', compact('employees', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'requested_by' => ['required', 'integer'],
            'project' => ['required', 'integer'],
            'products_list' => ['required', 'json'],
            'notes' => ['required', 'string'],
        ]);

//        return json_decode($request->products_list);

        try {
            DB::beginTransaction();

            $stockIssuanceRequest = new StockIssuanceRequest();

            $stockIssuanceRequest->sir_number = uniqid('SIR-');
            $stockIssuanceRequest->fill($validated);
            $stockIssuanceRequest->status = 'Pending';
            $stockIssuanceRequest->date = (new DateTime('now'))->format('Y-m-d H:i:s');

            $stockIssuanceRequest->save();

            $sirItemsArray = json_decode($validated['products_list']);
            foreach ($sirItemsArray as $item){

                $sirItem = new StockIssuanceRequestItems();

                $sirItem->siri_number = uniqid('SIRI-');
                $sirItem->sir_number = $stockIssuanceRequest->sir_number;
                $sirItem->product = $item->id;
                $sirItem->quantity = $item->quantity;

                $sirItem->save();
            }


            DB::commit();

        } catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'ERROR : ' . $e->getMessage());
        }


        return new RedirectResponse(route('biller.stock-issuance-request.index'), ['flash_success' => 'Stock Issuance Request Saved Successfully!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($sirNumber)
    {

        $sir = StockIssuanceRequest::find($sirNumber)
            ->join('users', 'stock_issuance_requests.requested_by', 'users.id')
            ->leftJoin('projects', 'stock_issuance_requests.project', 'projects.id')
            ->select(
                'sir_number',
                DB::raw('CONCAT(first_name, " ", last_name) as requested_by'),
                'projects.name as project',
                'stock_issuance_requests.status as status',
                'notes',
                'stock_issuance_requests.date as date',
            )
            ->first();

        $sirItems = StockIssuanceRequestItems::where('sir_number', $sir->sir_number)
            ->join('product_variations', 'stock_issuance_request_items.product', 'product_variations.id')
            ->leftJoin('product_categories', 'product_variations.productcategory_id', 'product_categories.id')
            ->leftJoin('warehouses', 'product_variations.warehouse_id', 'warehouses.id')
            ->select(
                'siri_number',
                'product_variations.name as name',
                'code',
                'product_categories.title as category',
                'warehouses.title as warehouse',
                'quantity'
            )
            ->get();

        return new ViewResponse('focus.stockIssuanceRequest.show', compact('sir', 'sirItems'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return ViewResponse
     */
    public function edit($sirNumber)
    {

        $sir = StockIssuanceRequest::find($sirNumber);

        $sia = StockIssuanceApproval::where('sir_number', $sirNumber)->get();
        if (!empty($sia)){
            return new RedirectResponse(route('biller.stock-issuance-request.index'), ['flash_error' => 'Edit Denied! Stock Issuance is Already Approved...']);
        }

//        $sirItems = $sir->sirItems;

        $sirItems = StockIssuanceRequestItems::where('sir_number', $sir->sir_number)
            ->join('product_variations', 'stock_issuance_request_items.product', 'product_variations.id')
            ->leftJoin('product_categories', 'product_variations.productcategory_id', 'product_categories.id')
            ->leftJoin('warehouses', 'product_variations.warehouse_id', 'warehouses.id')
            ->select(
                'siri_number',
                'product_variations.name as name',
                'code',
                'product_categories.title as category',
                'warehouses.title as warehouse',
                'quantity'
            )
            ->get();

        $employees = ($this->getEmployeesAndProducts())['employees'];
        $products = ($this->getEmployeesAndProducts())['products'];

//        return compact('sir', 'sirItems', 'employees', 'products');

        return new ViewResponse('focus.stockIssuanceRequest.edit', compact('sir', 'sirItems', 'employees', 'products'));
    }


    /**
     * Fetches the values of a key in a nested array
     * @param $array
     * @param $keyToFind
     * @return array
     */
    public function getValuesByKey($array, $keyToFind) {

        return array_merge(...array_map(function ($item) use ($keyToFind) {
            return isset($item[$keyToFind]) ? [$item[$keyToFind]] : [];
        }, $array));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $sirNumber)
    {
        $validated = $request->validate([
            'requested_by' => ['required', 'integer'],
            'project' => ['required', 'integer'],
            'notes' => ['required', 'string'],
            'products_list' => ['nullable', 'json'],
            'sir_items' => ['required', 'json'],
        ]);


        try {
            DB::beginTransaction();

            $sirRequest = StockIssuanceRequest::find($sirNumber);
            $sirRequest->fill($validated);

            $sirRequest->save();

            /** Updating and delesing SIR Items */
            $sirItems = json_decode($validated['sir_items'], true);

//            return $this->getValuesByKey($sirItems, 'siri_number');
//            return $removedItems = StockIssuanceRequestItems::where('sir_number', $sirNumber)
//                ->whereNotIn('siri_number', $this->getValuesByKey($sirItems, 'siri_number'))->get();

            foreach ($sirItems as $item){

                $item = json_decode(json_encode($item), true);

                $siri = StockIssuanceRequestItems::find($item['siri_number']);
                $siri->fill($item);
                $siri->save();
            }

            $removedItems = StockIssuanceRequestItems::where('sir_number', $sirNumber)
                ->whereNotIn('siri_number', $this->getValuesByKey($sirItems, 'siri_number'))
                ->get();
            foreach ($removedItems as $remItem){

                $remItem->delete();
            }


            /** Adding New Items */
            $sirItemsArray = json_decode($validated['products_list']);
            foreach ($sirItemsArray as $item){

                $sirItem = new StockIssuanceRequestItems();

                $sirItem->siri_number = uniqid('SIRI-');
                $sirItem->sir_number = $sirRequest->sir_number;
                $sirItem->product = $item->id;
                $sirItem->quantity = $item->quantity;

                $sirItem->save();
            }


            DB::commit();

        } catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'ERROR : ' . $e->getMessage());
        }




        $its = $sirRequest->sirItems;

        return new RedirectResponse(route('biller.stock-issuance-request.index'), ['flash_success' => 'Stock Issuance Updated Successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($sirNumber)
    {
        try {
            DB::beginTransaction();

            $sir = StockIssuanceRequest::find($sirNumber);

            $sirItemsNumbers = $this->getValuesByKey(($sir->sirItems)->toArray(), 'siri_number');
            $sirItems = StockIssuanceRequestItems::where('sir_number', $sir->sir_number)
                ->whereIn('siri_number', $sirItemsNumbers)
                ->get();
            foreach ($sirItems as $item){
                $item->delete();
            }

            $sir->delete();

            DB::commit();

        } catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'ERROR : ' . $e->getMessage());
        }

        return new RedirectResponse(route('biller.stock-issuance-request.index'), ['flash_success' => 'Stock Issuance Request Deleted Successfully!']);
    }
}
