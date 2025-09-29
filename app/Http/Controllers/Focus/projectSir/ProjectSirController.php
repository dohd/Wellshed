<?php

namespace App\Http\Controllers\Focus\projectSir;

use App\Http\Controllers\Controller;
use App\Models\customer\Customer;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\project\Project;
use App\Models\quote\Quote;
use App\Models\stock_issue\StockIssue;
use App\Models\stock_issue\StockIssueItem;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;

class ProjectSirController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {

        $productCategories = Productcategory::orderBy('title')->select('id', 'title')->get();

        $projects = Project::orderBy('name')
            ->get()
            ->map(function ($prj) {

                $customer = Customer::find($prj->customer_id);
                $quote = Quote::find($prj->main_quote_id);

                return [
                    'id' => $prj->id,
                    'name' => gen4tid('QT-' , optional($quote)->tid) . ' | ' . optional($customer)->company . ' | ' . $prj->name,
                ];
            });

        $clients = Customer::where(function ($query) {
            $query->whereHas('projects.stock_issuances')
                ->orWhereHas('projects.stock_issues');
        })->orderBy('company')
        ->select('id', 'company')
        ->get();

        return view('focus.projectSir.index', compact( 'productCategories', 'projects', 'clients'));
    }

    /**
     * Display.
     *
     */
    public function show($projectId)
    {

        $filterValues = json_decode(request('params'), true);

        $productCategories = Productcategory::orderBy('title')->select('id', 'title')->get();

        $projects = Project::orderBy('name')->where(function ($query) {
                        $query->whereHas('stock_issuances')
                            ->orWhereHas('stock_issues');
                    })->select('id', 'name')->get();

        $clients = Customer::orderBy('company')->where(function ($query) {
                        $query->whereHas('projects.stock_issuances')
                            ->orWhereHas('projects.stock_issues');
                    })->select('id', 'company')->get();

        return view('focus.projectSir.show', compact('projectId', 'productCategories', 'filterValues', 'projects', 'clients'));
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function getProjectsPayload()
    {

        $projects = Project::orderBy('start_date', 'desc')
            ->when(request('projectFilter'), function ($query) {

                $query->where('id', request('projectFilter'));
            })
            ->when(request('clientFilter'), function ($query) {

                $query->where('customer_id', request('clientFilter'));
            })
             ->where(function ($query) {
                $query->whereHas('stock_issuances')
                    ->orWhereHas('stock_issues');
            })
            ->with([
                'stock_issuances.issued_products' => function ($query) {
                    $query->selectRaw('stock_issue_id, SUM(issue_qty) as total_issue_qty, SUM(amount) as total_amount')
                        ->groupBy('stock_issue_id');
                },
                'stock_issues.issued_products' => function ($query) {
                    $query->selectRaw('stock_issue_id, SUM(issue_qty) as total_issue_qty, SUM(amount) as total_amount')
                        ->groupBy('stock_issue_id');
                }
            ])
            ->get()
            ->map(function ($project) {

                $stockItems =  StockIssueItem::whereHas('stock_issue', function ($si) use ($project) {
                    $si->where('quote_id', $project->main_quote_id)
                    ->orWhere('project_id', $project->id);
                })
                ->when(request('projectFilter'), function ($query) {

                    $query->whereHas('stock_issue.related_project', function ($si) {

                        $si->where('id', request('projectFilter'));
                    })->orWhereHas('stock_issue.project', function($q){
                        $q->where('id',request('projectFilter'));
                    });
                })
                ->when(request('clientFilter'), function ($query) {
                    $query->whereHas('stock_issue', function ($si) {
                        $si->where('customer_id', request('clientFilter'))
                        ->orWhereHas('project', function($q) {
                            $q->where('customer_id', request('clientFilter'));
                        });
                    });
                })
                ->when(request('categoryFilter'), function ($item) {

                    $item->whereHas('productvar', function ($prod) {

                        $prod->where('productcategory_id', request('categoryFilter'));
                    });
                })
                ->when(request('fromDateFilter'), function ($query) {
                    // Filter for 'fromDateFilter'
                    $query->whereDate('created_at', '>=', (new DateTime(request('fromDateFilter')))->format('Y-m-d'));
                })
                ->when(request('toDateFilter'), function ($query) {
                    // Filter for 'toDateFilter'
                    $query->whereDate('created_at', '<=', (new DateTime(request('toDateFilter')))->format('Y-m-d'));
                })
                ->get();

                $filteredQuantity = $stockItems->pluck('issue_qty')->sum();
                $filteredValue = $stockItems->pluck('amount')->sum();

                return (object) [

                    'project_id' => $project->id,

                    'client' => optional($project->customer)->company . ' | ' . optional($project->branch)->name,
                    'project' => gen4tid('PRJ-', $project->tid) . ' | ' . $project->name,

                    'filteredQuantity' => $filteredQuantity,
                    'filteredValue' => $filteredValue,

                    'si' => $stockItems,


                    'allTimeQuantity' => ($project->stock_issuances->isNotEmpty()
                        ? $project->stock_issuances
                        : $project->stock_issues
                    )->sum(function ($record) {
                        return $record->issued_products->sum('total_issue_qty');
                    }),

                    'allTimeValue' => ($project->stock_issuances->isNotEmpty()
                        ? $project->stock_issuances
                        : $project->stock_issues
                    )->sum(function ($record) {
                        return $record->issued_products->sum('total_amount');
                    }),


                    'requestData' => [
                        'fromDateFilter' => request('fromDateFilter'),
                        'toDateFilter' => request('toDateFilter'),
                        'categoryFilter' => request('categoryFilter')
                    ],
                ];
            });

        return $projects;
    }


    /**
     * Display a listing of the resource.
     *
     */
    public function getSpecificsPayload()
    {

        $project = Project::find(request('projectFilter'));

        $issuanceItems = StockIssueItem::query();


        if (request('projectFilter')) {

            $issuanceItems->whereHas('stock_issue', function ($si) use ($project) {

                $si->where('quote_id', optional($project)->main_quote_id)->orWhere('project_id', $project->id);
            });
        }


        if (request('clientFilter')) {

            $issuanceItems->whereHas('stock_issue', function ($si) use ($project) {

                $si->where('customer_id', request('clientFilter'));
            })->orWhereHas('project', function($q) {
                $q->where('customer_id', request('clientFilter'));
            });
        }


        if (request('fromDateFilter')) {

            $issuanceItems->whereHas('stock_issue', function ($si) {

                $si->whereDate('date', '>=', (new DateTime(request('fromDateFilter')))->format('Y-m-d'));
            });
        }


        if (request('toDateFilter')) {

            $issuanceItems->whereHas('stock_issue', function ($si) {

                $si->whereDate('date', '<=', (new DateTime(request('toDateFilter')))->format('Y-m-d'));
            });
        }


        if(request('categoryFilter')) {


            $issuanceItems->whereHas('productvar', function ($si) {

                $si->where('productcategory_id', request('categoryFilter'));
            });
        }


        // Group items by productvar_id and sum their amounts at the database level
        $groupedItems = $issuanceItems
            ->select(
                'id',
                'stock_issue_id',
                'productvar_id',
                DB::raw('SUM(issue_qty) as filtered_qty'),
                DB::raw('SUM(amount) as filtered_total')
            )
            ->groupBy('productvar_id')
            ->get();


//        return response()->json($groupedItems); // Return the result as JSON

        $payload = $groupedItems->map(function ($item){

            $product = ProductVariation::find($item->productvar_id);

            $allTime = StockIssueItem::where('productvar_id', $item->productvar_id)
                ->when(request('projectFilter'), function ($q) {

                    $q->whereHas('stock_issue', function ($si) {

                        $project = Project::find(request('projectFilter'));
                        $si->where('quote_id', optional($project)->main_quote_id)->orWhere('project_id', $project->id);
                    });
                })
                ->get();

            $allTimeQuantity = $allTime->pluck('issue_qty')->sum();
            $allTimeTotal = $allTime->pluck('amount')->sum();
            $issuanceTids = $allTime->map(function($iss) {

                if ($iss->stock_issue) return '<a href="' . route('biller.stock_issues.show', $iss->stock_issue_id) . '" target="_blank"><b>' . gen4tid("SI-", $iss->stock_issue->tid) . '</b></a>';
                else return '';
            });
            $issuanceTids = implode(" <br> ", $issuanceTids->toArray());


            $productCategory = Productcategory::find(optional($product)->productcategory_id);

            return (object) [

                'product' => optional($product)->code . ' | ' . optional($product)->name,
                'category' => optional($productCategory)->title,
                'issuances' => $issuanceTids,
                'filteredValue' => $item->filtered_total,
                'filteredQuantity' => $item->filtered_qty,
            ];
        });

        Log::info($payload);

        return $payload;
    }


    public function getSpecificsSummary() {

        $issuances = $this->getSpecificsPayload();
        $project = Project::find(request('projectFilter'));

        $projectDetails = '';
        if ($project) {

            $cst = '';
            $branch = '';
            if (empty($project->customer)) $cst = 'N/A';
            else $cst = $project->customer->name;
            if (empty($project->branch)) $branch = 'N/A';
            else $branch = $project->branch->name;
            $projectDetails = '<span> Project: </span> <a href="' . route('biller.projects.show', $project) . '"><b>' . gen4tid("PRJ-", $project->tid) . '</b></a> | '
                . '<span> <b>' . $project->name . '</b>' .
                '<br> Client: <b>' . $cst . '</b> <br> Branch: <b>' . $branch . '</b></span>';
        }


        $filteredGrandTotal = number_format($issuances->pluck('filteredValue')->sum(), 2);
        $allTimeGrandTotal = number_format($issuances->pluck('allTimeTotal')->sum(), 2);

        $filteredQuantityGrandTotal = number_format($issuances->pluck('filteredQuantity')->sum(), 2);
        $allTimeQuantityGrandTotal = number_format($issuances->pluck('allTimeQuantity')->sum(), 2);


        $totals = compact('filteredGrandTotal', 'allTimeGrandTotal', 'filteredQuantityGrandTotal', 'allTimeQuantityGrandTotal');

        return compact('totals', 'projectDetails');
    }


    public function getProjectsSummary() {

        $projects = $this->getProjectsPayload();


        $filteredGrandTotal = number_format($projects->pluck('filteredValue')->sum(), 2);
        $allTimeGrandTotal = number_format($projects->pluck('allTimeValue')->sum(), 2);

        $filteredQuantityGrandTotal = number_format($projects->pluck('filteredQuantity')->sum(), 2);
        $allTimeQuantityGrandTotal = number_format($projects->pluck('allTimeQuantity')->sum(), 2);


        $totals = compact('filteredGrandTotal', 'allTimeGrandTotal', 'filteredQuantityGrandTotal', 'allTimeQuantityGrandTotal');


        $requestData = [
            'fromDateFilter' => request('fromDateFilter'),
            'toDateFilter' => request('toDateFilter'),
            'categoryFilter' => request('categoryFilter')
        ];

        $printUrl = route('biller.print-project-sir') . '?fromDateFilter=' . request('fromDateFilter') . '&toDateFilter=' . request('toDateFilter') . '&categoryFilter=' . request('categoryFilter');

        return compact('totals', 'printUrl');
    }


    public function getProjectsDataTable(){

        return DataTables::of($this->getProjectsPayload())
            ->addColumn('filteredValue', function ($d){

                return '<a href="' . route('biller.project-sir.show', $d->project_id) . '?params=' . urlencode(json_encode($d->requestData)) . '" target="_blank">' .
                    ' <span style="font-size: 14px;"><b>' . number_format($d->filteredValue, 2) . '</b></span> </a>';
            })
            ->addColumn('filteredQuantity', function ($d){

                return '<span style="font-size: 14px;"><b>' . number_format($d->filteredQuantity, 0) . '</b></span>';
            })
            ->addColumn('allTimeValue', function ($d){

                return '<a href="' . route('biller.project-sir.show', $d->project_id) . '" target="_blank">' .
                    '<span style="font-size: 14px;"><b>' . number_format($d->allTimeValue, 2) . '</b></span> </a>';
            })
            ->addColumn('allTimeQuantity', function ($d){

                return '<span style="font-size: 14px;"><b>' . number_format($d->allTimeQuantity, 0) . '</b></span>';
            })
            ->rawColumns(['issuances', 'filteredValue', 'filteredQuantity', 'allTimeQuantity', 'allTimeValue'])
            ->make(true);
    }


    public function getSpecificsDataTable(){

        return DataTables::of($this->getSpecificsPayload())
            ->addColumn('filteredValue', function ($d){

                return '<span style="font-size: 14px;"><b>' . number_format($d->filteredValue, 2) . '</b></span>';
            })
            ->addColumn('filteredQuantity', function ($d){

                return '<span style="font-size: 14px;"><b>' . number_format($d->filteredQuantity, 0) . '</b></span>';
            })
            ->rawColumns(['issuances', 'filteredValue', 'filteredQuantity'])
            ->make(true);
    }



    public function printSir() {

        $payload = $this->getProjectsPayload();

        $filters = [
            'fromDateFilter' => request('fromDateFilter'),
            'toDateFilter' => request('toDateFilter'),
            'categoryFilter' => request('categoryFilter')
        ];

        try {

            $htmlContent = view('focus.projectSir.printSir', compact('payload', 'filters'))->render();


            // Create an instance of mPDF with margins
            $mpdf = new Mpdf([
                'format' => 'A3',
                'margin_top' => 10,     // Space for header
                'margin_bottom' => 20,  // Space for footer
                'margin_left' => 15,
                'margin_right' => 15,
            ]);

            $mpdf->setAutoBottomMargin = 'stretch';
            $mpdf->setAutoTopMargin = 'stretch';

            $mpdf->SetAutoPageBreak(true, 10);

            $mpdf->WriteHTML($htmlContent);

            return response($mpdf->Output('Project Materials Report ' . '.pdf', 'I'), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Project Materials Report  ' . '.pdf"'
            ]);

        } catch (\Exception $ex) {

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
