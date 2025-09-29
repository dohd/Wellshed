<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */
namespace App\Http\Controllers\Focus\boq;

use App\Models\boq\BoQ;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\boq\CreateResponse;
use App\Http\Responses\Focus\boq\EditResponse;
use App\Models\additional\Additional;
use App\Models\boq\BoQItem;
use App\Models\boq\BoQSheet;
use App\Models\boq_valuation\BoQValuationItem;
use App\Repositories\Focus\boq\BoQRepository;


/**
 * BoQsController
 */
class BoQsController extends Controller
{
    /**
     * variable to store the repository object
     * @var BoQRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param BoQRepository $repository ;
     */
    public function __construct(BoQRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\boq\ManageboqRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.boqs.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateboqRequestNamespace $request
     * @return \App\Http\Responses\Focus\boq\CreateResponse
     */
    public function create()
    {
        return new CreateResponse('focus.boqs.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreboqRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->only([
            'name'
        ]);
        $data_items = $request->except(['_token', 'ins','name','product_name','id']);
        $data_items = modify_array($data_items);
        try {
            //Create the model using repository create method
            $this->repository->create(compact('data','data_items'));
        } catch (\Throwable $th) {
            //throw $th;
            return errorHandler('Error Creating BoQ '.$th->getMessage(), $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.boqs.index'), ['flash_success' => 'BoQ Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\boq\boq $boq
     * @param EditboqRequestNamespace $request
     * @return \App\Http\Responses\Focus\boq\EditResponse
     */
    public function edit(BoQ $boq)
    {
        return new EditResponse($boq);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateboqRequestNamespace $request
     * @param App\Models\boq\boq $boq
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, BoQ $boq)
    {
        // dd(json_decode($request->getContent(), true));
        $dataInput = $request->input('data', []);
        // dd($dataInput);

        $numbering = [];
        $description = [];
        $misc = [];
        $productId = [];
        $unitId = [];
        $uom = [];
        $unit = [];
        $productName = [];
        $qty = [];
        $boqRate = [];
        $boqAmount = [];
        $rate = [];
        $newQty = [];
        $amount = [];
        $rowIndex = [];
        $type = [];
        $id = [];
        $product_subtotal = [];
        $tax_rate = [];

        $datas = [];
        $boq_sheet_id = 0;
        foreach($dataInput as $input){
            if ($input['name'] == 'name') {
                $datas['name'] = $input['value'];
            }
            
            if ($input['name'] == 'tax') {
                $datas['tax'] = $input['value'];
            }
            
            if ($input['name'] == 'taxable') {
                $datas['taxable'] = $input['value'];
            }
            
            if ($input['name'] == 'subtotal') {
                $datas['subtotal'] = $input['value'];
            }
            
            if ($input['name'] == 'total') {
                $datas['total'] = $input['value'];
            }
            
            if ($input['name'] == 'vat_type') {
                $datas['vat_type'] = $input['value'];
            }
            if ($input['name'] == 'total_boq_amount') {
                $datas['total_boq_amount'] = $input['value'];
            }
            if ($input['name'] == 'total_boq_vat') {
                $datas['total_boq_vat'] = $input['value'];
            }
            
            if ($input['name'] == 'boq_tax') {
                $datas['boq_tax'] = $input['value'];
            }
            
            if ($input['name'] == 'boq_taxable') {
                $datas['boq_taxable'] = $input['value'];
            }
            
            if ($input['name'] == 'boq_subtotal') {
                $datas['boq_subtotal'] = $input['value'];
            }
            
            if ($input['name'] == 'boq_total') {
                $datas['boq_total'] = $input['value'];
            }
            
            if ($input['name'] == 'lead_id') {
                $datas['lead_id'] = $input['value'];
            }
            if ($input['name'] == 'boq_sheet_id') {
                $boq_sheet_id = $input['value'];
            }
            
        }
    
        // Group the input data
        foreach ($dataInput as $item) {
            switch ($item['name']) {
                case 'numbering[]':
                    $numbering[] = $item['value'];
                    break;
                case 'description[]':
                    $description[] = $item['value'];
                    break;
                case 'misc[]':
                    $misc[] = $item['value'];
                    break;
                case 'product_id[]':
                    $productId[] = $item['value'];
                    break;
                case 'unit_id[]':
                    $unitId[] = $item['value'];
                    break;
                case 'uom[]':
                    $uom[] = $item['value'];
                    break;
                case 'unit[]':
                    $unit[] = $item['value'];
                    break;
                case 'product_name[]':
                    $productName[] = $item['value'];
                    break;
                case 'qty[]':
                    $qty[] = $item['value'];
                    break;
                case 'boq_rate[]':
                    $boqRate[] = $item['value'];
                    break;
                case 'boq_amount[]':
                    $boqAmount[] = $item['value'];
                    break;
                case 'rate[]':
                    $rate[] = $item['value'];
                    break;
                case 'new_qty[]':
                    $newQty[] = $item['value'];
                    break;
                case 'amount[]':
                    $amount[] = $item['value'];
                    break;
                case 'row_index[]':
                    $rowIndex[] = $item['value'];
                    break;
                case 'type[]':
                    $type[] = $item['value'];
                    break;
                case 'id[]':
                    $id[] = $item['value'];
                    break;
                case 'product_subtotal[]':
                    $product_subtotal[] = $item['value'];
                    break;
                case 'tax_rate[]':
                    $tax_rate[] = $item['value'];
                    break;
            }
        }

        $rowData = [];
        // Ensure all arrays have the same length
        $itemCount = count($numbering); // Assume all arrays are the same length
        // dd($itemCount);
        for ($i = 0; $i < $itemCount; $i++) {
            // Prepare data for each row
            $rowData[] = [
                'numbering' => $numbering[$i] ?? null,
                'description' => $description[$i] ?? null,
                'misc' => $misc[$i] ?? '0',
                'product_id' => $productId[$i] ?? '0',
                'unit_id' => $unitId[$i] ?? '0',
                'uom' => $uom[$i] ?? null,
                'unit' => $unit[$i] ?? null,
                'product_name' => $productName[$i] ?? null,
                'qty' => $qty[$i] ?? '0',
                'boq_rate' => $boqRate[$i] ?? '0',
                'boq_amount' => $boqAmount[$i] ?? '0.0000',
                'rate' => $rate[$i] ?? '0',
                'new_qty' => $newQty[$i] ?? '0',
                'amount' => $amount[$i] ?? '0.0000',
                'row_index' => $rowIndex[$i] ?? '0',
                'type' => $type[$i] ?? 'title',
                'id' => $id[$i] ?? null,
                'boq_sheet_id' => $boq_sheet_id ?? null,
                'product_subtotal' => $product_subtotal[$i] ?? 0,
                'tax_rate' => $tax_rate[$i] ?? 0
            ];
        }
        // dd($dataInput, $rowData);
        $data = $datas;
        $data_items = $rowData;
        try {
            //Update the model using repository update method
            $this->repository->update($boq, compact('data', 'data_items','boq_sheet_id'));
        } catch (\Throwable $th) {
            //throw $th;
            errorHandler('Error updating Boq '.$th->getMessage(), $th);
            session()->flash('success', 'Error updating Boq '.$th->getMessage());
            return response()->json(['success' => 'Error updating Boq '.$th->getMessage()]);
        }
        session()->flash('success', 'BoQ updated successfully!');
        return response()->json(['success' => 'BoQ updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteboqRequestNamespace $request
     * @param App\Models\boq\boq $boq
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(BoQ $boq)
    {
        //Calling the delete method on repository
        $this->repository->delete($boq);
        //returning with successfull message
        return new RedirectResponse(route('biller.boqs.index'), ['flash_success' => 'BoQ Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteboqRequestNamespace $request
     * @param App\Models\boq\boq $boq
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(BoQ $boq)
    {

        //returning with successfull message
        $additionals = Additional::all();
        return new ViewResponse('focus.boqs.view', compact('boq','additionals'));
    }

    public function generate_bom($boq_id)
    {
        try {
            $boq = BoQ::find($boq_id);
            $this->repository->generate_bom(compact('boq'));
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th->getMessage());
            return errorHandler("Error generating BOM ".$th->getLine(), $th);
        }
        return new RedirectResponse(route('biller.boqs.index'), ['flash_success' => 'BoM Created Successfully']);
    }

    public function store_boq_sheet(Request $request)
    {
        $data = $request->only(['sheet_name','description']);
        try {
            BoQSheet::create($data);
        } catch (\Throwable $th) {dd($th);
            //throw $th;
            return errorHandler('Error Creating BoQ Sheet', $th);
        }
        return back()->with('flash_success','BoQ Sheet Created Successfully!!');
    }

    public function get_boq_items(Request $request)
    {
        $boq_items = BoQItem::where([
            'boq_id' => $request->boq_id,
            'boq_sheet_id' => $request->boq_sheet_id,
        ])->with('product')->orderBy('row_index', 'ASC')->get();
        return response()->json($boq_items);
    }
    public function get_boq_products(Request $request)
    {
       
        $boq_id = $request->boq_id;
        $boq = BoQ::find($boq_id);
        $latestValuedItems = BoQValuationItem::whereHas('boq_valuation', function($q) use($boq_id){
            $q->where('boq_id', $boq_id);
        })
            ->latest()
            ->get(['id', 'boq_item_id', 'product_name', 'product_valued_bal']);

        $orderItems = $boq->items()
            ->where('misc', '!=', 1)
            ->where('type', 'product')
            ->where('boq_sheet_id',$request->boq_sheet_id)
            ->get()
            ->map(function($v) use($latestValuedItems, $boq) {
                 $v['vat_type'] = $boq->vat_type;
                if($v['vat_type'] == 'inclusive'){
                    $boq_rate = $v->boq_rate / 1.16;
                    $v->inclusive_rate = $v->boq_rate;
                    $v->boq_rate = numberClean($boq_rate);
                }elseif($v['vat_type'] == 'exclusive'){
                    $v->boq_rate = $v->boq_rate;
                    $rate = $v->boq_rate * 1.16;
                    $v->inclusive_rate = numberClean($rate);
                }
                $v->boq_amount = round($v->new_qty * $v->boq_rate,2);
                $v['valued_bal'] = round($v->new_qty * $v->boq_rate,4);

                // balance from the previous valuation
                $valuedItem = $latestValuedItems
                    ->where('boq_item_id', $v['id'])
                    ->first();
                if ($valuedItem) $v['valued_bal'] = $valuedItem->product_valued_bal;

                return $v;
            }); 
            
        return response()->json($orderItems);
    }

}
