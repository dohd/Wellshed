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
namespace App\Http\Controllers\Focus\BoM;

use App\Models\bom\BoM;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\additional\Additional;
use App\Models\boq\BoQSheet;
use App\Models\lead\Lead;
use App\Repositories\Focus\bom\BoMRepository;


/**
 * BoMsController
 */
class BoMsController extends Controller
{
    /**
     * variable to store the repository object
     * @var BoMRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param BoMRepository $repository ;
     */
    public function __construct(BoMRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\BoM\ManageBoMRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.boms.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateBoMRequestNamespace $request
     * @return \App\Http\Responses\Focus\BoM\CreateResponse
     */
    public function create()
    {
        return view('focus.boms.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBoMRequestNamespace $request
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
            return errorHandler('Error Creating BoM '.$th->getMessage(), $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.boms.index'), ['flash_success' => 'BoM Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\BoM\BoM $bom
     * @param EditBoMRequestNamespace $request
     * @return \App\Http\Responses\Focus\BoM\EditResponse
     */
    public function edit(BoM $bom)
    {
        $additionals = Additional::all();
        $prefixes = prefixesArray(['quote', 'lead'], auth()->user()->ins);
        $leads = Lead::get();
        $boq_id = $bom->boq_id;
        $boq_sheets = BoQSheet::whereHas('boqs', function($q) use($boq_id){
            $q->where('boq_id', $boq_id);
        })->get();
        return view('focus.boms.edit', compact('bom','additionals','leads','prefixes','boq_sheets'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBoMRequestNamespace $request
     * @param App\Models\BoM\BoM $bom
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, BoM $bom)
    {
        $dataInput = $request->input('data', []);

        $numbering = [];
        $misc = [];
        $productId = [];
        $unitId = [];
        $productName = [];
        $qty = [];
        $rate = [];
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
                case 'misc[]':
                    $misc[] = $item['value'];
                    break;
                case 'product_id[]':
                    $productId[] = $item['value'];
                    break;
                case 'unit_id[]':
                    $unitId[] = $item['value'];
                    break;
                case 'product_name[]':
                    $productName[] = $item['value'];
                    break;
                case 'qty[]':
                    $qty[] = $item['value'];
                    break;
                case 'rate[]':
                    $rate[] = $item['value'];
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
                'misc' => $misc[$i] ?? '0',
                'product_id' => $productId[$i] ?? '0',
                'unit_id' => $unitId[$i] ?? '0',
                'product_name' => $productName[$i] ?? null,
                'qty' => $qty[$i] ?? '0',
                'rate' => $rate[$i] ?? '0',
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
            $this->repository->update($bom, compact('data', 'data_items','boq_sheet_id'));
        } catch (\Throwable $th) {
            //throw $th;
            session()->flash('error', 'Error updating BoM '.$th->getMessage());
            errorHandler('Error updating BoM '.$th->getMessage(), $th);
            return response()->json(['success' => 'Error updating BoM '.$th->getMessage()]);
        }
        session()->flash('success', 'BoM / MTO updated successfully!');
        return response()->json(['success' => 'BoM / MTO updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBoMRequestNamespace $request
     * @param App\Models\BoM\BoM $bom
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(BoM $bom)
    {
        //Calling the delete method on repository
        $this->repository->delete($bom);
        //returning with successfull message
        return new RedirectResponse(route('biller.boms.index'), ['flash_success' => 'BoM Deleted Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteBoMRequestNamespace $request
     * @param App\Models\BoM\BoM $bom
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(BoM $bom)
    {

        //returning with successfull message
        $additionals = Additional::all();
        return new ViewResponse('focus.boms.view', compact('bom','additionals'));
    }

    public function select_bom(Request $request)
    {
        $boms = BoM::where('lead_id', $request->lead_id)->get();
        return response()->json($boms);
    }
    public function get_bom_items(Request $request)
    {
        $bom = BoM::find($request->bom_id);
        $bom_items = $bom->items()->whereHas('boq_item',fn($q) => $q->where('is_imported','!=',1))->get();
        $bom_items = $bom_items->map(function ($item){
            $item->uom = @$item->unit_of_measure->code;
            return $item;
        });
        return response()->json($bom_items);
    }
    public function bom_items(Request $request)
    {
        $bom = BoM::find($request->bom_id);
        $bom_items = $bom->items()->where([
            'bom_id' => $request->bom_id,
            'boq_sheet_id' => $request->boq_sheet_id,
        ])->with('product')->get();
        $bom_items = $bom_items->map(function ($item){
            $item->uom = @$item->unit_of_measure->code;
            return $item;
        });
        return response()->json($bom_items);
    }

}
