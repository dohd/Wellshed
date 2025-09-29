<?php

namespace App\Http\Controllers\Focus\refill_product;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\refill_product\RefillProductRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class RefillProductsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var RefillProductRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param RefillProductRepository $repository ;
     */
    public function __construct(RefillProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()   
            ->editColumn('unit_price', function ($product) {
                return numberFormat($product->unit_price);
            }) 
            ->editColumn('productcategory_id', function ($product) {
                return @$product->product_category->title;
            }) 
            ->addColumn('actions', function ($product) {
                return $product->action_buttons;
            })
            ->make(true);
    }
}
