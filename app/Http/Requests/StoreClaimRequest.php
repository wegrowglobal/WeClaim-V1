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
            'remarks' => 'required|string',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
            'location' => 'required|array|min:2',
            'location.*' => 'required|string|max:255',
            'distances' => 'required|array|min:1',
            'distances.*' => 'required|numeric|min:0',
            'total_distance' => ['required', 'numeric', 'regex:/^\d+(\.\d{0,2})?$/'],
            'toll_amount' => 'required|numeric',
            'toll_report' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'email_report' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'location.required' => 'At least two locations are required.',
            'location.array' => 'Locations must be an array.',
            'location.min' => 'Please provide at least two locations.',
            'location.*.required' => 'Each location is required.',
            'distances.required' => 'Distances are required between locations.',
            'distances.array' => 'Distances must be an array.',
            'distances.min' => 'Please provide distances between locations.',
            'distances.*.required' => 'Each distance is required.',
            'distances.*.numeric' => 'Each distance must be a valid number.',
            'distances.*.min' => 'Each distance must be at least 0 km.',
        ];
    }
}
