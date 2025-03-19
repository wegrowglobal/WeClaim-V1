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

        $department = $this->claim->user->role ? $this->formatDepartment($this->claim->user->role->name) : 'N/A';

        // Calculate total accommodation cost
        $accommodationCost = $this->claim->accommodations()->sum('price');

        return [
            'claim_id' => $this->claim->id,
            'employee_name' => $this->claim->user->first_name . ' ' . $this->claim->user->second_name,
            'department' => $department,
            'date_from' => $this->claim->date_from->format('d/m/Y'),
            'date_to' => $this->claim->date_to->format('d/m/Y'),
            'total_distance' => number_format($this->claim->total_distance, 2),
            'petrol_amount' => number_format($this->claim->petrol_amount, 2),
            'toll_amount' => number_format($this->claim->toll_amount, 2),
            'accommodation_cost' => number_format($accommodationCost, 2),
            'total_amount' => number_format(
                $this->claim->petrol_amount + 
                $this->claim->toll_amount + 
                $accommodationCost, 
                2
            ),
            'description' => $this->claim->description,
            'status' => match ($this->claim->status) {
                'APPROVED_ADMIN' => 'Approved Admin',
                'APPROVED_FINANCE' => 'Approved Finance',
                'APPROVED_DATUK' => 'Approved Datuk',
                'APPROVED_HR' => 'Approved HR',
                'REJECTED_ADMIN' => 'Rejected Admin',
                'REJECTED_FINANCE' => 'Rejected Finance',
                'REJECTED_DATUK' => 'Rejected Datuk',
                'REJECTED_HR' => 'Rejected HR',
                default => ucwords(strtolower(str_replace('_', ' ', $this->claim->status)))
            },
            'submitted_at' => $this->claim->submitted_at->format('d/m/Y'),
            'locations' => $this->claim->locations->map(function ($location) {
                return [
                    'from' => $location->from_location,
                    'to' => $location->to_location,
                    'distance' => number_format($location->distance, 2)
                ];
            })->toArray(),
            'reviews' => $this->mapReviews(),
            'company' => match ($this->claim->claim_company) {
                'WGE' => 'Wegrow Edutainment (M) Sdn. Bhd.',
                'WGG' => 'Wegrow Global Sdn. Bhd.',
                'WGS' => 'Wegrow Studios Sdn. Bhd.',
                'WGG & WGE' => 'Wegrow Global Sdn. Bhd. & Wegrow Edutainment (M) Sdn. Bhd.',
                default => ''
            },
        ];
    }

    protected function mapReviews()
    {
        return $this->claim->reviews()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($review) {
                // Special handling for Datuk/Management approval
                if ($review->department === 'Management') {
                    return [
                        'date' => $review->created_at->format('d/m/Y H:i'),
                        'department' => 'Management',
                        'status' => 'Approved Datuk',
                        'remarks' => $review->remarks ?? '-'
                    ];
                }

                // For Finance department, check if it's the final approval
                if ($review->department === 'Finance' && $this->claim->status === 'Done') {
                    return [
                        'date' => $review->created_at->format('d/m/Y H:i'),
                        'department' => 'Finance',
                        'status' => 'Payment Done',
                        'remarks' => $review->remarks ?? '-'
                    ];
                }

                // For all other departments
                return [
                    'date' => $review->created_at->format('d/m/Y H:i'),
                    'department' => $review->department,
                    'status' => $this->formatReviewStatus($review->status, $review->department),
                    'remarks' => $review->remarks ?? '-'
                ];
            })
            ->unique(function ($review) {
                // Make unique by department to avoid duplicates
                return $review['department'];
            })
            ->values()
            ->toArray();
    }

    protected function formatReviewStatus($status, $department)
    {
        // Convert status to lowercase for consistent comparison
        $status = strtolower($status);

        if ($department === 'Management') {
            return 'Approved Datuk';
        }

        if ($status === 'approved') {
            return "Approved by {$department}";
        }

        if ($department === 'Finance' && $status === 'payment done') {
            return 'Payment Done';
        }

        return ucfirst($status);
    }

    protected function formatDepartment(string $department): string
    {
        return match (strtolower($department)) {
            'hr' => 'HR',
            'email approval' => 'Email Approval',
            default => ucwords(strtolower($department))
        };
    }
}
