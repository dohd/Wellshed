<?php

namespace App\Http\Requests\Focus\lender;

use Illuminate\Foundation\Http\FormRequest;

class CreateLenderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-client');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
         public function rules()
    {
        $rules = array();

        return $rules;
    }

    public function messages()
    {
        return [
            'company.required' => trans('customers.valid_enter'),
        ];
    }
}
