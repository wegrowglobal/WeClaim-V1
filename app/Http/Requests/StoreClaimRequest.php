<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'claim_company' => 'required|string',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'remarks' => 'nullable|string',
            'total_distance' => 'required|numeric|min:0',
            'petrol_amount' => 'required|numeric|min:0',
            'toll_amount' => 'nullable|numeric|min:0',
            'locations' => 'required|array|min:1',
            'locations.*.from_location' => 'required|string',
            'locations.*.to_location' => 'required|string',
            'locations.*.distance' => 'required|numeric|min:0',
            'locations.*.order' => 'required|integer|min:0',
            'toll_file' => 'nullable|file|mimes:pdf|max:10240',
            'email_file' => 'nullable|file|mimes:pdf|max:10240',
        ];
    }
}
