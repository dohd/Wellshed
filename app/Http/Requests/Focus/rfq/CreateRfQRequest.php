<?php

namespace App\Http\Requests\Focus\rfq;

use Illuminate\Foundation\Http\FormRequest;

class CreateRfQRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-rfq');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        [
            //
        ];
    }
}
