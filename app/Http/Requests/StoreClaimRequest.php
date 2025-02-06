<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

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
            'accommodations.*.location' => 'required_if:accommodations.*.check_in,!=,null|string|max:255',
            'accommodations.*.check_in' => 'required_if:accommodations.*.location,!=,null|date',
            'accommodations.*.check_out' => 'required_if:accommodations.*.check_in,!=,null|date|after_or_equal:accommodations.*.check_in',
            'accommodations.*.price' => 'required_if:accommodations.*.location,!=,null|numeric|min:0',
            'accommodations.*.receipt' => 'sometimes|file|mimes:pdf,jpg,jpeg,png|max:2048',
            
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

    protected function prepareForValidation()
    {
        $accommodations = $this->input('accommodations');
        $draftData = $this->input('draft_data');
        
        Log::info('Preparing accommodation data', [
            'raw_accommodations' => $accommodations,
            'has_draft_data' => !empty($draftData)
        ]);
        
        // Try to get accommodations from draft data first
        if (!empty($draftData)) {
            try {
                $draftDataArray = is_string($draftData) ? json_decode($draftData, true) : $draftData;
                if (isset($draftDataArray['accommodations']) && !empty($draftDataArray['accommodations'])) {
                    $accommodations = $draftDataArray['accommodations'];
                    Log::info('Using accommodations from draft data', [
                        'accommodations' => $accommodations
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error parsing draft data', [
                    'error' => $e->getMessage(),
                    'draft_data' => $draftData
                ]);
            }
        }
        
        // If accommodations is a string (JSON), decode it
        if (is_string($accommodations)) {
            try {
                $accommodations = json_decode($accommodations, true);
                Log::info('Decoded accommodations from JSON', [
                    'accommodations' => $accommodations
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to decode accommodations JSON', [
                    'error' => $e->getMessage(),
                    'raw_data' => $accommodations
                ]);
                $accommodations = [];
            }
        }
        
        // If accommodations is null or not an array, set to empty array
        if (!is_array($accommodations)) {
            Log::warning('Accommodations data is not an array, setting to empty array', [
                'type' => gettype($accommodations)
            ]);
            $accommodations = [];
        }
        
        // Get uploaded receipt files
        $accommodationReceipts = $this->file('accommodation_receipts') ?? [];
        
        // Filter and validate accommodations
        $validAccommodations = [];
        
        foreach ($accommodations as $index => $accommodation) {
            // Skip if not an array
            if (!is_array($accommodation)) {
                Log::warning('Skipping invalid accommodation entry - not an array', [
                    'index' => $index,
                    'type' => gettype($accommodation)
                ]);
                continue;
            }
            
            // Validate required fields
            $requiredFields = ['location', 'check_in', 'check_out', 'price'];
            $missingFields = array_filter($requiredFields, function($field) use ($accommodation) {
                return !isset($accommodation[$field]) || 
                       (is_string($accommodation[$field]) && trim($accommodation[$field]) === '') ||
                       (is_numeric($accommodation[$field]) && (float)$accommodation[$field] <= 0);
            });
            
            if (!empty($missingFields)) {
                Log::warning('Skipping invalid accommodation entry - missing or invalid required fields', [
                    'index' => $index,
                    'missing_fields' => $missingFields,
                    'accommodation' => $accommodation
                ]);
                continue;
            }
            
            try {
                // Create valid accommodation entry with proper type casting
                $validAccommodation = [
                    'location' => trim($accommodation['location']),
                    'check_in' => date('Y-m-d', strtotime($accommodation['check_in'])),
                    'check_out' => date('Y-m-d', strtotime($accommodation['check_out'])),
                    'price' => (float) $accommodation['price']
                ];
                
                // Validate dates
                $checkIn = new \DateTime($validAccommodation['check_in']);
                $checkOut = new \DateTime($validAccommodation['check_out']);
                $dateFrom = new \DateTime($this->input('date_from'));
                $dateTo = new \DateTime($this->input('date_to'));
                
                if ($checkIn > $checkOut || $checkIn < $dateFrom || $checkOut > $dateTo) {
                    Log::warning('Skipping invalid accommodation entry - invalid dates', [
                        'index' => $index,
                        'check_in' => $validAccommodation['check_in'],
                        'check_out' => $validAccommodation['check_out'],
                        'claim_date_from' => $this->input('date_from'),
                        'claim_date_to' => $this->input('date_to')
                    ]);
                    continue;
                }
                
                // Handle receipt file if present in the uploaded files
                if (isset($accommodationReceipts[$index]) && $accommodationReceipts[$index]->isValid()) {
                    $validAccommodation['receipt'] = $accommodationReceipts[$index];
                }
                
                $validAccommodations[] = $validAccommodation;
                
                Log::info('Added valid accommodation entry', [
                    'index' => $index,
                    'accommodation' => $validAccommodation
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error processing accommodation entry', [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'accommodation' => $accommodation
                ]);
                continue;
            }
        }
        
        // Merge the validated data
        $this->merge([
            'accommodations' => $validAccommodations
        ]);
        
        Log::info('Prepared accommodations data', [
            'valid_count' => count($validAccommodations),
            'valid_accommodations' => $validAccommodations
        ]);
    }
}
