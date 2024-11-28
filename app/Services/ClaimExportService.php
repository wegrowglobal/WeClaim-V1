<?php

namespace App\Services;

use App\Models\Claim;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Carbon\Carbon;

class ClaimExportService
{
    protected $startRow = 8;
    protected $mapper;

    public function __construct(ClaimTemplateMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function exportToExcel(Claim $claim)
    {
        $this->mapper = new ClaimTemplateMapper($claim);
        $templatePath = resource_path('templates/claim_template.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $this->fillHeaderInfo($sheet, $claim);
        $this->fillClaimDetails($sheet, $claim);
        $this->fillFooterSection($sheet, $claim);

        $filename = "claim_{$claim->id}_{$claim->created_at->format('Y-m-d')}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    protected function fillHeaderInfo($sheet, $claim)
    {
        $mappings = $this->mapper->getMappings();

        $sheet->setCellValue('A1', 'STAFF CLAIM FOR EXPENSES INCURRED ON OFFICIAL DUTIES FORM - ' . $claim->claim_company);
        $sheet->setCellValue('A3', 'NAME: ' . $mappings['first_name'] . ' ' . $mappings['second_name']);
        $sheet->setCellValue('I3', 'IC NUMBER: ' . $mappings['ic_number']);
        $sheet->setCellValue('R3', 'MONTH: ' . $mappings['claim_month']);
        $sheet->setCellValue('A4', 'BANK NAME: ' . $mappings['bank_name']);
        $sheet->setCellValue('K4', 'PROJECT/DEPARTMENT: ' . $mappings['department']);
        $sheet->setCellValue('A5', 'ACCOUNT NUMBER: ' . $mappings['bank_account']);
        $sheet->setCellValue('K5', 'POSITION: ' . $mappings['user_department_name']);
    }

    protected function fillClaimDetails($sheet, $claim)
    {
        $currentRow = $this->startRow;
        $locations = $claim->locations()->orderBy('order')->get();
        $totalDistance = 0;
        $totalToll = 0;

        foreach ($locations as $index => $location) {
            // Date column
            $sheet->setCellValue('A' . $currentRow, $claim->date_from->format('d/m/Y') . ' - ' . $claim->date_to->format('d/m/Y'));

            // Location details
            $sheet->setCellValue('B' . $currentRow, $location->from_location);
            if ($index > 0) {
                $sheet->setCellValue('B' . ($currentRow + 1), $location->to_location);
            }

            // Distance and toll
            $sheet->setCellValue('E' . $currentRow, $location->distance);
            $sheet->setCellValue('F' . $currentRow, 25.00); // Fixed toll amount

            // Remarks
            $sheet->setCellValue('W' . $currentRow, 'Test');

            $totalDistance += $location->distance;
            $totalToll += 25.00;

            $currentRow += 2; // Increment by 2 to leave space for the next location pair
        }

        // Add date range row
        $sheet->setCellValue('D' . ($currentRow - 1), $claim->date_from->format('d/m/Y') . ' - ' . $claim->date_to->format('d/m/Y'));

        // Add totals
        $sheet->setCellValue('E' . $currentRow, $totalDistance);
        $sheet->setCellValue('F' . $currentRow, $totalToll);
        $sheet->setCellValue('Q' . $currentRow, $totalDistance);
    }

    protected function fillFooterSection($sheet, $claim)
    {
        $mappings = $this->mapper->getMappings();
        $footerStartRow = 12;

        // PREPARED BY row
        $sheet->setCellValue('A' . $footerStartRow, 'PREPARED BY:');
        $sheet->setCellValue('D' . $footerStartRow, 'VERIFIED BY:');
        $sheet->setCellValue('K' . $footerStartRow, 'REVIEWED BY:');

        // Signature row
        $sheet->setCellValue('A14', 'Staff Signature');
        $sheet->setCellValue('D14', 'Manager');
        $sheet->setCellValue('G14', 'HR & Admin');
        $sheet->setCellValue('K14', 'Head of Department');
        $sheet->setCellValue('O14', 'Approved By');
        $sheet->setCellValue('R14', 'Checked & Received By');

        // Names row
        $sheet->setCellValue('A15', 'Name: ' . $mappings['prepared_by']);
        $sheet->setCellValue('D15', 'Name: ' . $mappings['verified_by']);
        $sheet->setCellValue('G15', 'Name: ' . $mappings['checked_by']);
        $sheet->setCellValue('K15', 'Name: ' . $mappings['reviewed_by']);
        $sheet->setCellValue('O15', 'Name: ' . $mappings['approved_by']);
        $sheet->setCellValue('R15', 'Name: ' . $mappings['checked_received_by']);

        // Dates row
        $sheet->setCellValue('A16', 'Date: ' . $mappings['todays_date']);
        $sheet->setCellValue('D16', 'Date: ' . $mappings['todays_date']);
        $sheet->setCellValue('G16', 'Date: ' . $mappings['todays_date']);
        $sheet->setCellValue('K16', 'Date: ' . $mappings['todays_date']);
        $sheet->setCellValue('O16', 'Date: ' . $mappings['todays_date']);
        $sheet->setCellValue('R16', 'Date: ' . $mappings['todays_date']);
    }
}
