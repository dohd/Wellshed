<?php

namespace App\Http\Responses\Focus\banktransfer;

use App\Models\account\Account;
use App\Models\casual\CasualLabourer;
use App\Models\hrm\Hrm;
use App\Models\third_party_user\ThirdPartyUser;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\productcategory\Productcategory
     */
    protected $banktransfer;

    /**
     * @param App\Models\productcategory\Productcategory $productcategories
     */
    public function __construct($banktransfer)
    {
        $this->banktransfer = $banktransfer;
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
        $tid = $this->banktransfer->tid;
        // banks, cash on-hand, credit-cards, misc assets
        $accounts = Account::whereHas('account_type_detail', fn($q) =>  $q->whereIn('system_rel', ['bank', 'cash', 'credit_card'])->orWhere('system', 'other_current_assets'))
            ->whereHas('currency')
            ->with(['currency' => fn($q) => $q->select('id', 'code', 'rate')->get()])
            ->with(['transactions' => fn($q) => $q->selectRaw('account_id, SUM(debit) debit, SUM(credit) credit, SUM(fx_debit) fx_debit, SUM(fx_credit) fx_credit')->groupBy('account_id')])
            ->get(['id', 'holder', 'currency_id', 'account_type','system'])
            ->map(function($v) {
                $balance = 0;
                $tr = $v->transactions->first();
                if ($tr) {
                    if (in_array($v->account_type, ['Asset', 'Expense'])) {
                        if ($v->currency->rate == 1) $balance = round($tr->debit - $tr->credit, 2);
                        else $balance = round($tr->fx_debit - $tr->fx_credit, 2);
                    } else {
                        if ($v->currency->rate == 1) $balance = round($tr->credit - $tr->debit, 2);
                        else $balance = round($tr->fx_credit - $tr->fx_debit, 2);
                    }
                }
                $v['balance'] = $balance;
                return $v;
            });
        $employees = Hrm::all();
        $third_party_users = ThirdPartyUser::all();
        $casuals = CasualLabourer::all();
        
        return view('focus.banktransfers.edit', compact('tid', 'accounts','employees','casuals','third_party_users'))->with([ 'banktransfer' => $this->banktransfer]);
    }
}
