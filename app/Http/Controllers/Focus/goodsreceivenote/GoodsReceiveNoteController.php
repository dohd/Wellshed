<?php

namespace App\Http\Controllers\Focus\goodsreceivenote;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\additional\Additional;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\PurchaseorderItem;
use App\Models\product\ProductVariation;
use App\Models\supplier\Supplier;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\goodsreceivenote\GoodsreceivenoteRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class GoodsReceiveNoteController extends Controller
{
    /**
     * Store repository object
     * @var \App\Repositories\Focus\goodsreceivenote\GoodsreceivenoteRepository
     */
    public $repository;

    public function __construct(GoodsreceivenoteRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $supplier_id = auth()->user()->supplier_id;
        $suppliers = Supplier::when($supplier_id, fn($q) => $q->where('id', $supplier_id))
        ->whereHas('goods_receive_notes')
        ->get(['id', 'name']);

        $grns = Goodsreceivenote::whereIn('id', explode(',', '191,192,324,201,201'))->get();
        foreach ($grns as $key => $goodsreceivenote) {
            // if ($goodsreceivenote->invoice_no) {
            //     $bill = $this->repository->generate_bill($goodsreceivenote); 
            //     $this->repository->post_invoiced_grn_bill($bill);
            // } else {
            //     $goodsreceivenote->transactions()->delete();
            //     $this->repository->post_uninvoiced_grn($goodsreceivenote);
            // }
        }

        return view('focus.goodsreceivenotes.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = Goodsreceivenote::where('ins', auth()->user()->ins)->max('tid');
        $suppliers = Supplier::with(['currency' => fn($q) => $q->select('id', 'code', 'rate')])->get(['id', 'company', 'name', 'email', 'currency_id']);
        $additionals = Additional::get();
        $warehouses = Warehouse::all();

        return view('focus.goodsreceivenotes.create', compact('tid', 'suppliers', 'additionals', 'warehouses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (@$request['invoice_no'] && empty($request['invoice_date'])) {
            throw ValidationException::withMessages(['invoice_date' => 'Invoice date is required.']);
        }
        if (@$request['tax_rate'] > 1) {
            $supplier = Supplier::find($request['supplier_id']);
            if (!isset($supplier->taxid)) throw ValidationException::withMessages(['tax_rate' => 'Update Supplier Tax Pin']);
        }

        try {
            $grn = $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Goods Received Note'.$th->getMessage(), $th);
        }

        return new RedirectResponse(route('biller.goodsreceivenote.index'), ['flash_success' => $grn->invoice_no? 'Goods Received Note Created Successfully With Invoice' : 'Goods Received Note Created Successfully With DNote']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @return \Illuminate\Http\Response
     */
    public function show(Goodsreceivenote $goodsreceivenote)
    {
        return view('focus.goodsreceivenotes.view', compact('goodsreceivenote'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @return \Illuminate\Http\Response
     */
    public function edit(Goodsreceivenote $goodsreceivenote)
    {
        $suppliers = Supplier::with(['currency' => fn($q) => $q->select('id', 'code', 'rate')])->get(['id', 'company', 'name', 'email', 'currency_id']);
        $additionals = Additional::get();
        $warehouses = Warehouse::all();

        return view('focus.goodsreceivenotes.edit', compact('goodsreceivenote', 'suppliers', 'additionals', 'warehouses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Goodsreceivenote $goodsreceivenote)
    {
        try {
            $this->repository->update($goodsreceivenote, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Goods Received Note', $th);
        }

        return new RedirectResponse(route('biller.goodsreceivenote.index'), ['flash_success' => 'Goods Received Note Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\goodsreceivenote\Goodsreceivenote $goodsreceivenote
     * @return \Illuminate\Http\Response
     */
    public function destroy(Goodsreceivenote $goodsreceivenote)
    {
        try {
            $this->repository->delete($goodsreceivenote);
        } catch (\Throwable $th) { 
            return errorHandler('Error Deleting Goods Received Note', $th);
        }
        return new RedirectResponse(route('biller.goodsreceivenote.index'), ['flash_success' => 'Goods Received Note Deleted Successfully']);
    }

    /** Gets a list of all items received by grn from suppliers */
    public function getGrnItemsBySupplier(int $supplierId){
        $supplierGrnList = Goodsreceivenote::where('supplier_id', $supplierId)->get();
        $grnItemsList = [];
        foreach ($supplierGrnList as $grn){
            $grnItems = $grn->items;
            $itemDetailsList = [];
            foreach ($grnItems as $item){
                $poItem = PurchaseorderItem::where('id', $item->purchaseorder_item_id)
                    ->select(
                        'id',
                        'product_code',
                        'description',
                        'qty',
                        'amount',
                    )
                    ->first();
                array_push($itemDetailsList, $poItem);
            }
            $grnItemsList = array_merge($grnItemsList, $itemDetailsList);
        }
        $productCodesList = [];
        foreach ($grnItemsList as $grnItem){
            if(!empty($grnItem['product_code'])) array_push($productCodesList, $grnItem['product_code']);
        }
        $productCodesList = array_values(array_unique($productCodesList));
        $productCodesMetrics = [];
        foreach ($productCodesList as $pcl){
            $template = [
                "name" => '',
                "quantity" => 0,
                "value" => 0,
            ];
            array_push($productCodesMetrics, $template);
        }
        foreach ($grnItemsList as $grnItem) {
            if(!empty($grnItem['product_code'])) {
                foreach ($productCodesList as $index => $code) {
                    if ($grnItem['product_code'] === $code) {
                        $productCodesMetrics[$index]['quantity'] += $grnItem['qty'];
                        $productCodesMetrics[$index]['value'] += $grnItem['amount'];
                    }
                }
            }
        }
        $grnItemsBySupplier =  array_combine($productCodesList, $productCodesMetrics);
        foreach ($grnItemsBySupplier as $key => $pcm){
            $productVariation = ProductVariation::where('code', $key)->first();
              if (!empty($productVariation)) $grnItemsBySupplier[$key]['name'] = $productVariation->name;
              else $grnItemsBySupplier[$key]['name'] = 'Product Not Found';
        }
        return $grnItemsBySupplier;
    }

    /**Optimized version of V1*/
    public function getGrnItemsBySupplierV2(Request $request)
    {
        $supplierGrnList = Goodsreceivenote::with('items.purchaseorder_item')
            ->when(!empty($request->month) && !empty($request->year), function ($query) use ($request) {
                $query->whereMonth('date', $request->month)
                    ->whereYear('date', $request->year);
            })
            ->when(!empty($request->month) && empty($request->year), function ($query) use ($request) {
                $query->whereMonth('date', $request->month);
            })
            ->when(empty($request->month) && !empty($request->year), function ($query) use ($request) {
                $query->whereYear('date', $request->year);
            })
            ->when(empty($request->month) && empty($request->year), function ($query) {
                // No additional conditions when both month and year are empty
            })
            ->where('supplier_id', $request->supplierId)
            ->get();

        $productCodesMetrics = [];
        foreach ($supplierGrnList as $grn) {
            foreach ($grn->items as $item) {
                $poItem = $item->purchaseorder_item;
                if ($poItem) {
                    $productCode = $poItem->product_code;
                    if (!isset($productCodesMetrics[$productCode])) {
                        $productCodesMetrics[$productCode] = [
                            'code' => $productCode,
                            'name' => '',
                            'uom' => $poItem->uom,
                            'quantity' => 0,
                            'value' => 0,
                        ];
                    }
                    $productCodesMetrics[$productCode]['quantity'] += $item->qty;
                    $productCodesMetrics[$productCode]['value'] += $poItem->amount;
                }
            }
        }
        $productCodesList = array_keys($productCodesMetrics);
        $productsList = array_map(function ($productCode) use ($productCodesMetrics) {
            $productVariation = ProductVariation::where('code', $productCode)->first();
            $productName = !empty($productVariation) ? $productVariation->name : 'Product Not Found';
            return [
                'code' => $productCodesMetrics[$productCode]['code'],
                'name' => $productName,
                'uom' => $productCodesMetrics[$productCode]['uom'],
                'quantity' => $productCodesMetrics[$productCode]['quantity'],
                'value' => numberFormat(sprintf('%0.2f', $productCodesMetrics[$productCode]['value'])) ,
            ];
        }, $productCodesList);
        //        return $productsList;
        return Datatables::of($productsList)->make(true);
        //        return array_combine($productCodesList, $productsList);
    }
    //    public function getGrnOrdersByMonth()
}
