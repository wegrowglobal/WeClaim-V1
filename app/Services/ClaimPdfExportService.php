<?php

namespace App\Services;

use App\Models\Claim\Claim;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ClaimPdfExportService
{
    protected $claim;
    protected $mapper;
    protected $data;
    protected $pdf;

    // Define claim statuses that allow export
    const EXPORTABLE_STATUSES = [
        'Approved Finance',
        'Done'
    ];

    public function __construct(ClaimTemplateMapper $mapper, Claim $claim)
    {
        if (!in_array($claim->status, self::EXPORTABLE_STATUSES)) {
            throw new \Exception('Only claims with status "Approved Finance" or "Done" can be exported.');
        }

        $this->mapper = $mapper;
        $this->claim = $claim;
        $this->data = $mapper->setClaim($claim)->mapClaimData();
    }

    public function exportToPdf()
    {
        $data = [
            'claim' => $this->claim,
            'data' => $this->data,
            'signatures' => $this->getSignatures(),
            'reviews' => $this->formatReviews(),
            'generated_at' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = PDF::loadView('exports.claim-pdf', $data);
        $pdf->setPaper('a4');

        return $pdf->download('claim_' . $this->claim->id . '.pdf');
    }

    protected function getSignatures()
    {
        return [
            'claim_owner' => [
                'path' => $this->claim->user->signature_path ?? null,
                'name' => $this->claim->user->first_name . ' ' . $this->claim->user->second_name,
                'role' => 'Claim Owner'
            ],
            'admin' => [
                'path' => $this->getSignatureForRole('Admin'),
                'name' => $this->getReviewerName('Admin'),
                'role' => 'Admin'
            ],
            'manager' => [
                'path' => $this->getSignatureForRole('Manager'),
                'name' => $this->getReviewerName('Manager'),
                'role' => 'Manager'
            ],
            'hr' => [
                'path' => $this->getSignatureForRole('HR'),
                'name' => $this->getReviewerName('HR'),
                'role' => 'HR'
            ],
            'datuk' => [
                'path' => 'signatures/signature-datuk.png',
                'name' => 'Datuk',
                'role' => 'Datuk'
            ]
        ];
    }

    protected function formatReviews()
    {
        return collect($this->data['reviews'])->map(function ($review) {
            return [
                'date' => $review['date'],
                'department' => $review['department'],
                'status' => $this->formatApprovalStatus($review['department'], $review['status']),
                'remarks' => $review['remarks']
            ];
        })->all();
    }

    protected function getReviewerName($roleName)
    {
        $review = $this->claim->reviews()
            ->whereHas('reviewer', function ($query) use ($roleName) {
                $query->whereHas('role', function ($q) use ($roleName) {
                    $q->where('name', $roleName);
                });
            })
            ->latest()
            ->first();

        return $review ? $review->reviewer->first_name . ' ' . $review->reviewer->second_name : 'N/A';
    }

    protected function getSignatureForRole($roleName)
    {
        $review = $this->claim->reviews()
            ->whereHas('reviewer', function ($query) use ($roleName) {
                $query->whereHas('role', function ($q) use ($roleName) {
                    $q->where('name', $roleName);
                });
            })
            ->latest()
            ->first();

        return $review ? $review->reviewer->signature_path : null;
    }

    protected function formatApprovalStatus($department, $status)
    {
        $statusMap = [
            'Submitted' => 'Submitted',
            'Approved Admin' => 'Approved by Admin',
            'Approved Manager' => 'Approved by Manager',
            'Approved HR' => 'Approved by HR',
            'Pending Datuk' => 'Pending Datuk Approval',
            'Approved Datuk' => 'Approved by Datuk',
            'Approved Finance' => 'Approved by Finance',
            'Rejected' => 'Rejected',
            'Done' => 'Completed',
            'Cancelled' => 'Cancelled'
        ];

        return $statusMap[$status] ?? $status;
    }
} 