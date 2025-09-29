<?php

namespace App\Http\Controllers\Focus\template_quote;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\template_quote\TemplateQuoteRepository;
use Illuminate\Http\Request;

use App\Models\quote\Quote;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\template_quote\CreateResponse;
use App\Http\Responses\Focus\template_quote\EditResponse;
use App\Repositories\Focus\quote\QuoteRepository;
use App\Http\Requests\Focus\quote\ManageQuoteRequest;
use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Http\Requests\Focus\quote\CreateQuoteRequest;
use App\Http\Requests\Focus\quote\EditQuoteRequest;
use App\Http\Requests\Focus\template_quote\ManageTemplateQuoteRequest;
use App\Models\customer\Customer;
use App\Models\items\VerifiedItem;
use App\Models\lpo\Lpo;
use App\Models\verifiedjcs\VerifiedJc;
use App\Models\fault\Fault;
use App\Models\hrm\Hrm;
use App\Models\template_quote\TemplateQuote;
use Illuminate\Support\Facades\Request as FacadesRequest;

class TemplateQuoteController extends Controller
{
    protected $repository;
    public function __construct(TemplateQuoteRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ManageTemplateQuoteRequest $request)
    {
        $customers = Customer::all(['id', 'company']);
        
        return new ViewResponse('focus.template_quotes.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return new CreateResponse();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->only([
            'notes', 'subtotal', 'tax', 'total', 'taxable',
            'extra_header', 'extra_footer'
        ]);
        $data_items = $request->only([
            'numbering', 'product_id', 'product_name', 'product_qty', 'product_subtotal', 'product_price', 'tax_rate',
            'unit', 'estimate_qty', 'buy_price', 'row_index', 'a_type', 'misc'
        ]);

        // $skill_items = $request->only(['skill', 'charge', 'hours', 'no_technician' ]);
        
        // $equipments = $request->only(['unique_id','equipment_tid','equip_serial','make_type','item_id','capacity','location','fault','row_index_id']);
            
        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;

        $data_items = modify_array($data_items);
        // $skill_items = modify_array($skill_items);
        //  $equipments = modify_array($equipments);
         //dd($data_items);
        // $result = $this->repository->create(compact('data', 'data_items', 'skill_items','equipments'));
        $result = $this->repository->create(compact('data', 'data_items'));

        $route = route('biller.template-quotes.index');
        $msg = trans('alerts.backend.quotes.created');
        // if ($result['bank_id']) {
        //     $route = route('biller.quotes.index', 'page=pi');
        //     $msg = 'Proforma Invoice created successfully';
        // }

        // print preview url
        $valid_token = token_validator('', 'q'.$result->id .$result->tid, true);
        // $msg .= ' <a href="'. route('biller.print_quote', [$result->id, 4, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>'; 

        return new RedirectResponse($route, ['flash_success' => "Template Quote created successfully"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $quote = TemplateQuote::find($id);
        $quote['bill_type'] = 4;
        $accounts = Account::all();
        $features = ConfigMeta::where('feature_id', 9)->first();
        $lpos = Lpo::where('customer_id', $quote->customer_id)->get();

        return new ViewResponse('focus.template_quotes.view', compact('quote', 'accounts', 'features', 'lpos'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $quote = TemplateQuote::find($id);
        return new EditResponse($quote);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $quote = TemplateQuote::find($id);
        $data = $request->only([
            'notes', 'subtotal', 'tax', 'total', 
            'extra_header', 'extra_footer'
        ]);
        $data_items = $request->only([
            'numbering', 'product_id', 'product_name', 'product_qty', 'product_subtotal', 'product_price', 
            'unit', 'estimate_qty', 'buy_price', 'row_index', 'a_type', 'misc'
        ]);
        // $skill_items = $request->only(['skill_id', 'skill', 'charge', 'hours', 'no_technician']);
        // $equipments = $request->only(['eqid','unique_id','equipment_tid','equip_serial','make_type','item_id','capacity','location','fault','row_index_id']);

        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;

        $data_items = modify_array($data_items);
        // $skill_items = modify_array($skill_items);
        // $equipments = modify_array($equipments);
        //dd($data_items);
        $result = $this->repository->update($quote, compact('data', 'data_items'));

        $route = route('biller.template-quotes.index');
        $msg = trans('alerts.backend.quotes.updated');
        // if ($result['bank_id']) {
        //     $route = route('biller.quotes.index', 'page=pi');
        //     $msg = 'Proforma Invoice updated successfully';
        // }

        // print preview url
        $valid_token = token_validator('', 'q'.$result->id .$result->tid, true);
        // $msg .= ' <a href="'. route('biller.print_quote', [$result->id, 4, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>';        

        return new RedirectResponse($route, ['flash_success' => "Template Quote updated successfully"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $quote = TemplateQuote::find($id);
        // $type = $quote->bank_id > 0? 'pi' : 'quote';

        $this->repository->delete($quote);

        $route = route('biller.template-quotes.index');
        $msg = "Template Quote deleted successfully";

        return new RedirectResponse($route, ['flash_success' => $msg]);
    }

    public function getTemplateQuoteDetails(ManageTemplateQuoteRequest $request){
        $templateQuoteId = $request->get('template_quote_id');
        $templateQuote = TemplateQuote::where('id', $templateQuoteId)->with('products')->get();


        return response()->json($templateQuote);
    }
}
