<?php

namespace App\Services;

use App\Models\Claim;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;

class ClaimExportService
{
    private const TEMPLATE_CELLS = [
        'COMPANY_NAME' => 'B1',
        'EMPLOYEE_NAME' => 'B4',
        'DEPARTMENT' => 'E4',
        'SUBMITTED_DATE' => 'G4',
        'CLAIM_PERIOD' => 'B5',
        'DEPARTMENT_NAME' => 'E5',
        'DATE_RANGE' => 'A9',
        'DETAILS' => 'B9',
        'PETROL' => 'C9',
        'TOLL' => 'D9',
        'PARKING' => 'E9',
        'TOTAL' => 'F9',
        'REMARKS' => 'G9',
        'APPROVED_BY_ADMIN_DATE' => 'A15',
        'APPROVED_BY_DATUK_DATE' => 'B15',
        'APPROVED_BY_HR_DATE' => 'E15',
        'APPROVED_BY_FINANCE_DATE' => 'F15'
    ];

    protected $mapper;
    protected $spreadsheet;
    protected $sheet;

    public function __construct(ClaimTemplateMapper $mapper)
    {
        $this->mapper = $mapper;
        $templatePath = resource_path('templates/travel_claim_template.xlsx');
        $this->spreadsheet = IOFactory::load($templatePath);
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

    public function exportToExcel(Claim $claim)
    {
        $this->fillHeaderInfo($claim);
        $this->fillClaimDetails($claim);
        $this->applyStyles();
        return $this->outputFile($claim);
    }

    private function fillHeaderInfo(Claim $claim)
    {
        $mappings = $this->mapper->getMappings();

        // Set company name in header
        $this->sheet->setCellValue('C1', 'TRAVEL CLAIM FORM');
        $this->sheet->setCellValue('C2', $mappings['claim_company']);

        foreach (self::TEMPLATE_CELLS as $key => $cell) {
            if (isset($mappings[strtolower($key)])) {
                $this->sheet->setCellValue($cell, $mappings[strtolower($key)]);
            }
        }
    }

    private function fillClaimDetails(Claim $claim)
    {
        $currentRow = 9;
        $locations = $claim->locations()->orderBy('order')->get();
        $templateStyle = $this->sheet->getStyle("A9:G9");
        $totalRowPosition = 9 + count($locations);

        foreach ($locations as $index => $location) {
            if ($index > 0) {
                // Insert new row before total
                $this->sheet->insertNewRowBefore($currentRow);
                $this->sheet->duplicateStyle($templateStyle, "A{$currentRow}:G{$currentRow}");
            }

            $this->sheet->setCellValue("A{$currentRow}", $claim->date_from->format('d/m/Y'));
            $this->sheet->setCellValue("B{$currentRow}", "{$location->from_location} → {$location->to_location}");
            $this->sheet->setCellValue("C{$currentRow}", number_format($location->distance, 2));
            $this->sheet->setCellValue("D{$currentRow}", number_format($claim->toll_amount, 2));
            $this->sheet->setCellValue("E{$currentRow}", '0.00');
            $this->sheet->setCellValue("F{$currentRow}", "=SUM(C{$currentRow}:E{$currentRow})");
            $this->sheet->setCellValue("G{$currentRow}", $claim->remarks ?? '');

            $currentRow++;
        }

        // Fill totals row at the end
        $this->fillTotalsRow($currentRow);
    }

    private function fillTotalsRow($currentRow)
    {
        $this->sheet->setCellValue("B{$currentRow}", 'TOTAL');
        $this->sheet->setCellValue("C{$currentRow}", "=SUM(C9:C" . ($currentRow - 1) . ")");
        $this->sheet->setCellValue("D{$currentRow}", "=SUM(D9:D" . ($currentRow - 1) . ")");
        $this->sheet->setCellValue("E{$currentRow}", "=SUM(E9:E" . ($currentRow - 1) . ")");
        $this->sheet->setCellValue("F{$currentRow}", "=SUM(F9:F" . ($currentRow - 1) . ")");
    }

    private function outputFile(Claim $claim)
    {
        $filename = "travel_claim_{$claim->id}_{$claim->created_at->format('Y-m-d')}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    private function applyStyles()
    {
        $lastRow = $this->sheet->getHighestRow();

        // Apply number formatting to numeric columns
        $this->sheet->getStyle("C9:F{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        // Center align dates
        $this->sheet->getStyle("A9:A{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Right align numbers
        $this->sheet->getStyle("C9:F{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
}
