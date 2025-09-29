<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;


class InvoicesController extends Controller
{

    public function __construct()
    {

    }


    public function index()
    {

        $input['sub_json'] = "sub: 0";
        $input['sub_url'] = '';
        $input['title'] = trans('labels.backend.invoices.management');
        $input['meta'] = 'sub';
        $input['pre'] = 1;
        return view('crm.invoices.index', compact('input'));
    }


}
