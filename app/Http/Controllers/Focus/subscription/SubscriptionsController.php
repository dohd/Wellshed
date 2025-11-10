<?php

namespace App\Http\Controllers\Focus\subscription;

use App\Http\Controllers\Controller;
use App\Models\customer\Customer;
use App\Models\subpackage\SubPackage;
use App\Models\subscription\Subscription;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('focus.subscriptions.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = Customer::all();
        $subpackages = SubPackage::all();
        return view('focus.subscriptions.create', compact('customers', 'subpackages'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required',
            'end_date' => 'required',
            'customer_id' => 'required',
        ]);
        $data = $request->except(['_token', '_method']);

        try {
            foreach($request->only('start_date', 'end_date') ?? [] as $key => $value) {
                $data[$key] = Carbon::parse($value)->format('Y-m-d H:i:s');
            }
            $subscription = Subscription::create($data);

            return redirect(route('biller.subscriptions.index'))->with(['flash_success' => 'Package Created Successfully']);
        } catch (Exception $e) {
            return errorHandler('Error Creating Package', $e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Subscription $subscription)
    {
        $packages = SubPackage::where('id', '!=', $subscription->sub_package_id)->get();
        return view('focus.subscriptions.view', compact('subscription', 'packages'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Subscription $subscription)
    {
        $customers = Customer::all();
        $subpackages = SubPackage::all();
        return view('focus.subscriptions.edit', compact('subscription', 'customers', 'subpackages'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Subscription $subscription)
    {
        $request->validate([
            'start_date' => 'required',
            'end_date' => 'required',
            'customer_id' => 'required',
        ]);
        $data = $request->except(['_token', '_method']);

        try {
            foreach($request->only('start_date', 'end_date') ?? [] as $key => $value) {
                $data[$key] = Carbon::parse($value)->format('Y-m-d H:i:s');
            }
            $subscription->update($data);
            
            return redirect(route('biller.subscriptions.index'))->with(['flash_success' => 'Package Updated Successfully']);
        } catch (Exception $e) {
            return errorHandler('Error Updating Package', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Subscription $subscription)
    {
        try {
            $subscription->update(['deleted_at' => now(), 'deleted_by' => auth()->id()]);            
            return redirect(route('biller.subscriptions.index'))->with(['flash_success' => 'Package Deleted Successfully']);
        } catch (Exception $e) {
            return errorHandler('Error Deleting Package', $e);
        }
    }

    /**
     * Subscription Plan Upgrade
     * 
     * **/
    public function upgrade(Request $request)
    {
        try {
            $subscription = Subscription::findOrFail(request('subscription_id'));
            $prevPlanId = $subscription->sub_package_id;
            $subscription->update([
                'sub_package_id' => request('sub_package_id'),
                'prev_sub_package_id' => $prevPlanId,
                'upgrade_date' => now()->toDateString(),
                'upgrade_effective_date' => now()->toDateString(),
            ]);                
            return response()->json([
                'status' => 'success', 
                'message' => 'Subscription upgraded successfully'
            ]);
        } catch (Exception $e) {
            \Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['status' => 'error', 'message' => 'Subscription upgraded failed'], 500);
        }
    }
}
