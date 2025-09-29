<?php

namespace App\Models\project\Traits;

/**
 * Class ProjectAttribute.
 */
trait ProjectAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-project", "biller.projects.show")
         . ' ' . $this->getEditButtonAttribute("edit-project", "biller.projects.edit")
         . ' ' . $this->getDeleteButtonAttribute("delete-project", "biller.projects.destroy");
    }
    
    /** Expense Project Gross Profit **/
    // public function getExpProfitMarginAttribute()
    // {
    //     $project = $this;
        
    //     $total_estimate = 0;
    //     $total_balance = 0;
    //     foreach ($project->quotes as $quote) {
    //         $actual_amount = $quote->subtotal;

    //         $dir_purchase_amount = $project->purchase_items->sum('amount') / $project->quotes->count();
    //         $proj_grn_amount = $project->grn_items()->sum(DB::raw('round(rate*qty)')) / $project->quotes->count();
    //         $labour_amount = $project->labour_allocations()->sum(DB::raw('hrs * 500')) / $project->quotes->count();
    //         $expense_amount = $dir_purchase_amount + $proj_grn_amount + $labour_amount;
    //         if ($quote->projectstock) $expense_amount += $quote->projectstock->sum('total');

    //         $balance = $actual_amount - $expense_amount;
    //         // aggregate
    //         // $total_actual += $actual_amount;
    //         $total_estimate += $expense_amount;
    //         $total_balance += $balance;
    //     }
    //     $exp_profit_margin = round(div_num($total_balance, $total_estimate) * 100);
    //     return $exp_profit_margin;
    // }
}
