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
            // Header Information
            'claim_company' => match ($this->claim->claim_company) {
                'WGG' => 'Wegrow Global Sdn. Bhd.',
                'WGE' => 'Wegrow Edutainment (M) Sdn. Bhd.',
                'Both' => 'Wegrow Global Sdn. Bhd. & Wegrow Edutainment (M) Sdn. Bhd.',
                default => $this->claim->claim_company,
            },
            'first_name' => $this->claim->user->first_name,
            'second_name' => $this->claim->user->last_name,
            'claim_month' => $this->claim->created_at->format('F Y'),
            'user_department_name' => $this->claim->user->department_name,

            // Bank & Position Details
            'bank_name' => $this->claim->user->bank_name ?? 'N/A',
            'bank_account' => $this->claim->user->bank_account ?? 'N/A',
            'ic_number' => $this->claim->user->ic_number ?? 'N/A',
            'department' => 'HQ EXECUTIVE',

            // Claim Details
            'date_from' => $this->claim->date_from->format('d/m/Y'),
            'date_to' => $this->claim->date_to->format('d/m/Y'),

            // Signatures
            'prepared_by' => $this->claim->user->first_name . ' ' . $this->claim->user->last_name,
            'verified_by' => 'Nur Shathirah Afiqah Binti Annuar',
            'checked_by' => 'Shahida Shamsudin',
            'reviewed_by' => '',
            'approved_by' => 'Datuk Dr Yong Lam Woei',
            'checked_received_by' => 'Fatin Ayuni',

            // Dates
            'todays_date' => Carbon::now()->format('d/m/Y'),

            // Location details will be handled separately in ClaimExportService
            // as they need to be iterated and placed in multiple rows
        ];
    }
}
