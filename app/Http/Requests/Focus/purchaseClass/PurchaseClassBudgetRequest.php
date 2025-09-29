<?php

namespace App\Http\Requests\Focus\purchaseClass;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseClassBudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Return true if you don't have specific authorization logic
        return access()->allow('create-purchase-class-budget') || access()->allow('edit-purchase-class-budget');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'purchase_class_id' => ['required', 'numeric', 'exists:purchase_classes,id'],
            'classlist_id' => ['nullable', 'numeric', 'exists:classlists,id'],
            'description' => ['nullable', 'string'],
            'financial_year_id' => ['required', 'integer', 'exists:financial_years,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'january' => ['required', 'numeric', 'min:0'],
            'february' => ['required', 'numeric', 'min:0'],
            'march' => ['required', 'numeric', 'min:0'],
            'april' => ['required', 'numeric', 'min:0'],
            'may' => ['required', 'numeric', 'min:0'],
            'june' => ['required', 'numeric', 'min:0'],
            'july' => ['required', 'numeric', 'min:0'],
            'august' => ['required', 'numeric', 'min:0'],
            'september' => ['required', 'numeric', 'min:0'],
            'october' => ['required', 'numeric', 'min:0'],
            'november' => ['required', 'numeric', 'min:0'],
            'december' => ['required', 'numeric', 'min:0'],
            'budget' => ['required', 'numeric', 'min:0', 'gte:january,february,march,april,may,june,july,august,september,october,november,december'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'The name is required.',
            'name.unique' => 'The name must be unique.',
            'financial_year_id.exists' => 'The selected financial year is invalid.',
            'budget.gte' => 'The total budget must be greater than or equal to the sum of all monthly budgets.',
        ];
    }
}
