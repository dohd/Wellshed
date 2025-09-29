<?php

namespace App\Http\Controllers\Focus\classlist;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\classlist\ClasslistRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClasslistsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ClasslistRepository
     */
    protected $classlist;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(ClasslistRepository $classlist)
    {

        $this->classlist = $classlist;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->classlist->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($classlist) {
                return gen4tid('', $classlist->tid);
            })
            ->editColumn('parent_id', function ($classlist) {
                return @$classlist->parent_class->name ?: 'N/A';
            })
            ->addColumn('actions', function ($classlist) {
                return $classlist->action_buttons;
            })
            ->make(true);
    }
}
