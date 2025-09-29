<?php

namespace App\Http\Responses\Focus\queuerequisition;

use Illuminate\Contracts\Support\Responsable;
use App\Models\project\BudgetItem;
class EditResponse implements Responsable
{
    /**
     * @var App\Models\queuerequisition\queuerequisition
     */
    protected $queuerequisition;

    /**
     * @param App\Models\queuerequisition\queuerequisition $queuerequisition
     */
    public function __construct($queuerequisition)
    {
        $this->queuerequisition = $queuerequisition;
    }

    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $id = $this->queuerequisition->budget_item_id;
        $item = BudgetItem::find($id);
        if ($item) {
            $item_qty = $item->new_qty - $item->issue_qty;
            return view('focus.queuerequisition.edit')->with([
                'queuerequisition' => $this->queuerequisition,
                'item'=>$item_qty
            ]);
        }
        return view('focus.queuerequisition.edit')->with([
            'queuerequisition' => $this->queuerequisition,
            'item'=>'0'
        ]);
        
    }
}