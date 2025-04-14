<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Claim\Claim;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ClaimExcelExportService
{
    protected $claim;
    protected $mapper;
    protected $data;
    protected $spreadsheet;
    protected $sheet;
    protected $currentRow = 1;

    // Define colors
    const HEADER_BG_COLOR = 'F3F4F6';
    const SECTION_BG_COLOR = '000000';
    const TOTAL_BG_COLOR = 'EBF5FF';

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
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->setupSheet();
    }

    protected function setupSheet()
    {
        // Set worksheet name
        $this->sheet->setTitle('Claim - ' . $this->claim->id);

        // Set default font
        $this->spreadsheet->getDefaultStyle()->getFont()
            ->setName('Calibri')
            ->setSize(10);

        // Set column widths - adjust to prevent text wrapping
        $this->sheet->getColumnDimension('A')->setWidth(25); // Increased from 15 to 25
        $this->sheet->getColumnDimension('B')->setWidth(40);
        $this->sheet->getColumnDimension('C')->setWidth(40); // Increased from 25 to 40 to match B
        $this->sheet->getColumnDimension('D')->setWidth(25);

        // Default alignment and text wrapping
        $this->spreadsheet->getDefaultStyle()->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
            
        // Set print layout
        $this->sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
            
        // Set auto-size for all columns to ensure text fits
        $this->sheet->getColumnDimension('A')->setAutoSize(false);
        $this->sheet->getColumnDimension('B')->setAutoSize(false);
        $this->sheet->getColumnDimension('C')->setAutoSize(false);
        $this->sheet->getColumnDimension('D')->setAutoSize(false);
    }

    public function exportToExcel()
    {
        $this->addTitle();
        $this->addBankingInformation();
        $this->currentRow += 1;
        $this->addClaimInformation();
        $this->currentRow += 1;
        $this->addTripDetails();
        $this->currentRow += 1;
        if ($this->claim->accommodations->count() > 0) {
            $this->addAccommodationDetails();
            $this->currentRow += 1;
        }
        $this->addFinancialSummary();
        $this->currentRow += 1;
        $this->addApprovalHistory();
        $this->addSignatures();
        $this->addGeneratedAt();

        $writer = new Xlsx($this->spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'claim_');
        $writer->save($tempFile);

        return response()->download($tempFile, 'claim_' . $this->claim->id . '.xlsx')
            ->deleteFileAfterSend();
    }

    protected function addTitle()
    {
        // Merge cells for the header row
        $this->sheet->mergeCells('A1:D1');

        // Create a rich text object to style parts of the text differently
        $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();

        // Add the title part
        $titlePart = $richText->createTextRun("TRAVEL CLAIM REPORT\n");
        $titlePart->getFont()
            ->setBold(true)
            ->setSize(16);

        // Add the company part
        $companyPart = $richText->createTextRun(strtoupper($this->data['company']));
        $companyPart->getFont()
            ->setBold(false)
            ->setSize(11);

        // Set the rich text to the cell
        $this->sheet->getCell('A1')->setValue($richText);

        // Style the cell
        $this->sheet->getStyle('A1')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Set row height first
        $this->sheet->getRowDimension(1)->setRowHeight(55);

        // Add logo
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(public_path('images/logo.png'));
        $drawing->setHeight(35);
        $drawing->setCoordinates('D1');

        // Position logo to the right side of cell D1
        $columnDWidth = $this->sheet->getColumnDimension('D')->getWidth() * 7;
        $logoWidth = $drawing->getWidth();
        $drawing->setOffsetX($columnDWidth - $logoWidth - 10);

        // Position logo vertically
        $drawing->setOffsetY(10);

        $drawing->setWorksheet($this->sheet);

        $this->currentRow = 2;
    }

    protected function addSectionHeader($title, $row)
    {
        $this->sheet->mergeCells("A{$row}:D{$row}");
        $this->sheet->setCellValue("A{$row}", $title);
        $this->sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::SECTION_BG_COLOR]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT, 
                'vertical' => Alignment::VERTICAL_CENTER,
                'indent' => 1
            ]
        ]);
        $this->sheet->getRowDimension($row)->setRowHeight(25);
        return $row + 1;
    }

    protected function addBankingInformation()
    {
        $this->currentRow = $this->addSectionHeader('Banking Information', $this->currentRow);

        $user = $this->claim->user;
        // Get the latest banking information for the user
        $bankingInfo = \App\Models\BankingInformation::where('user_id', $user->id)
            ->latest()
            ->first();

        $details = [
            ['Bank Name', $bankingInfo ? $bankingInfo->bank_name : 'No Information'],
            ['Account Number', $bankingInfo ? $bankingInfo->account_number : 'No Information'],
            ['Account Holder Name', $bankingInfo ? $bankingInfo->account_holder : ($user->first_name . ' ' . $user->second_name)],
        ];

        foreach ($details as $detail) {
            $this->sheet->setCellValue('A' . $this->currentRow, $detail[0]);
            $this->sheet->mergeCells('B' . $this->currentRow . ':D' . $this->currentRow);
            $this->sheet->setCellValue('B' . $this->currentRow, $detail[1]);

            $this->sheet->getStyle('A' . $this->currentRow)->getFont()->setBold(true);
            
            // Set left alignment for both label and value with proper text wrapping
            $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(false)
                ->setIndent(1);
                
            $this->sheet->getStyle('B' . $this->currentRow . ':D' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(false)
                ->setIndent(1);

            // Increase row height if needed for longer text
            $this->sheet->getRowDimension($this->currentRow)->setRowHeight(25);
            $this->currentRow++;
        }
    }

    protected function addClaimInformation()
    {
        $this->currentRow = $this->addSectionHeader('Claim Information', $this->currentRow);

        // Format the description/remarks
        $description = $this->data['description'] ?? '';
        $formattedDescription = !empty($description) ? $description : '-';

        $details = [
            ['Claim ID', '#' . $this->claim->id],
            ['Employee Name', $this->data['employee_name']],
            ['Department', $this->data['department']],
            ['Claim Period', $this->data['date_from'] . ' to ' . $this->data['date_to']],
            ['Status', $this->data['status']],
            ['Submitted Date', $this->data['submitted_at']],
            ['Remarks', $formattedDescription]
        ];

        foreach ($details as $detail) {
            $this->sheet->setCellValue('A' . $this->currentRow, $detail[0]);
            $this->sheet->mergeCells('B' . $this->currentRow . ':D' . $this->currentRow);
            $this->sheet->setCellValue('B' . $this->currentRow, $detail[1]);

            $this->sheet->getStyle('A' . $this->currentRow)->getFont()->setBold(true);
            
            // Set left alignment for both label and value with proper text wrapping
            $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(false) // Disable text wrapping for column A
                ->setIndent(1);
                
            // For remarks, enable text wrapping
            if ($detail[0] === 'Remarks') {
                $this->sheet->getStyle('B' . $this->currentRow . ':D' . $this->currentRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true) // Enable text wrapping for remarks
                    ->setIndent(1);
                
                // Increase row height for remarks to accommodate multiple lines
                $rowHeight = min(max(strlen($formattedDescription) / 3, 50), 100); // Dynamic height based on content length
                $this->sheet->getRowDimension($this->currentRow)->setRowHeight($rowHeight);
            } else {
                $this->sheet->getStyle('B' . $this->currentRow . ':D' . $this->currentRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(false) // Disable text wrapping for other fields
                    ->setIndent(1);
                
                // Standard row height for other fields
                $this->sheet->getRowDimension($this->currentRow)->setRowHeight(25);
            }

            $this->currentRow++;
        }
    }

    protected function addTripDetails()
    {
        $this->currentRow = $this->addSectionHeader('Trip Details', $this->currentRow);

        // Headers
        $headers = ['No.', 'From', 'To', 'KM'];
        $columns = ['A', 'B', 'C', 'D'];

        foreach (array_combine($columns, $headers) as $col => $header) {
            $this->sheet->setCellValue($col . $this->currentRow, $header);
        }

        $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::HEADER_BG_COLOR]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $this->sheet->getRowDimension($this->currentRow)->setRowHeight(25);
        $this->currentRow++;

        // Trip details
        foreach ($this->data['locations'] as $index => $location) {
            $this->sheet->setCellValue('A' . $this->currentRow, $index + 1);
            $this->sheet->setCellValue('B' . $this->currentRow, $location['from']);
            $this->sheet->setCellValue('C' . $this->currentRow, $location['to']);
            $this->sheet->setCellValue('D' . $this->currentRow, $location['distance']);

            // Apply styling
            $style = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
            ];

            if ($index % 2 === 1) {
                $style['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'F9FAFB']
                ];
            }

            $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
                ->applyFromArray($style);

            // Set specific alignments
            $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            $this->sheet->getStyle('B' . $this->currentRow . ':C' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setIndent(1);
                
            $this->sheet->getStyle('D' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                ->setIndent(1);

            // Adjust row height based on content
            $this->sheet->getRowDimension($this->currentRow)->setRowHeight(40);
            $this->currentRow++;
        }

        // Total row
        $this->sheet->setCellValue('A' . $this->currentRow, '');
        $this->sheet->setCellValue('B' . $this->currentRow, '');
        $this->sheet->setCellValue('C' . $this->currentRow, 'Total Distance');
        $this->sheet->setCellValue('D' . $this->currentRow, $this->data['total_distance']);

        $totalStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::TOTAL_BG_COLOR]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
        ];

        $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
            ->applyFromArray($totalStyle);

        $this->sheet->getStyle('C' . $this->currentRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setIndent(1);
            
        $this->sheet->getStyle('D' . $this->currentRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setIndent(1);

        $this->sheet->getRowDimension($this->currentRow)->setRowHeight(25);
        $this->currentRow++;
    }

    protected function addAccommodationDetails()
    {
        $this->currentRow = $this->addSectionHeader('Accommodation Details', $this->currentRow);

        // Headers
        $headers = ['No.', 'Location', 'Check In', 'Check Out', 'Cost (RM)'];
        $columns = ['A', 'B', 'C', 'D'];

        // Set headers
        $this->sheet->setCellValue('A' . $this->currentRow, $headers[0]);
        $this->sheet->mergeCells('B' . $this->currentRow . ':C' . $this->currentRow);
        $this->sheet->setCellValue('B' . $this->currentRow, $headers[1]);
        $this->sheet->setCellValue('D' . $this->currentRow, $headers[4]);

        // Header styling with consistent padding
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::HEADER_BG_COLOR]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1]
        ];

        $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
            ->applyFromArray($headerStyle);

        $this->sheet->getRowDimension($this->currentRow)->setRowHeight(30);
        $this->currentRow++;

        // Add date header row
        $this->sheet->setCellValue('A' . $this->currentRow, '');
        $this->sheet->setCellValue('B' . $this->currentRow, $headers[2]);
        $this->sheet->setCellValue('C' . $this->currentRow, $headers[3]);
        $this->sheet->setCellValue('D' . $this->currentRow, '');

        $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
            ->applyFromArray($headerStyle);

        $this->sheet->getRowDimension($this->currentRow)->setRowHeight(30);
        $this->currentRow++;

        // Content
        foreach ($this->claim->accommodations as $index => $accommodation) {
            // First row: Number and Location
            $this->sheet->setCellValue('A' . $this->currentRow, $index + 1);
            $this->sheet->mergeCells('B' . $this->currentRow . ':C' . $this->currentRow);
            $this->sheet->setCellValue('B' . $this->currentRow, $accommodation->location);
            $this->sheet->setCellValue('D' . $this->currentRow, number_format($accommodation->price, 2));

            // Base style with consistent padding
            $style = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'indent' => 1
                ]
            ];

            if ($index % 2 === 0) {
                $style['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'F9FAFB']
                ];
            }

            // Apply base style to all cells
            $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
                ->applyFromArray($style);

            // Ensure number column is left-aligned
            $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Set cost column to right alignment
            $this->sheet->getStyle('D' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Second row: Dates
            $this->currentRow++;
            $this->sheet->setCellValue('A' . $this->currentRow, '');
            $this->sheet->setCellValue('B' . $this->currentRow, $accommodation->check_in->format('d/m/Y'));
            $this->sheet->setCellValue('C' . $this->currentRow, $accommodation->check_out->format('d/m/Y'));
            $this->sheet->setCellValue('D' . $this->currentRow, '');

            // Apply same style to second row
            if ($index % 2 === 0) {
                $style['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'F9FAFB']
                ];
            }

            $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
                ->applyFromArray($style);

            // Set consistent row heights
            $this->sheet->getRowDimension($this->currentRow - 1)->setRowHeight(25);
            $this->sheet->getRowDimension($this->currentRow)->setRowHeight(25);
            $this->currentRow++;
        }
    }

    protected function addFinancialSummary()
    {
        $this->currentRow = $this->addSectionHeader('Financial Summary', $this->currentRow);

        // Headers
        $headers = ['Item', 'Description', 'Amount (RM)', 'Total (RM)'];
        $columns = ['A', 'B', 'C', 'D'];

        foreach (array_combine($columns, $headers) as $col => $header) {
            $this->sheet->setCellValue($col . $this->currentRow, $header);
        }

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::HEADER_BG_COLOR]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ];

        $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
            ->applyFromArray($headerStyle);

        $this->sheet->getRowDimension($this->currentRow)->setRowHeight(25);
        $this->currentRow++;

        // Mileage row
        $this->sheet->setCellValue('A' . $this->currentRow, '1');
        $this->sheet->setCellValue('B' . $this->currentRow, 'Mileage (' . $this->data['total_distance'] . ' KM)');
        $this->sheet->setCellValue('C' . $this->currentRow, $this->data['petrol_amount']);
        $this->sheet->setCellValue('D' . $this->currentRow, $this->data['petrol_amount']);

        $this->applyRowStyle($this->currentRow);
        $this->currentRow++;

        // Toll row
        $this->sheet->setCellValue('A' . $this->currentRow, '2');
        $this->sheet->setCellValue('B' . $this->currentRow, 'Toll');
        $this->sheet->setCellValue('C' . $this->currentRow, $this->data['toll_amount']);
        $this->sheet->setCellValue('D' . $this->currentRow, $this->data['toll_amount']);

        $this->applyRowStyle($this->currentRow);
        $this->currentRow++;

        // Accommodation row (if applicable)
        if (isset($this->data['accommodation_cost']) && $this->data['accommodation_cost'] > 0) {
            $this->sheet->setCellValue('A' . $this->currentRow, '3');
            $this->sheet->setCellValue('B' . $this->currentRow, 'Accommodation');
            $this->sheet->setCellValue('C' . $this->currentRow, $this->data['accommodation_cost']);
            $this->sheet->setCellValue('D' . $this->currentRow, $this->data['accommodation_cost']);

            $this->applyRowStyle($this->currentRow);
            $this->currentRow++;
        }

        // Total row
        $this->sheet->setCellValue('A' . $this->currentRow, '');
        $this->sheet->setCellValue('B' . $this->currentRow, 'Total');
        $this->sheet->setCellValue('C' . $this->currentRow, '');
        $this->sheet->setCellValue('D' . $this->currentRow, $this->data['total_amount']);

        // Total row styling
        $totalRowStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::TOTAL_BG_COLOR]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ];

        $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
            ->applyFromArray($totalRowStyle);

        // Right-align the amount
        $this->sheet->getStyle('D' . $this->currentRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setIndent(1);

        $this->sheet->getRowDimension($this->currentRow)->setRowHeight(25);
        $this->currentRow++;
    }

    protected function applyRowStyle($row)
    {
        $style = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
        ];

        $this->sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($style);

        // Center the item number
        $this->sheet->getStyle('A' . $row)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Left-align the description
        $this->sheet->getStyle('B' . $row)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setIndent(1);

        // Right-align the amounts
        $this->sheet->getStyle('C' . $row . ':D' . $row)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setIndent(1);

        $this->sheet->getRowDimension($row)->setRowHeight(25);
    }

    protected function addApprovalHistory()
    {
        $this->currentRow = $this->addSectionHeader('Approval History', $this->currentRow);

        // Headers
        $headers = ['Date', 'Department', 'Status', 'Remarks'];
        $columns = ['A', 'B', 'C', 'D'];

        foreach (array_combine($columns, $headers) as $col => $header) {
            $this->sheet->setCellValue($col . $this->currentRow, $header);
        }

        // Header styling with consistent padding
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::HEADER_BG_COLOR]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ];

        $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
            ->applyFromArray($headerStyle);

        $this->sheet->getRowDimension($this->currentRow)->setRowHeight(20);
        $this->currentRow++;

        foreach ($this->data['reviews'] as $index => $review) {
            // Format date to be more compact (remove time)
            $date = explode(' ', $review['date'])[0];
            
            $this->sheet->setCellValue('A' . $this->currentRow, $date);
            $this->sheet->setCellValue('B' . $this->currentRow, $review['department']);
            $this->sheet->setCellValue('C' . $this->currentRow, $this->formatApprovalStatus($review['department'], $review['status']));
            $this->sheet->setCellValue('D' . $this->currentRow, $review['remarks']);

            // Base style with consistent padding
            $style = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];

            if ($index % 2 === 1) {
                $style['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'F9FAFB']
                ];
            }

            // Apply base style to all cells
            $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
                ->applyFromArray($style);

            // Set specific alignments
            $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            $this->sheet->getStyle('B' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
            $this->sheet->getStyle('C' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
            $this->sheet->getStyle('D' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setIndent(1);

            // Reduce row height for more compact display
            $this->sheet->getRowDimension($this->currentRow)->setRowHeight(20);
            $this->currentRow++;
        }
    }

    protected function addSignatures()
    {
        $this->currentRow = $this->addSectionHeader('Signatures', $this->currentRow);

        // Create a border around the entire signature section
        $startRow = $this->currentRow;

        // Get the claim owner's signature path
        $claimOwnerSignature = $this->claim->user->signature_path;
        
        // Log the claim owner signature path for debugging
        \Illuminate\Support\Facades\Log::info("Claim owner signature", [
            'user_id' => $this->claim->user->id,
            'name' => $this->claim->user->first_name . ' ' . $this->claim->user->second_name,
            'signature_path' => $claimOwnerSignature ?? 'null'
        ]);

        // Directly check if the signature file exists in various locations
        if (!empty($claimOwnerSignature)) {
            $possiblePaths = [
                storage_path('app/public/' . $claimOwnerSignature),
                public_path('storage/' . $claimOwnerSignature),
                public_path($claimOwnerSignature),
                storage_path('app/' . $claimOwnerSignature),
                public_path('storage/signatures/' . basename($claimOwnerSignature)),
                storage_path('app/public/signatures/' . basename($claimOwnerSignature))
            ];
            
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    \Illuminate\Support\Facades\Log::info("Found claim owner signature at path", [
                        'path' => $path
                    ]);
                    break;
                }
            }
        }

        // First row: Claim Owner and Admin Executive
        $this->addSignaturePairWithBorder(
            $claimOwnerSignature,
            $this->claim->user->first_name . ' ' . $this->claim->user->second_name,
            'Claim Owner',
            $this->getSignatureForRole('Admin'),
            $this->getReviewerName('Admin'),
            'Admin Executive',
            $this->currentRow
        );
        $this->currentRow += 3;

        // Second row: Operation Manager and HR
        $this->addSignaturePairWithBorder(
            $this->getSignatureForRole('Manager'),
            $this->getReviewerName('Manager'),
            'Operation Manager',
            $this->getSignatureForRole('HR'),
            $this->getReviewerName('HR'),
            'Human Resources Executive',
            $this->currentRow
        );
        $this->currentRow += 3;

        // Third row: Datuk (centered, spans full width)
        $this->addDatukSignature($this->currentRow);
        $this->currentRow += 3;

        // Add border around the entire signature section
        $endRow = $this->currentRow - 1;
        $this->sheet->getStyle("A{$startRow}:D{$endRow}")->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
    }

    protected function addSignaturePairWithBorder($path1, $name1, $role1, $path2, $name2, $role2, $row)
    {
        // Add signature cells with borders
        $this->sheet->mergeCells("A{$row}:B{$row}");
        $this->sheet->mergeCells("C{$row}:D{$row}");

        // Add borders
        $borderStyle = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $this->sheet->getStyle("A{$row}:B{$row}")->applyFromArray($borderStyle);
        $this->sheet->getStyle("C{$row}:D{$row}")->applyFromArray($borderStyle);

        // Set row height for signature
        $this->sheet->getRowDimension($row)->setRowHeight(100);

        // Add signatures with increased height
        if ($path1) $this->addSignatureImage('A', $row, $path1, 80);
        if ($path2) $this->addSignatureImage('C', $row, $path2, 80);

        // Add names and roles
        $nameRow = $row + 1;
        $roleRow = $row + 2;

        // Merge cells for name and role
        $this->sheet->mergeCells("A{$nameRow}:B{$nameRow}");
        $this->sheet->mergeCells("C{$nameRow}:D{$nameRow}");
        $this->sheet->mergeCells("A{$roleRow}:B{$roleRow}");
        $this->sheet->mergeCells("C{$roleRow}:D{$roleRow}");

        // Set text and alignment
        $this->sheet->setCellValue("A{$nameRow}", $name1 !== 'N/A' ? "Approved by " . $name1 : "Not Approved");
        $this->sheet->setCellValue("C{$nameRow}", $name2 !== 'N/A' ? "Approved by " . $name2 : "Not Approved");
        $this->sheet->setCellValue("A{$roleRow}", $role1);
        $this->sheet->setCellValue("C{$roleRow}", $role2);

        // Style text
        $this->sheet->getStyle("A{$nameRow}:D{$nameRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        $this->sheet->getStyle("A{$roleRow}:D{$roleRow}")->applyFromArray([
            'font' => ['size' => 9],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Set row heights
        $this->sheet->getRowDimension($nameRow)->setRowHeight(20);
        $this->sheet->getRowDimension($roleRow)->setRowHeight(20);
    }

    protected function addDatukSignature($row)
    {
        // Merge all cells for Datuk's signature
        $this->sheet->mergeCells("A{$row}:D{$row}");
        
        // Add border
        $this->sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Set row height for signature
        $this->sheet->getRowDimension($row)->setRowHeight(100);

        // Get Datuk's signature from Management role
        $datukSignature = $this->getSignatureForRole('Management');
        
        // Add signature with increased height
        if ($datukSignature) {
            $this->addSignatureImage('FULL', $row, $datukSignature, 80);
        } else {
            // Fallback to static signature if no dynamic one is found
            $this->addSignatureImage('FULL', $row, 'signatures/signature-datuk.png', 80);
        }

        // Add name and role
        $nameRow = $row + 1;
        $roleRow = $row + 2;

        // Merge cells for name and role
        $this->sheet->mergeCells("A{$nameRow}:D{$nameRow}");
        $this->sheet->mergeCells("A{$roleRow}:D{$roleRow}");

        // Get Datuk's name from Management role
        $datukName = $this->getReviewerName('Management');
        $displayName = ($datukName !== 'N/A') ? $datukName : 'Datuk Yong Lam Woei';
        
        // Set text
        $this->sheet->setCellValue("A{$nameRow}", "Approved by " . $displayName);
        $this->sheet->setCellValue("A{$roleRow}", "Management");

        // Style text
        $this->sheet->getStyle("A{$nameRow}:D{$nameRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        $this->sheet->getStyle("A{$roleRow}:D{$roleRow}")->applyFromArray([
            'font' => ['size' => 9],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Set row heights
        $this->sheet->getRowDimension($nameRow)->setRowHeight(20);
        $this->sheet->getRowDimension($roleRow)->setRowHeight(20);
    }

    protected function addSignatureImage($column, $row, $signaturePath, $height)
    {
        try {
            // Log the signature path for debugging
            \Illuminate\Support\Facades\Log::info("Adding signature image", [
                'column' => $column,
                'row' => $row,
                'path' => $signaturePath
            ]);
            
            if (empty($signaturePath)) {
                // If no signature path, add placeholder text
                $targetColumn = ($column === 'FULL') ? 'A' : $column;
                $this->sheet->setCellValue($targetColumn . $row, "No Signature Available");
                $this->sheet->getStyle($targetColumn . $row)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                return;
            }
            
            $drawing = new Drawing();
            $drawing->setName('Signature');
            $drawing->setDescription('Signature');
            
            // Handle both public and storage paths
            $fullPath = $signaturePath;
            
            // Check for different path formats
            if (strpos($signaturePath, 'signatures/') === 0) {
                // This is the most common format based on UserSignature.php
                $fullPath = storage_path('app/public/' . $signaturePath);
                
                // If file doesn't exist in storage, try public path
                if (!file_exists($fullPath)) {
                    $fullPath = public_path('storage/' . $signaturePath);
                }
            } else if (strpos($signaturePath, 'public/') === 0) {
                $fullPath = storage_path('app/' . $signaturePath);
            } else if (strpos($signaturePath, 'storage/') === 0) {
                // Handle storage URLs
                $path = str_replace('storage/', '', $signaturePath);
                $fullPath = storage_path('app/public/' . $path);
            } else if (strpos($signaturePath, '/storage/') !== false) {
                // Handle full storage URLs
                $parts = explode('/storage/', $signaturePath);
                if (count($parts) > 1) {
                    $fullPath = storage_path('app/public/' . $parts[1]);
                }
            }
            
            // Try alternative paths if the file doesn't exist
            if (!file_exists($fullPath)) {
                $alternativePaths = [
                    storage_path('app/public/' . $signaturePath),
                    public_path($signaturePath),
                    public_path('storage/' . $signaturePath),
                    storage_path('app/' . $signaturePath),
                    // Add direct path for the format in the image
                    public_path('storage/signatures/' . basename($signaturePath)),
                    storage_path('app/public/signatures/' . basename($signaturePath))
                ];
                
                foreach ($alternativePaths as $path) {
                    if (file_exists($path)) {
                        $fullPath = $path;
                        \Illuminate\Support\Facades\Log::info("Found signature at alternative path", [
                            'original_path' => $signaturePath,
                            'working_path' => $path
                        ]);
                        break;
                    }
                }
            }
            
            // Log the full path for debugging
            \Illuminate\Support\Facades\Log::info("Signature full path", [
                'path' => $signaturePath,
                'fullPath' => $fullPath,
                'exists' => file_exists($fullPath) ? 'yes' : 'no',
                'basename' => basename($signaturePath)
            ]);

            if (file_exists($fullPath)) {
                // Get original image dimensions
                list($imgWidth, $imgHeight) = getimagesize($fullPath);
                $aspectRatio = $imgWidth / $imgHeight;
                
                // Set drawing height and calculate width based on aspect ratio
                $drawing->setPath($fullPath);
                $drawing->setHeight($height);
                $signatureWidth = $height * $aspectRatio;
                
                // Calculate cell widths using a more precise conversion factor
                $columnWidths = [
                    'A' => $this->sheet->getColumnDimension('A')->getWidth() * 9.142857,
                    'B' => $this->sheet->getColumnDimension('B')->getWidth() * 9.142857,
                    'C' => $this->sheet->getColumnDimension('C')->getWidth() * 9.142857,
                    'D' => $this->sheet->getColumnDimension('D')->getWidth() * 9.142857
                ];
                
                // Calculate merged cell width and target column
                $cellWidth = 0;
                $targetColumn = 'A';
                
                if ($column === 'FULL') {
                    // For Datuk signature (centered across all columns A-D)
                    $cellWidth = array_sum($columnWidths);
                    $targetColumn = 'A'; // Start from column A for true centering
                } elseif ($column === 'A' || $column === 'B') {
                    // First pair (A-B)
                    $cellWidth = $columnWidths['A'] + $columnWidths['B'];
                    $targetColumn = 'A';
                } elseif ($column === 'C' || $column === 'D') {
                    // Second pair (C-D)
                    $cellWidth = $columnWidths['C'] + $columnWidths['D'];
                    $targetColumn = 'C';
                }
                
                // Calculate horizontal centering
                $xOffset = ($cellWidth - $signatureWidth) / 2;
                
                // Calculate vertical centering with more precision
                $rowHeight = $this->sheet->getRowDimension($row)->getRowHeight();
                $yOffset = ($rowHeight - $height) / 2;
                
                // Set coordinates and offsets with minimum padding
                $drawing->setCoordinates($targetColumn . $row);
                $drawing->setOffsetX(max(15, (int)$xOffset)); // Increased minimum padding
                $drawing->setOffsetY(max(15, (int)$yOffset)); // Increased minimum padding
                
                // Increase signature size for better visibility
                $drawing->setHeight($height * 1.2); // Make signature 20% larger
                
                $drawing->setWorksheet($this->sheet);
            } else {
                // If signature file doesn't exist, add a placeholder text
                $targetColumn = ($column === 'FULL') ? 'A' : $column;
                $this->sheet->setCellValue($targetColumn . $row, "Signature File Not Found");
                $this->sheet->getStyle($targetColumn . $row)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading signature image', [
                'path' => $signaturePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Add placeholder text in case of error
            $targetColumn = ($column === 'FULL') ? 'A' : $column;
            $this->sheet->setCellValue($targetColumn . $row, "Signature Error");
            $this->sheet->getStyle($targetColumn . $row)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }
    }

    protected function getReviewerName($roleName)
    {
        $review = $this->claim->reviews()
            ->whereHas('reviewer', function ($query) use ($roleName) {
                $query->whereHas('role', function ($q) use ($roleName) {
                    $q->where('name', 'like', "%{$roleName}%");
                });
            })
            ->latest()
            ->first();

        return $review ? $review->reviewer->first_name . ' ' . $review->reviewer->second_name : 'N/A';
    }

    protected function getSignatureForRole($roleName)
    {
        try {
            $review = $this->claim->reviews()
                ->whereHas('reviewer', function ($query) use ($roleName) {
                    $query->whereHas('role', function ($q) use ($roleName) {
                        $q->where('name', 'like', "%{$roleName}%");
                    });
                })
                ->latest()
                ->first();

            // Log the signature path for debugging
            \Illuminate\Support\Facades\Log::info("Signature for role {$roleName}", [
                'review_found' => $review ? 'yes' : 'no',
                'reviewer_name' => $review ? $review->reviewer->first_name . ' ' . $review->reviewer->second_name : 'N/A',
                'signature_path' => $review ? $review->reviewer->signature_path : 'null'
            ]);

            return $review ? $review->reviewer->signature_path : null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error getting signature for role {$roleName}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function addGeneratedAt()
    {
        $this->currentRow += 1;
        $this->sheet->mergeCells("A{$this->currentRow}:D{$this->currentRow}");
        $this->sheet->setCellValue("A{$this->currentRow}", 'Generated at: ' . now()->format('d/m/Y H:i:s') . ' by WeClaim System');
        $this->sheet->getStyle("A{$this->currentRow}")->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 9,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => self::SECTION_BG_COLOR]
            ]
        ]);
        $this->sheet->getRowDimension($this->currentRow)->setRowHeight(20);
    }

    protected function columnWidthToPixels(array $columns): int
    {
        // Approximate conversion: 1 unit = 7 pixels
        return array_sum($columns) * 7;
    }

    protected function formatApprovalStatus($department, $status)
    {
        // Map status to full names
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
