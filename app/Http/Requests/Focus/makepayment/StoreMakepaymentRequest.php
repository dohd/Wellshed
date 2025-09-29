<?php

namespace App\Http\Requests\Focus\makepayment;

use Illuminate\Foundation\Http\FormRequest;

class StoreMakepaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('manage-account');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
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
                'amount_paid' => 'required',
                'tid' => 'required|integer|min:1',
                'account_id' => 'required|integer|min:1',
                //'term_id' => 'required|integer|min:1',
            ];
        }
        return $rules;
    }

    public function messages()
    {
        return [
            //'supplier_id.min' => trans('suppliers.valid_enter'),
            'tid.required' => trans('invoices.invalid_number'),
            'tid.min' => trans('invoices.invalid_number'),
           // 'term_id.required' => trans('terms.required'),
        ];
    }
}
