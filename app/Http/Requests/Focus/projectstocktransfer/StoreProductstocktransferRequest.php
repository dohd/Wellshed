<?php

namespace App\Http\Requests\Focus\projectstocktransfer;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductstocktransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-stock-transfer');
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
                'project_id' => 'required|integer|min:1',
                'tid' => 'required|integer|min:1',
                'total' => 'required',
                
            ];
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'project_id.min' => 'Make sure project is selected',
            'tid.required' => trans('invoices.invalid_number'),
            'tid.min' => trans('invoices.invalid_number'),
            //'term_id.required' => trans('terms.required'),
        ];
    }
}
