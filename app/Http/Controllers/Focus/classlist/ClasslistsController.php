<?php

namespace App\Http\Controllers\Focus\classlist;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\classlist\Classlist;
use App\Repositories\Focus\classlist\ClasslistRepository;


class ClasslistsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ClasslistRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ClasslistRepository $repository ;
     */
    public function __construct(ClasslistRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.classlists.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {
        $tid = Classlist::max('tid')+1;
        $classlists = Classlist::where('is_sub_class', 0)->get();
        return new ViewResponse('focus.classlists.create', compact('classlists', 'tid'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Class', $th);
        }

        return new RedirectResponse(route('biller.classlists.index'), ['flash_success' => 'Class Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\lead\Classlist $classlist
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Classlist $classlist)
    {
        $tid = $classlist->tid;
        $classlists = Classlist::where('is_sub_class', 0)->get();
        return new ViewResponse('focus.classlists.edit', compact('classlist', 'classlists', 'tid'));
    }

    /**
     * Update the specified resource.
     *
     * @param \App\Models\lead\Classlist $classlist
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function update(Request $request, Classlist $classlist)
    {
        try {
            $this->repository->update($classlist, $request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Class', $th);
        }

        return new RedirectResponse(route('biller.classlists.index'), ['flash_success' => 'Class Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\lead\Classlist $classlist
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Classlist $classlist)
    {
        try {
            $this->repository->delete($classlist);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Class', $th);
        }

        return new RedirectResponse(route('biller.classlists.index'), ['flash_success' => 'Class Successfully Deleted']);
    }

    /**
     * Show the view for the specific resource
     *
     * @param \App\Models\lead\Classlist $classlist
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Classlist $classlist, Request $request)
    {
        return new ViewResponse('focus.classlists.view', compact('classlist'));
    }
}
