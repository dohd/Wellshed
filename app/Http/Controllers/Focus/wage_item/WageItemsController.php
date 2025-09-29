<?php

namespace App\Http\Controllers\Focus\wage_item;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\wage_item\WageItem;
use App\Repositories\Focus\wage_item\WageItemRepository;
use Illuminate\Validation\Rule;

/**
 * WageItemsController
 */
class WageItemsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $repository ;
     */
    public function __construct(WageItemRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return view('focus.wage_items.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     */
    public function create()
    {
        return view('focus.wage_items.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
            ],
        ]);
        
        try {
            $this->repository->create($request->except(['_token', 'ins']));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Wage Item', $th);
        }
        
        return new RedirectResponse(route('biller.wage_items.index'), ['flash_success' => 'Wage Item Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     */
    public function edit(WageItem $wageItem)
    {
        return view('focus.wage_items.edit', compact('wageItem'));
    }


    public function update(Request $request, WageItem $wageItem )
    {
        $request->validate([
            'name' => [
                'required',
                'string',
            ],
        ]);

        try {
            $this->repository->update($wageItem, $request->except(['_token', 'ins']));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Wage Item', $th);
        }

        return new RedirectResponse(route('biller.wage_items.index'), ['flash_success' => 'Wage Item Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(WageItem $wageItem)
    {
        try {
            $this->repository->delete($wageItem);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Wage Item', $th);
        }

        return new RedirectResponse(route('biller.wage_items.index'), ['flash_success' => 'Wage Item Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(WageItem $wageItem, Request $request)
    {
        return new ViewResponse('focus.wage_items.view', compact('wageItem'));
    }
}