<?php

namespace App\Http\Controllers\Focus\project;

use App\Http\Controllers\Controller;
use App\Models\casual_labourer_remuneration\CasualLabourersRemuneration;
use App\Models\items\PurchaseItem;
use App\Models\project\ProjectMileStone;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\project\ProjectRepository;
use App\Models\items\GoodsreceivenoteItem;
use App\Models\labour_allocation\LabourAllocation;
use App\Models\stock_issue\StockIssueItem;

/**
 * Class ProjectsTableController.
 */
class ExpensesTableController extends Controller
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
        $core = $this->get_expenses();
        $core = $this->request_filter($core);
        $group_totals = $this->group_totals($core);

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('exp_category', function ($item) {
                $exp_category = '';
                switch ($item->exp_category) {
                    case 'dir_purchase_stock':
                        $exp_category = 'Direct Purchase Stock';
                        break;
                    case 'dir_purchase_service':
                        $exp_category = 'Direct Purchase Service';
                        break;
                    case 'purchase_order_stock':
                        $exp_category = 'Purchase Order Stock';
                        break;
                    case 'inventory_stock':
                        $exp_category = 'Inventory Stock';
                        break;
                    case 'labour_service':
                        $exp_category = 'Labour Service';
                        break;
                }
                if ($item->ledger_account)
                    return "{$exp_category}<br>(Account: {$item->ledger_account})";
                return $exp_category;
            })
            ->editColumn('qty', function ($item) {
                return +$item->qty;
            })
            ->editColumn('rate', function ($item) {
                return numberFormat($item->rate);
            })
            ->editColumn('amount', function ($item) {
                return numberFormat($item->amount);
            })
            ->addColumn('group_totals', function () use ($group_totals) {
                return $group_totals;
            })
            ->make(true);
    }

    /**
     * Apply Expense Filter
     */
    public function request_filter($expenses)
    {
        $params = request()->only(['exp_category', 'ledger_id', 'supplier_id', 'product_name', '']);
        $params = array_filter($params);
        if (!$params) return $expenses;

        return $expenses->filter(function ($item) use ($params) {
            $eval = 0;
            foreach ($params as $key => $value) {
                // Check if the parameter is 'product_name'
                if ($key == 'product_name') {
                    // Use strpos() to check if $value is contained within $item->$key
                    if (strpos($item->$key, $value) !== false) $eval += 1;
                } else {
                    // Regular check for exact match
                    if ($item->$key == $value) $eval += 1;
                }
            }
            return count($params) == $eval;
        });
    }



    /**
     * Expense Group Totals
     */
    public function group_totals($expenses = [])
    {
        $group_totals = [];
        foreach ($expenses as $expense) {
            if (@$group_totals[$expense->exp_category])
                $group_totals[$expense->exp_category] += $expense->amount * 1;
            else $group_totals[$expense->exp_category] = $expense->amount * 1;
        }
        $group_totals['grand_total'] = collect(array_values($group_totals))->sum();

        return $group_totals;
    }

    /**
     * Collect Related Project Expenses
     */
    public function get_expenses()
    {
        $index = 0;
        $expenses = collect();
            
        // direct purchase
        $purchaseItems = PurchaseItem::whereHas('project')
        ->where('itemproject_id', request('project_id'))
        ->when(request('budget_line_filter'), function ($q) {
            $q->where(function($q) {
                $q->whereHas('purchase', fn($q) => $q->whereHas('budgetLine')->where('project_milestone', request('budget_line_filter')));
                $q->orWhere(fn($q) => $q->whereHas('budgetLine')->where('budget_line_id', request('budget_line_filter')));                
            });
        })
        ->with('purchase', 'account', 'budgetLine')
        ->get();
        foreach ($purchaseItems as $item) {
            $index++;
            $projectMilestone = $item->budgetLine ?: @$item->purchase->budgetLine;
            $data = (object) [
                'id' => $index,
                'origin_id' => $item->id,
                'exp_category' => $item->type == 'Stock' ? 'dir_purchase_stock' : ($item->type == 'Expense' ? 'dir_purchase_service' : ''),
                'milestone_id' => @$projectMilestone->id,
                'milestone' => @$projectMilestone->name ?: 'No Budget Line Selected',
                'ledger_id' => @$item->account->id,
                'ledger_account' => @$item->account->holder,
                'supplier_id' => @$item->purchase->supplier->id,
                'supplier' => @$item->purchase->suppliername ? $item->purchase->suppliername : ($item->purchase->supplier ? $item->purchase->supplier->name : ''),
                'productvar_id' => $item->type == 'Stock'? @$item->productvariation->id : null,
                'product_code' => $item->type == 'Stock'? @$item->productvariation->code : null,
                'product_name' => $item->purchase? '(' . gen4tid('DP-', $item->purchase->tid) . ') <br>' . $item->description : '',
                'description' => $item->description,
                'date' => $item->purchase? dateFormat($item->purchase->date) : '',
                'uom' => $item->uom,
                'qty' => $item->qty,
                'rate' => $item->qty > 0 ? ($item->amount / $item->qty) : $item->amount,
                'amount' => $item->amount,
            ];
            $expenses->add($data);
        }

        // Stock issuance
        $stockIssueItems = StockIssueItem::whereHas('stock_issue', function($q) {
            $q->whereHas('quote', fn($q) => $q->whereHas('project', fn($q) => $q->where('projects.id', request('project_id'))));
            $q->orWhereHas('project', fn($q) => $q->where('projects.id', request('project_id')));
        })
        ->with(['stock_issue.quote.saleReturn.items', 'productvar.product.unit'])
        ->get();

        // Net-off stock issues with returns
        $saleReturnItems = collect();
        foreach ($stockIssueItems as $item) {
            $saleReturnItems1 = @$item->stock_issue->quote->saleReturn->items ?: collect();
            $saleReturnItems = $saleReturnItems->merge($saleReturnItems1);
        }
        $saleReturnItems = $saleReturnItems->unique('id');
        if ($saleReturnItems->count()) {
            foreach ($stockIssueItems as $key => $item) {
                $issueQty = (float) $item->issue_qty;
                $productvarId = $item->productvar_id;
                foreach ($saleReturnItems as $key1 => &$returnItem) {
                    if ($returnItem->productvar_id == $productvarId) {
                        $returnQty = (float) $returnItem->return_qty;

                        if ($issueQty <= $returnQty) {
                            $returnItem->return_qty -= $issueQty;
                            $issueQty = 0;
                            break; // Done matching this item
                        } else {
                            $issueQty -= $returnQty;
                            $returnItem->return_qty = 0;
                            unset($saleReturnItems[$key1]); // Fully consumed
                        }
                    }
                }
                // filter stock issues
                if ($issueQty > 0) {
                    $stockIssueItems[$key]['issue_qty'] = $issueQty;
                } else {
                    unset($stockIssueItems[$key]);
                }
            }
        }
        // append stock issues to expense collection
        foreach ($stockIssueItems as $item) {
            $index++;
            $date = $item->stock_issue->date;
            $issueNo = gen4tid('ISS-', $item->stock_issue->tid);
            $issueQty = +$item->issue_qty;
            $expenses->push((object) [
                'id' => $index,
                'origin_id' => $item->id,
                'exp_category' => 'inventory_stock',
                'milestone_id' => null,
                'milestone' => 'No Budget Line Selected',
                'ledger_id' => '',
                'ledger_account' => '',
                'supplier_id' => '',
                'supplier' => 'Stock Issuance',
                'productvar_id' => @$item->productvar->id,
                'product_code' => @$item->productvar->code,
                'product_name' => "({$issueNo})<br>" . @$item->productvar->name . ' || ' . @$item->productvar->code,
                'description' => @$item->productvar->name,
                'date' => $date,
                'uom' => @$item->productvar->product->unit->title,
                'qty' => $issueQty,
                'rate' => $item->cost,
                'amount' => $item->amount,
            ]);
        }

        // purchase order goods received
        $grnItems = GoodsreceivenoteItem::whereHas('project')
        ->where('itemproject_id', request('project_id'))
        ->get();
        foreach ($grnItems as $item) {
            $index++;
            $grn = $item->goodsreceivenote;
            $po_item = $item->purchaseorder_item;
            $po = @$po_item->purchaseorder;
            if (empty($item->purchaseorder_item)) continue;

            $projectMilestone = ProjectMileStone::where('id',@$item->purchaseorder_item->purchaseorder->project_milestone)->first();
            $data = (object) [
                'id' => $index,
                'origin_id' => $item->id,
                'exp_category' => 'purchase_order_stock',
                'milestone_id' => @$projectMilestone->id,
                'milestone' => @$projectMilestone->name ?: 'No Budget Line Selected',
                'ledger_id' => @$item->account->id,
                'ledger_account' => @$item->account->holder,
                'supplier_id' => @$grn->supplier->id,
                'supplier' => @$grn->supplier->name,
                'productvar_id' => @$po_item->productvariation->id,
                'product_code' => @$po_item->productvariation->code,
                'product_name' => $po? '(' . gen4tid('PO-', $po->tid) . ') <br>' . @$po_item->description : '',
                'description' => @$po_item->description,
                'date' => $item->goodsreceivenote? dateFormat($item->goodsreceivenote->date) : '',
                'uom' => @$po_item->uom,
                'qty' => $item->qty,
                'rate' => $item->rate,
                'amount' => $item->rate * $item->qty,
            ];
            $expenses->add($data);
        }

        // labour allocations (unconsolidated for payment)
        $labourAllocations = LabourAllocation::doesntHave('casualLabourersRemuneration')
        ->whereHas('project')
        ->where('project_id', request('project_id'))
        ->with(['items', 'projectMilestone', 'company', 'casualLabourers', 'casualWeeklyHrs'])
        ->get();
        foreach ($labourAllocations as $allocation) {
            $totalHrs = $allocation->hrs;
            $stdRate = (float) @$allocation->company->rate;
            $employeesAmt = $allocation->items->sum(fn($v) => $v->hrs * $stdRate);

            if ($allocation->casualWeeklyHrs->count()) {
                $employeesAmt = 0; 
                $casualsAmt = 0;
                foreach ($allocation->casualLabourers as $casual) {
                    $casual->casualWeeklyHrs = $casual->casualWeeklyHrs->filter(fn($v) => ($v->casual_labourer_id == $casual->id));
                    $overtimeHrs = $casual->casualWeeklyHrs->whereNotNull('is_overtime')->sum('total_ot_hrs');
                    $regularHrs = $casual->casualWeeklyHrs->whereNull('is_overtime')->sum('total_reg_hrs');
                    $totalHrs = $overtimeHrs + $regularHrs;

                    $OTmultiplierHrs = 0;
                    $weekdayTotal = 0;
                    $weekendTotal = 0;
                    $wageItem = $casual->wageItems->where('earning_type', 'overtime')->first();
                    $overtimeLog = $casual->casualWeeklyHrs->whereNotNull('is_overtime')->first();
                    if ($wageItem && $overtimeLog) {
                        foreach ($overtimeLog->getAttributes() as $key => $value) {
                            // holidays
                            // ** set holiday logic **
                            
                            if (in_array($key, ['mon', 'tue', 'wed', 'thu', 'fri'])) {
                                $weekdayTotal += $value * $wageItem->weekday_ot;
                            } elseif ($key == 'sat') {
                                 $weekendTotal += $value * $wageItem->weekend_sat_ot;
                            } elseif ($key == 'sun') {
                                $weekendTotal += $value * $wageItem->weekend_sun_ot;
                            }
                        }
                        $OTmultiplierHrs = $weekdayTotal+$weekendTotal;
                    }
                    $overtimeTtl = $casual->rate * $OTmultiplierHrs;
                    $regularTtl = $casual->rate * $regularHrs;
                    $casualsAmt += $regularTtl + $overtimeTtl;
                }
            } else {
                $casualsAmt = $allocation->casualLabourers->sum(fn($v) => $v->rate * $allocation->hrs);
            }

            $totalAmt = $employeesAmt + $casualsAmt;
            $index++;
            $expenses->add((object) [
                'id' => $index,
                'origin_id' => $allocation->id,
                'labour_allocation_id' => $allocation->id,
                'link' => "<a href=". route('biller.labour_allocations.edit', $allocation) .">link</a>",
                'exp_category' => 'labour_service',
                'milestone_id' => @$allocation->projectMilestone->id,
                'milestone' => (string) @$allocation->projectMilestone->name ?: 'No Budget Line',
                'ledger_id' => '',
                'ledger_account' => '',
                'supplier_id' => '',
                'supplier' => 'Employee / Casual',
                'productvar_id' => null,
                'product_code' => null,
                'product_name' => @$allocation->type,
                'description' => $allocation->note,
                'date' => dateFormat(@$allocation->date),
                'uom' => 'Mnhrs',
                'qty' => $totalHrs,
                'rate' => 0,
                'amount' => $totalAmt,
            ]);
        }

        // casual remunerations
        $casualsRemunerations = CasualLabourersRemuneration::whereHas('labourAllocations', function ($q) {
            $q->where('project_id', request('project_id'));
        })
        ->with([
            'labourAllocations' => fn ($q) => $q->where('project_id', request('project_id')),
            'clrWages' => fn($q) => $q->whereHas('labourAllocation', function ($q) {
                $q->where('project_id', request('project_id'));
            }),
        ])
        ->get();
        foreach ($casualsRemunerations as $casualRemun) {
            $allocations = $casualRemun->labourAllocations;
            $milestoneList = $allocations->map(fn ($v) => (string) @$v->budgetLine->name)->implode('<br>');

            $tid = gen4tid('CW-', $casualRemun->tid);
            $totalHrs = $casualRemun->clrWages->sum(fn($v) => $v->hours + $v->overtime_hrs);
            $casualsAmt = $casualRemun->clrWages->sum('wage_total');

            $expenses->push((object) [
                'id' => $casualRemun->id,
                'origin_id' => $casualRemun->id,
                'exp_category' => 'labour_service',
                'milestone_id' => null,
                'milestone' => $allocations->count() ?  $milestoneList : 'No Budget Line',
                'ledger_id' => '',
                'ledger_account' => '',
                'supplier_id' => '',
                'supplier' => 'Employee / Casual',
                'productvar_id' => null,
                'product_code' => null,
                'product_name' => "<a href=". route('biller.casual_remunerations.show', $casualRemun) .">({$tid})</a><br>Casuals Remuneration",
                'description' => $casualRemun->title,
                'date' => dateFormat($casualRemun->date),
                'uom' => 'Mnhrs',
                'qty' => $totalHrs,
                'rate' => 0,
                'amount' => $casualsAmt,
            ]);
        }

        return $expenses;
    }
}
