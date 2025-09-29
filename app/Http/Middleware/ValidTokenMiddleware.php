<?php

namespace App\Http\Middleware;

use App\Models\bill\Bill;
use App\Models\Company\ConfigMeta;
use App\Models\invoice\Invoice;
use App\Models\order\Order;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\quote\Quote;
use App\Models\djc\Djc;
use App\Models\rjc\Rjc;
use Closure;
use Illuminate\Support\Facades\App;

class ValidTokenMiddleware
{
    /*
     _ Handle an incoming request.
     _
     _ @param  \Illuminate\Http\Request  $request
     _ @param  \Closure  $next
     _ @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (App::environment('production')) error_reporting(0);        
        if (!isset($request->type)) abort(403, 'Access denied');
        
        $resource = (object) array();
        $id = $request->id;
        switch ($request->type) {
            case 1:
                $resource = Invoice::withoutGlobalScopes()->find($id);
                break;
            case 3:
                $resource = Bill::withoutGlobalScopes()->find($id);
                break;
            case 4:
                $resource = Quote::withoutGlobalScopes()->find($id);
                break;
            case 5:
                $resource = Order::withoutGlobalScopes()->find($id);
                break;
            case 9:
                $resource = Purchaseorder::withoutGlobalScopes()->find($id);
                break;
            case 10:
                $resource = Djc::withoutGlobalScopes()->find($id);
                break;
            case 11:
                $resource = Rjc::withoutGlobalScopes()->find($id);
                break;
        }

        if (isset($resource->ins)) {
            $meta = ConfigMeta::where('feature_id', 15)->first('value1')->value1;
            session(['theme' => $meta]);
        }
        
        return $next($request);
    }
}
