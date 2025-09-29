<?php

namespace App\Http\Requests\Focus\transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-transaction');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */


      public function rules()
    {
        $rules = array();
        if ($this->getMethod() == 'POST') {
            $rules = [
            'tid' => 'required|integer|min:1',
            'note' => 'required',
            'transaction_date' => 'required',
            'total_debit' => 'required|same:total_credit',
            ];
        }
        return $rules;
    }

        public function messages()
    {
        return [
            'account_id.min' => trans('accounts.valid_enter'),
            'trans_category_id.required' => trans('transactioncategories.valid_enter'),
            'debit.required' => trans('transactions.debit_invalid'),
            'credit.required' => trans('transactions.credit_invalid'),
        ];
    }
}
