<?php

namespace App\Http\Requests\Focus\documentManager;

use App\Models\hrm\Hrm;
use App\Models\Procurement\Inventory\InventoryPrfq;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentManagerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        return access()->allow('create-document-tracker');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'in:LICENSE,CONTRACT,CERTIFICATE,POLICY,AGREEMENT'],
            'description' => ['required', 'string'],
            'responsible' => ['required', 'exists:users,id',
                function (string $attribute, $value, Closure $fail) {

                    $responsibleUser = Hrm::where('id', $value)->first();

                    if (!$responsibleUser || !filter_var($responsibleUser->email, FILTER_VALIDATE_EMAIL)) {
                        $fail('Action Denied! The Primary Responsible User Must Have a Valid Email Address');
                    }
                },
            ],
            'co_responsible' => ['required', 'exists:users,id',
                function (string $attribute, $value, Closure $fail) {

                    $coResponsibleUser = Hrm::where('id', $value)->first();

                    if (!$coResponsibleUser || !filter_var($coResponsibleUser->email, FILTER_VALIDATE_EMAIL)) {
                        $fail('Action Denied! The Secondary Responsible User Must Have a Valid Email Address');
                    }
                },
            ],
            'issuing_body' => ['required', 'string', 'max:255'],
            'issue_date' => ['required', 'date', 'before:renewal_date', 'before:expiry_date'],
            'cost_of_renewal' => ['required', 'numeric', 'min:0'],
            'renewal_date' => ['required', 'date', 'after:issue_date'],
            'expiry_date' => ['required', 'date', 'after:issue_date'],
            'alert_days_before' => ['required', 'integer', 'min:0', 'max:30'],
            'status' => ['required', 'in:ACTIVE,EXPIRED,ARCHIVED'],
        ];
    }
}
