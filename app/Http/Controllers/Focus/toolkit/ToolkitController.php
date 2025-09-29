<?php

namespace App\Http\Controllers\focus\toolkit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\toolkit\Toolkit;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Repositories\Focus\toolkit\ToolkitRepository;
use App\Http\Responses\Focus\toolkit\CreateResponse;
use App\Http\Responses\Focus\toolkit\EditResponse;
use App\Models\product\ProductVariation;

class ToolkitController extends Controller
{

    /**
     * variable to store the repository object
     * @var ToolkitRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ToolkitRepository $repository ;
     */
    public function __construct(ToolkitRepository $repository)
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
        return view('focus.toolkit.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('focus.toolkit.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       // dd($request->all());
        // $toolkit = new ToolKit();
        // $toolkit->toolkit_name = $request->toolkit_name;
        // $toolkit->toolname = implode(",",$request->toolname);
        // //$string=implode(",",$your_array);
        // $toolkit['ins'] = auth()->user()->ins;
        // $toolkit['user_id'] = auth()->user()->id;
        // $toolkit->save();
        // return new RedirectResponse(route('biller.toolkits.index'), ['flash_success' => 'ToolKit created successfully']);

        $toolkit = $request->only([
            'toolkit_name'
            
        ]);
        $toolkit_items = $request->only([
            'toolname',
            'quantity',
            'uom',
            'code',
            'item_id',
            'qty',
            'cost'
        ]);
        
        $toolkit['ins'] = auth()->user()->ins;
        $toolkit['user_id'] = auth()->user()->id;

        // $subtracted = array_map(function ($x, $y) { 
        //     if($y - $x < 0){
        //         $x=$y;
        //         return $x;
        //     }
        //     else{
        //         return $x;
        //     }
        //     //return $y-$x;
        //  } , $toolkit_items['quantity'], $toolkit_items['qty']);
        // $quantity_issued = array_combine(array_keys($toolkit_items['qty']), $subtracted);
        // // modify and filter items without item_id
        // $toolkit_items['quantity'] = $quantity_issued;
        
        
        // foreach ($toolkit_items['item_id'] as $key => $value) {
        //     //dd($key);
        //     $product_variations = ProductVariation::where('id', $value)->get()->first();
        //     $product_variations->qty = $product_variations->qty - $quantity_issued[$key];
        //     $product_variations->update();
            
        // }

        $toolkit_items = modify_array($toolkit_items);
        $toolkit_items = array_filter($toolkit_items, function ($v) { return $v['item_id']; });
        
        $result = $this->repository->create(compact('toolkit', 'toolkit_items'));

        return new RedirectResponse(route('biller.toolkits.index'), ['flash_success' => 'Toolkit created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Toolkit $toolkit)
    {
        return new ViewResponse('focus.toolkit.view', compact('toolkit'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Toolkit $toolkit, Request $request)
    {
        return new EditResponse($toolkit);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Toolkit $toolkit)
    {
        $data = $request->only([
            'toolkit_name'
        ]);
        $data_items = $request->only([
            'toolname',
            'quantity',
            'uom',
            'code',
            'item_id',
            'id',
            'cost'
        ]);
        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, fn($v) => $v['item_id']);
        if (!$data_items) throw ValidationException::withMessages(['Please use suggested options for input within a row!']);
        $toolkit = $this->repository->update($toolkit, compact('data', 'data_items'));

        $msg = 'Direct toolkit Updated Successfully.';

        return new RedirectResponse(route('biller.toolkits.index'), ['flash_success' => $msg]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Toolkit $toolkit)
    {
        $this->repository->delete($toolkit);

        return new RedirectResponse(route('biller.toolkits.index'), ['flash_success' => 'Toolkit deleted successfully']);
    }
    public function select(Request $request)
    {
        $q = $request->keyword;
        $tools = Toolkit::where('toolkit_name', 'LIKE', '%'.$q.'%')->get(['id', 'toolkit_name']);

        return response()->json($tools);
    }
    
}
