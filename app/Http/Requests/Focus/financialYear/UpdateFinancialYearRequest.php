<?php

namespace App\Http\Requests\Focus\financialYear;

use App\Models\financialYear\FinancialYear;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialYearRequest extends FormRequest
{
    public function authorize()
    {

        if (access()->allow('edit-financial-year')) return true;
        return false;
    }

    public function rules()
    {
        return [
            'start_date' => ['required', 'date', function ($attribute, $value, $fail) {
                $startDate = $value;
                $endDate = $this->end_date;
                $financialYearId = $this->route('financial_year')->id;

                $overlappingFinancialYear = FinancialYear::where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($query) use ($startDate, $endDate) {
                            $query->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                })
                    ->where('id', '!=', $financialYearId)
                    ->first();

                if ($overlappingFinancialYear) {
                    $fail('The financial year range overlaps with an existing financial year. ');
                }
            }],
            'end_date' => 'required|date|after:start_date',
        ];
    }}
