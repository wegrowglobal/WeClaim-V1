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
            'claim_company' => 'required|string|in:WGG,WGE,WGG & WGE',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'remarks' => 'nullable|string',
            'total_distance' => 'required|numeric|min:0',
            'petrol_amount' => 'required|numeric|min:0',
            'toll_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:Submitted',
            'title' => 'required|string|max:255',
            'claim_type' => 'required|string|in:Petrol',
            
            // Validate locations array
            'locations' => 'required|json',
            
            // File validations
            'toll_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'email_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ];
    }

    public function messages(): array
    {
        return [
            'claim_company.in' => 'The selected company is invalid.',
            'date_to.after_or_equal' => 'The end date must be after or equal to the start date.',
            'toll_file.required' => 'The toll receipt file is required.',
            'toll_file.mimes' => 'The toll receipt must be a PDF file.',
            'email_file.required' => 'The email approval file is required.',
            'email_file.mimes' => 'The email approval must be a PDF file.',
            'locations.required' => 'At least one location must be specified.',
            'locations.json' => 'The locations data is invalid.',
        ];
    }
}
