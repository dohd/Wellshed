<?php

namespace App\Http\Controllers\Focus\stockIssuance;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\product\ProductVariation;
use App\Models\stock_issuance\StockIssuanceApproval;
use App\Models\stock_issuance\StockIssuanceRequest;
use App\Models\stock_issuance\StockIssuanceRequestItems;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockIssuanceApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function approve(string $sirNumber)
    {

        try {
            DB::beginTransaction();

            $sia = new StockIssuanceApproval();

            $sia->sia_number = uniqid('SIA-');
            $sia->sir_number = $sirNumber;
            $sia->approved_by = Auth::user()->id;
            $sia->date = (new DateTime('now'))->format('Y-m-d H:i:s');

            $sia->save();

            /** Updading SIR status */
            $sir = StockIssuanceRequest::find($sirNumber);
            $sir->status = 'Approved';

            $sir->save();

            /** Updating the Inventory */
            $sirItemsNumbers = (new StockIssuanceRequestController())->getValuesByKey(($sir->sirItems)->toArray(), 'siri_number');
            $sirItems = StockIssuanceRequestItems::where('sir_number', $sir->sir_number)
                ->whereIn('siri_number', $sirItemsNumbers)
                ->get();

            foreach ($sirItems as $item){

                $productVariation = ProductVariation::find($item->product);

                if ($productVariation->qty < $item->quantity){

                    DB::rollBack();
                    return redirect()->back()->with(
                        ["flash_error" => "Approval Cannot Proceed! Requested Quentity of item '" . $productVariation->code . ": " . $productVariation->name . "' Exceeds the Quantity Available in Your Inventory"]
                    );
                }

                $productVariation->qty -= $item->quantity;
                $productVariation->save();
            }


            DB::commit();

        } catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'ERROR : ' . $e->getMessage());
        }

        return new RedirectResponse(route('biller.stock-issuance-approval.show', $sia->sia_number), ['flash_success' => 'Stock Issuance Request Approved Successfully!']);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($siaNumber)
    {

        $sia = StockIssuanceApproval::find($siaNumber)
            ->join('users', 'stock_issuance_approvals.approved_by', 'users.id')
            ->select(
                'sia_number',
                'sir_number',
                DB::raw('CONCAT(first_name, " ", last_name) as approved_by'),
                'stock_issuance_approvals.date as date',
            )
            ->first();

        $sir = StockIssuanceRequest::find($sia->sir_number)
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

        return new ViewResponse('focus.stockIssuanceRequest.show', compact('sia', 'sir', 'sirItems'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reject($siaNumber)
    {

        try {
            DB::beginTransaction();

            $sia = StockIssuanceApproval::find($siaNumber);

            $sirNumber = $sia->sir_number;

            $sir = StockIssuanceRequest::find($sia->sir_number);
            $sir->status = 'Rejected';
            $sir->save();

            $sia->delete();

            DB::commit();

        } catch (Exception $e){
            DB::rollBack();
            return redirect()->back()->with('flash_error', 'ERROR : ' . $e->getMessage());
        }

        return new RedirectResponse(route('biller.stock-issuance-request.show', $sirNumber), ['flash_success' => 'Stock Issuance Request Rejected Successfully!']);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
