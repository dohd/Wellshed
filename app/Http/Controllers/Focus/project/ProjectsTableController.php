<?php

namespace App\Http\Controllers\Focus\project;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\project\ProjectRepository;
use DB;

/**
 * Class ProjectsTableController.
 */
class ProjectsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProjectRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProjectRepository $repository ;
     */
    public function __construct(ProjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $query = $this->repository->getForDataTable();
        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['lead', 'project', 'proforma_invoice', 'quote'], $ins);

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('name', function ($project) {
                $customer = '';
                $branch = '';
                if (empty($project->customer)) $customer = 'N/A';
                else $customer = $project->customer->company;
                if (empty($project->branch)) $branch = 'N/A';
                else $branch = $project->branch->name;
                return '<p> ' . $project->name . '<br> <b> Client: </b> ' . $customer . '<br> <b> Branch: </b> ' . $branch . '</p>';
                    
            })
            ->addColumn('wip_account', function ($project) {
                if ($project->wipAccount) {
                    return '<a href="'. route('biller.accounts.show', $project->wipAccount) .'">'. @$project->wipAccount->holder .'</a>';
                }
            })
            ->addColumn('customer', function ($project) {
                $name = '';
                if ($project->customer_project) {
                    $name = $project->customer_project->company;
                    if ($project->branch) $name .= " - {$project->branch->name}";
                }
                return $name;
            })
            ->editColumn('tid', function ($project) use ($prefixes) {
                return '<a href="' . route('biller.projects.show', $project) . '"><b>' . gen4tid("{$prefixes[1]}-", $project->tid) . '</b></a>';;
            })
            ->filterColumn('tid', function ($query, $tid) use ($prefixes) {
                $arr = explode('-', $tid);
                if (count($arr) > 1 && strtolower($arr[0]) == strtolower($prefixes[1])) {
                    $query->where('tid', floatval($arr[1]));
                } elseif (floatval($tid)) {
                    $query->where('tid', floatval($tid));
                }
            })
            ->editColumn('start_date', function ($project) {
                return dateFormat($project->start_date);
            })
            ->orderColumn('start_date', '-start_date $1')
            ->editColumn('end_date', function ($project) {
                if (!$project->end_date) return '';
                return dateFormat($project->end_date);
            })
            ->orderColumn('end_date', '-end_date $1')
            ->addColumn('status', function ($project) {
                $isClientLogin = auth()->user()->customer_id? '#' : '';
                return '<span  data-id="' . $project->misc->id . '" project-id="' . $project->id . '" end-note="' . $project->end_note . '" class="badge badge-secondary status" style="background-color:' . $project->misc->color . ';cursor:pointer;" data-toggle="modal" data-target="#statusModal'. $isClientLogin .'">'
                    . $project->misc->name . ' <i class="fa fa-caret-down" aria-hidden="true"></i></span>';
            })
            ->editColumn('main_quote_id', function ($project) {
                $tids = [];
                foreach ($project->quotes as $quote) {
                    $tid = gen4tid($quote->bank_id ? 'PI-' : 'QT-', $quote->tid);
                    // $tids[] = '<a href="'. route('biller.quotes.show', $quote) .'"><b>'. $tid .'</b></a>';
                    $tids[] = $tid;
                }
                return implode(', ', $tids);
            })
            ->filterColumn('main_quote_id', function ($query, $tid) use ($prefixes) {
                $arr = explode('-', $tid);
                if (count($arr) > 1 && in_array($arr[0], ['QT', 'PI'])) {
                    $query->whereHas('quotes', fn($q) => $q->where('tid', floatval($arr[1])));
                } elseif (floatval($tid)) {
                    $query->whereHas('quotes', fn($q) => $q->where('tid', floatval($tid)));
                }
            })
            // ->editColumn('exp_profit_margin', function ($project) {
            //     return $project->exp_profit_margin;
            // })
            // ->orderColumn('exp_profit_margin', '-exp_profit_margin $1')

            ->addColumn('exp_profit_margin', function ($project) {
                $total_estimate = 0;
                $total_balance = 0;
                foreach ($project->quotes as $quote) {
                    $actual_amount = $quote->subtotal;

                    $dir_purchase_amount = $project->purchase_items->sum('amount') / $project->quotes->count();
                    $proj_grn_amount = $project->grn_items()->sum(DB::raw('round(rate*qty)')) / $project->quotes->count();
                    $labour_amount = $project->labour_allocations()->sum(DB::raw('hrs * 500')) / $project->quotes->count();
                    $expense_amount = $dir_purchase_amount + $proj_grn_amount + $labour_amount;
                    if ($quote->projectstock) $expense_amount += $quote->projectstock->sum('total');

                    $balance = $actual_amount - $expense_amount;
                    // aggregate
                    // $total_actual += $actual_amount;
                    $total_estimate += $expense_amount;
                    $total_balance += $balance;
                }
                $exp_profit_margin = round(div_num($total_balance, $total_estimate) * 100);
                return '<span key="' . $exp_profit_margin . '">' . $exp_profit_margin . '</span>';
            })
            ->addColumn('job_hrs', function ($project) {
                return +$project->labour_allocations()->sum('hrs');
            })
            ->addColumn('actions', function ($project) {
                return $project->action_buttons;
            })
            ->make(true);
    }
}
