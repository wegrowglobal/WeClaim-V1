<?php

namespace App\Services;

use FPDF;
use App\Models\Claim;

class ClaimPdfExportService extends FPDF
{
    protected $claim;
    protected $mapper;
    protected $data;
    protected $pageWidth = 210; // A4 width in mm
    protected $margin = 20; // Equal margins on all sides
    protected $effectiveWidth;

    public function __construct(ClaimTemplateMapper $mapper, Claim $claim)
    {
        parent::__construct();
        $this->mapper = $mapper;
        $this->claim = $claim;
        $this->data = $this->mapper->setClaim($claim)->mapClaimData();
        $this->effectiveWidth = $this->pageWidth - (2 * $this->margin);
    }

    public function exportToPdf()
    {
        $this->AddPage();
        $this->SetMargins($this->margin, $this->margin);
        $this->SetAutoPageBreak(true, $this->margin);

        $this->generateHeader();
        $this->generateClaimDetails();
        $this->generateTripDetails();
        $this->generateFinancialSummary();
        $this->generateApprovalHistory();
        $this->generateFooter();

        return $this->Output('D', 'claim_' . $this->claim->id . '.pdf');
    }

    protected function generateHeader()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Travel Claim Report', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'Claim ID: ' . $this->data['claim_id'], 0, 1, 'C');
    }

    protected function generateClaimDetails()
    {
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, 'Claim Information', 0, 1, 'L');

        $this->SetFont('Arial', '', 9);
        $details = [
            ['Employee Name', $this->data['employee_name']],
            ['Department', $this->data['department']],
            ['Claim Period', $this->data['date_from'] . ' to ' . $this->data['date_to']],
            ['Status', $this->data['status']],
            ['Submission Date', $this->data['submitted_at']]
        ];

        foreach ($details as $detail) {
            $this->Cell(35, 6, $detail[0] . ':', 0, 0);
            $this->Cell(0, 6, $detail[1], 0, 1);
        }
    }

    protected function generateTripDetails()
    {
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, 'Trip Details', 0, 1, 'L');
        $this->Ln(2);

        // Column widths
        $colWidths = [10, 75, 75, 20];

        // Headers
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(245, 245, 245);
        $this->Cell($colWidths[0], 7, 'No.', 1, 0, 'C', true);
        $this->Cell($colWidths[1], 7, 'From', 1, 0, 'C', true);
        $this->Cell($colWidths[2], 7, 'To', 1, 0, 'C', true);
        $this->Cell($colWidths[3], 7, 'KM', 1, 1, 'C', true);

        // Content
        $this->SetFont('Arial', '', 8);
        $validLocations = array_values(array_filter($this->data['locations'], function ($location) {
            return !empty($location['from']) && !empty($location['to']);
        }));

        foreach ($validLocations as $index => $location) {
            $startX = $this->GetX();
            $startY = $this->GetY();

            // Calculate required height for wrapped text
            $fromHeight = $this->getMultiCellHeight($colWidths[1], 5, $location['from']);
            $toHeight = $this->getMultiCellHeight($colWidths[2], 5, $location['to']);
            $rowHeight = max($fromHeight, $toHeight, 10);

            // Draw number cell
            $this->Cell($colWidths[0], $rowHeight, $index + 1, 1, 0, 'C');

            // Store current position
            $currentX = $this->GetX();
            $currentY = $this->GetY();

            // Draw all cells with same height
            $this->MultiCell($colWidths[1], $rowHeight / 2, $location['from'], 1, 'L');
            $this->SetXY($currentX + $colWidths[1], $currentY);

            $this->MultiCell($colWidths[2], $rowHeight / 2, $location['to'], 1, 'L');
            $this->SetXY($currentX + $colWidths[1] + $colWidths[2], $currentY);

            $this->Cell($colWidths[3], $rowHeight, $location['distance'], 1, 1, 'R');
        }

        $this->Ln(5);
    }

    protected function getMultiCellHeight($w, $h, $txt)
    {
        $lines = ceil(strlen($txt) * $this->FontSize / ($w * 2));
        return $h * $lines;
    }

    protected function generateFinancialSummary()
    {
        $this->Ln(3); // Add space before section
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, 'Financial Summary', 0, 1, 'L');

        $this->SetFont('Arial', '', 9);
        $summary = [
            ['Total Distance', $this->data['total_distance'] . ' KM'],
            ['Petrol Amount', 'RM ' . $this->data['petrol_amount']],
            ['Toll Amount', 'RM ' . $this->data['toll_amount']],
            ['Total Amount', 'RM ' . $this->data['total_amount']]
        ];

        foreach ($summary as $item) {
            $this->Cell(35, 6, $item[0] . ':', 0, 0);
            $this->Cell(0, 6, $item[1], 0, 1);
        }
        $this->Ln(5); // Add space after section
    }

    protected function generateApprovalHistory()
    {
        $this->Ln(3); // Add space before section
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, 'Approval History', 0, 1, 'L');

        $colWidths = [25, 35, 35, 85];

        // Headers
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(245, 245, 245);
        $this->Cell($colWidths[0], 7, 'Date', 1, 0, 'C', true);
        $this->Cell($colWidths[1], 7, 'Department', 1, 0, 'C', true);
        $this->Cell($colWidths[2], 7, 'Status', 1, 0, 'C', true);
        $this->Cell($colWidths[3], 7, 'Remarks', 1, 1, 'C', true);

        // Content
        $this->SetFont('Arial', '', 8);
        foreach ($this->data['reviews'] as $review) {
            $startX = $this->GetX();
            $startY = $this->GetY();

            // Format status with proper capitalization
            $status = preg_replace_callback('/\b(hr)\b/i', function ($matches) {
                return strtoupper($matches[1]);
            }, ucwords(strtolower($review['status'])));

            // Calculate height needed for remarks
            $remarkHeight = $this->getMultiCellHeight($colWidths[3], 5, $review['remarks']);
            $rowHeight = max($remarkHeight, 7); // Minimum 7mm height

            // Draw cells with uniform height
            $this->Cell($colWidths[0], $rowHeight, $review['date'], 1, 0, 'C');
            $this->Cell($colWidths[1], $rowHeight, $review['department'], 1, 0, 'C');
            $this->Cell($colWidths[2], $rowHeight, $status, 1, 0, 'C');

            // Draw remarks
            $this->MultiCell($colWidths[3], $rowHeight, $review['remarks'], 1, 'L');

            // Move to next row
            $this->SetY($startY + $rowHeight);
        }
        $this->Ln(5); // Add space after section
    }

    protected function generateFooter()
    {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Generated on ' . date('d/m/Y H:i:s'), 0, 0, 'C');
    }
}
