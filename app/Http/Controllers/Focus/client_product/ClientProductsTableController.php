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

namespace App\Http\Controllers\Focus\client_product;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\client_product\ClientProductRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BranchTableController.
 */
class ClientProductsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ClientProductRepository
     */
    protected $client_product;

    /**
     * contructor to initialize repository object
     * @param ClientProductRepository $client_product ;
     */
    public function __construct(ClientProductRepository $client_product)
    {

        $this->client_product = $client_product;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->client_product->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('customer', function ($client_product) {
                $customer = $client_product->customer;
                if ($customer) return $customer->company;
            })
            ->addColumn('row', function ($client_product) {
                return $client_product->row_num;
            })
            ->addColumn('product_code', function ($client_product) {
                $code = $client_product->product_code;
                if($code) {
                    return '<a class="font-weight-bold click text-primary" data-toggle="modal" client-rate="'.$client_product->rate.'"  client-uom="'.$client_product->uom.'" data_id="'.$client_product->id.'" " data-target="#inventoryModal">' . $code . '</a>';
                }
                return '<a class="font-weight-bold click text-danger" data-toggle="modal" client-rate="'.$client_product->rate.'" client-uom="'.$client_product->uom.'" data_id="'.$client_product->id.'" " data-target="#inventoryModal">Click</a>';
            })
            ->addColumn('rate', function ($client_product) {
                return numberFormat($client_product->rate);
            })
            ->addColumn('actions', function ($client_product) {
                return $client_product->action_buttons;
            })
            ->make(true);
    }
}
