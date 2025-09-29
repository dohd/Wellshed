<?php

namespace App\Http\Requests\Focus\casualLabourersRemuneration;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCLRWage extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'clr_number' => 'required|string|exists:casual_labourers_remunerations,clr_number',
            'casual_labourer_id' => 'required|integer|exists:casual_labourers,id',
            'wage' => 'required|numeric',
            'hours' => 'required|numeric',
            'remuneration' => 'required|numeric'
        ];
    }
}
