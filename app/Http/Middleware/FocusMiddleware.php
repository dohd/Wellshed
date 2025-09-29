<?php

namespace App\Http\Middleware;

use App\Models\Company\ConfigMeta;
use Closure;

class FocusMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /**
         * Set core configuraion key value to company instance 
         */
        try {
            $company = auth()->user()->business;
            $meta = ConfigMeta::where('feature_id', 2)->first();
            config([
                'core' => $company, 
                'app.timezone' => $company->zone, 
                'currency' => $meta->currency,
                'agentToken' => base64_encode($company->agent_key . ':' . $company->email),
            ]);
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error($th->getMessage() .' {user_id: '. auth()->user()->id . '}' . ' at ' . $th->getFile() . ':' . $th->getLine());
            abort(500, 'Check company and meta configurations');
        }

        return $next($request);
    }
}
