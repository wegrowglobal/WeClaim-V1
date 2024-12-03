<?php

namespace App\Services;

use App\Models\Claim;
use Carbon\Carbon;

class ClaimTemplateMapper
{
    protected $claim;

    public function __construct(Claim $claim)
    {
        $this->claim = $claim;
    }

    public function getMappings(): array
    {
        return [
            // Company Header
            'claim_company' => $this->formatCompanyName($this->claim->claim_company),

            // Employee Details
            'employee_name' => $this->formatFullName($this->claim->user->first_name, $this->claim->user->last_name),
            'department' => $this->claim->user->department ?? 'N/A',
            'submitted_date' => $this->claim->submitted_at->format('d/m/Y'),

            // Department Details
            'department_name' => $this->claim->user->department ?? 'N/A',

            // Claim Period
            'claim_period' => sprintf(
                '%s to %s',
                $this->claim->date_from->format('d/m/Y'),
                $this->claim->date_to->format('d/m/Y')
            ),

            // Location Details (for each row)
            'date_from' => $this->claim->date_from->format('d/m/Y'),
            'date_to' => $this->claim->date_to->format('d/m/Y'),
            'from_location' => '',  // Will be populated in loop
            'to_location' => '',    // Will be populated in loop
            'distance' => '',       // Will be populated in loop
            'toll_amount' => number_format($this->claim->toll_amount, 2),
            'parking_amount' => '0.00',
            'remarks' => $this->claim->remarks ?? '',

            // Approval Information
            'approved_by_admin_date' => $this->getApprovalDate('Admin'),
            'approved_by_datuk_date' => $this->getApprovalDate('Datuk'),
            'approved_by_hr_date' => $this->getApprovalDate('HR'),
            'approved_by_finance_date' => $this->getApprovalDate('Finance'),
        ];
    }

    private function formatCompanyName(string $code): string
    {
        return match ($code) {
            'WGG' => 'WEGROW GLOBAL SDN. BHD.',
            'WGE' => 'WEGROW EDUTAINMENT (M) SDN. BHD.',
            'Both' => 'WEGROW GLOBAL SDN. BHD. & WEGROW EDUTAINMENT (M) SDN. BHD.',
            default => $code,
        };
    }

    private function formatFullName(?string $firstName, ?string $lastName): string
    {
        $parts = array_filter([$firstName, $lastName], fn($part) => !empty($part));
        return implode(' ', $parts) ?: 'N/A';
    }

    private function getApprovalDate(string $department): string
    {
        $review = $this->claim->reviews()
            ->where('department', $department)
            ->whereNotNull('reviewed_at')
            ->orderBy('reviewed_at', 'desc')
            ->first();

        return $review ? $review->reviewed_at->format('d/m/Y') : 'N/A';
    }
}
