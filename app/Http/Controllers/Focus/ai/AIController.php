<?php

namespace App\Http\Controllers\Focus\ai;

use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Log;
use Validator;

class AIController extends Controller
{
    public function logError($e)
    {
        return Log::error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    }

    /**
     * Analytics AI Index Page
     */
    public function analytics(Request $request)
    {
        $business = auth()->user()->business;
        return new ViewResponse('focus.ai.analytics', compact('business'));
    }
}
