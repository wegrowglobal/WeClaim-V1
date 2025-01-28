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
            
            // Validate accommodations
            'accommodations' => 'nullable|array',
            'accommodations.*.location' => 'required_with:accommodations|string',
            'accommodations.*.price' => 'required_with:accommodations|numeric|min:0',
            'accommodations.*.check_in' => 'required_with:accommodations|date|after_or_equal:date_from|before_or_equal:date_to',
            'accommodations.*.check_out' => 'required_with:accommodations|date|after_or_equal:accommodations.*.check_in|before_or_equal:date_to',
            'accommodations.*.receipt' => 'required_with:accommodations|file|mimes:pdf,jpg,jpeg,png|max:10240',
            
            // File validations
            'toll_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'email_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
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
            'accommodations.*.check_in.after_or_equal' => 'Check-in date must be within the claim period.',
            'accommodations.*.check_in.before_or_equal' => 'Check-in date must be within the claim period.',
            'accommodations.*.check_out.after_or_equal' => 'Check-out date must be after or equal to check-in date.',
            'accommodations.*.check_out.before_or_equal' => 'Check-out date must be within the claim period.',
        ];
    }
}
