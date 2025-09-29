<?php

namespace App\Http\Requests\Focus\calendar;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCalendarEventRequest extends FormRequest
{
    public function authorize()
    {
        return access()->allow('edit-calendar-events'); // Adjust authorization as needed
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'location' => ['required', 'string', 'max:255'],
            'organizer' => ['required', 'exists:users,id'],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*' => ['exists:users,id'],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'color' => ['required', 'string', 'size:7'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        // Format the response to include success: false
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $errors,
        ], 400));
    }
}