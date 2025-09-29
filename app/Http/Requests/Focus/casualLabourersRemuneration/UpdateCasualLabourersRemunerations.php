<?php

namespace App\Http\Requests\Focus\casualLabourersRemuneration;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCasualLabourersRemunerations extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        return access()->allow('edit-casual-labourers-remuneration');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => ['required', 'string'],
            'date' => ['required', 'date'],
            'total_amount' => ['required', 'numeric'],
            'period_from' => ['nullable', 'date'],
            'period_to' => ['nullable', 'date'],
            'labour_allocation_id' => ['nullable', 'array', 'min:1'],
            'labour_allocation_id.*' => ['integer', 'exists:labour_allocations,id'],
            'casual_labourer_id' => ['required', 'array', 'min:1'],
            'hours' => ['required', 'array', 'min:1'],
            'overtime_hrs' => ['nullable', 'array', 'min:1'],
            'regular_hrs' => ['nullable', 'array', 'min:1'],
            'wage' => ['required', 'array', 'min:1'],
            'ot_multiplier' => ['nullable', 'array', 'min:1'],
            'overtime_total' => ['nullable', 'array', 'min:1'],
            'regular_total' => ['required', 'array', 'min:1'],
            'wage_subtotal' => ['required', 'array', 'min:1'],
            'wage_item_id' => ['nullable', 'array', 'min:1'],
            'wage_item_total' => ['nullable', 'array', 'min:1'],
            'wage_total' => ['required', 'array', 'min:1'],
            'casual_la_id' => ['required', 'array', 'min:1'],
        ];

        $inputs = $this->all();
        foreach ($inputs as $key => $value) {
            if (strpos($key, 'wage_item') !== false) {
                $rules[$key] = ['nullable', 'array', 'min:1'];
            }
        }

        return $rules;
    }
}
