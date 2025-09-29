<?php

namespace App\Http\Controllers\Focus\casual_remuneration;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\casual_remuneration\CasualRemunerationRepository;
use Illuminate\Support\Str;

/**
 * Class CasualRemunerationsTableController.
 */
class CasualRemunerationsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(CasualRemunerationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param ManageProductcategoryRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->repository->getForDataTable();
        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->editColumn('tid', function ($model) {
            return "<a href=". route('biller.casual_remunerations.show', $model) .">". gen4tid('CW-', $model->tid) ."</a>";
        })
        ->editColumn('date', function ($model) {
            return dateFormat($model->date);
        })
        ->editColumn('description', function ($model) {
            return Str::limit(strip_tags($model->description), 60);
        })
        ->editColumn('job_card', function ($model) {
            $laString = '';
            foreach ($model->labourAllocations as $item){
                if ($item['job_card'] && $item['note']) {
                    $laString .= '<b>JC: </b>' . $item['job_card'] . '<b> | </b>' . $item['note'] . '<br>';
                } else if ($item['job_card']) {
                    $laString .= '<b>JC: </b>' . $item['job_card'] . '<br>';
                }
            }
            return $laString;
        })
        ->editColumn('remuneration', function ($model) {
            return number_format($model->total_amount, 2);
        })
        ->editColumn('status', function ($model) {
            if ($model->status == 'PENDING') {
                return '<div class="badge" style="background-color: #BDBDBD;">Pending</div>';
            } else if ($model->status == 'APPROVED') {
                return '<div class="badge" style="background-color: #81C784;">Approved</div>';
            } else if ($model->status == 'ON HOLD') {
                return '<div class="badge" style="background-color: #FDD835;">On Hold</div>';
            } else {
                return '<div class="badge" style="background-color: #b80000;">Rejected</div>';
            }
        })
        ->addColumn('action', function ($model) {
            $view = ' <a href="' . route('biller.casual_remunerations.show',$model->clr_number) . '" class="btn btn-success round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-eye"></i></a> ';
            $edit = ' <a href="' . route('biller.casual_remunerations.edit',$model->clr_number) . '" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-pencil"></i></a> ';
            $delete = '<button class="btn btn-danger trash-cl-remuneration round" id="' . $model->clr_number . '" data-toggle="tooltip" data-placement="top" title="Delete"> <i  class="fa fa-trash"></i></button>';
            return $view . $edit . $delete;
        })
        ->make(true);
    }
}
