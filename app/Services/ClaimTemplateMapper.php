<?php

namespace App\Services;

use App\Models\Claim;

class ClaimTemplateMapper
{
    protected $claim;

    public function setClaim(Claim $claim)
    {
        $this->claim = $claim;
        return $this;
    }

    public function mapClaimData(): array
    {
        if (!$this->claim) {
            throw new \RuntimeException('Claim must be set before mapping data');
        }

        // Get department name from user's role
        $department = $this->claim->user->role ? $this->claim->user->role->name : 'N/A';

        return [
            'claim_id' => $this->claim->id,
            'employee_name' => $this->claim->user->first_name . ' ' . $this->claim->user->last_name,
            'department' => $department,
            'date_from' => $this->claim->date_from->format('d/m/Y'),
            'date_to' => $this->claim->date_to->format('d/m/Y'),
            'total_distance' => number_format($this->claim->total_distance, 2),
            'petrol_amount' => number_format($this->claim->petrol_amount, 2),
            'toll_amount' => number_format($this->claim->toll_amount, 2),
            'total_amount' => number_format($this->claim->petrol_amount + $this->claim->toll_amount, 2),
            'description' => $this->claim->description,
            'status' => ucfirst(strtolower(str_replace('_', ' ', $this->claim->status))),
            'submitted_at' => $this->claim->submitted_at->format('d/m/Y'),
            'locations' => $this->claim->locations->map(function ($location) {
                return [
                    'from' => $location->from_location,
                    'to' => $location->to_location,
                    'distance' => number_format($location->distance, 2)
                ];
            })->toArray(),
            'reviews' => $this->claim->reviews
                ->sortBy('created_at')
                ->take(4) // Limit to last 4 reviews to ensure it fits on one page
                ->map(function ($review) {
                    return [
                        'date' => $review->created_at->format('d/m/Y'),
                        'department' => ucfirst($review->department),
                        'status' => ucfirst(strtolower(str_replace('_', ' ', $review->status))),
                        'remarks' => $review->remarks
                    ];
                })->toArray()
        ];
    }
}
