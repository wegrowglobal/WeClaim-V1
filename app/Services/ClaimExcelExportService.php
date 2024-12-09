<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Claim;
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

    public function __construct(ClaimTemplateMapper $mapper, Claim $claim)
    {
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

        // Set column widths
        $this->sheet->getColumnDimension('A')->setWidth(25);
        $this->sheet->getColumnDimension('B')->setWidth(45);
        $this->sheet->getColumnDimension('C')->setWidth(45);
        $this->sheet->getColumnDimension('D')->setWidth(25);

        // Default alignment
        $this->spreadsheet->getDefaultStyle()->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
    }

    public function exportToExcel()
    {
        $this->addTitle();
        $this->addClaimInformation();
        $this->currentRow += 1;

        $this->addTripDetails();
        $this->currentRow += 1;

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
        $titlePart = $richText->createTextRun("Travel Claim Report\n");
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
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1]
        ]);
        $this->sheet->getRowDimension($row)->setRowHeight(25);
        return $row + 1;
    }

    protected function addClaimInformation()
    {
        $this->currentRow = $this->addSectionHeader('Claim Information', $this->currentRow);

        $details = [
            ['Claim ID', '#' . $this->claim->id],
            ['Employee Name', $this->data['employee_name']],
            ['Department', $this->data['department']],
            ['Claim Period', $this->data['date_from'] . ' to ' . $this->data['date_to']],
            ['Status', $this->data['status']],
            ['Submitted Date', $this->data['submitted_at']]
        ];

        foreach ($details as $detail) {
            $this->sheet->setCellValue('A' . $this->currentRow, $detail[0]);
            $this->sheet->mergeCells('B' . $this->currentRow . ':D' . $this->currentRow);
            $this->sheet->setCellValue('B' . $this->currentRow, $detail[1]);

            $this->sheet->getStyle('A' . $this->currentRow)->getFont()->setBold(true);
            $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
                ->getAlignment()->setIndent(1);

            $this->sheet->getRowDimension($this->currentRow)->setRowHeight(20);
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
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1]
        ]);
        $this->sheet->getRowDimension($this->currentRow)->setRowHeight(30);
        $this->currentRow++;

        // Content
        $locations = collect($this->data['locations']);
        if ($locations->count() % 2 !== 0) {
            $locations = $locations->slice(0, -1);
        }

        foreach ($locations as $index => $location) {
            $this->sheet->setCellValue('A' . $this->currentRow, $index + 1);
            $this->sheet->setCellValue('B' . $this->currentRow, $location['from']);
            $this->sheet->setCellValue('C' . $this->currentRow, $location['to']);
            $this->sheet->setCellValue('D' . $this->currentRow, $location['distance']);

            $style = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ];

            // Align trip detail numbers to the left
            $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setIndent(1);

            if ($index % 2 === 1) {
                $style['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'F9FAFB']
                ];
            }

            $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
                ->applyFromArray($style);

            $this->sheet->getRowDimension($this->currentRow)->setRowHeight(35);
            $this->currentRow++;
        }
    }

    protected function addFinancialSummary()
    {
        $this->currentRow = $this->addSectionHeader('Financial Summary', $this->currentRow);

        $summary = [
            ['Total Distance', $this->data['total_distance'] . ' KM'],
            ['Petrol Amount', 'RM ' . $this->data['petrol_amount']],
            ['Toll Amount', 'RM ' . $this->data['toll_amount']],
            ['Total Amount', 'RM ' . $this->data['total_amount']]
        ];

        foreach ($summary as $index => $item) {
            $this->sheet->setCellValue('A' . $this->currentRow, $item[0]);
            $this->sheet->mergeCells('B' . $this->currentRow . ':D' . $this->currentRow);
            $this->sheet->setCellValue('B' . $this->currentRow, $item[1]);

            $style = [
                'font' => ['bold' => true],
                'alignment' => [
                    'indent' => 1,
                    'horizontal' => Alignment::HORIZONTAL_RIGHT
                ]
            ];

            if ($index === count($summary) - 1) {
                $totalRowStyle = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'EBF5FF']
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '1E40AF']
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB']
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'indent' => 1
                    ]
                ];

                // Apply styles to the entire row
                $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
                    ->applyFromArray($totalRowStyle);

                // Override alignment for label cell
                $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
            } else {
                // Normal row styling
                $this->sheet->getStyle('A' . $this->currentRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    'font' => ['bold' => true]
                ]);

                $this->sheet->getStyle('B' . $this->currentRow . ':D' . $this->currentRow)
                    ->applyFromArray($style);
            }

            $this->sheet->getRowDimension($this->currentRow)->setRowHeight(25);
            $this->currentRow++;
        }
    }

    protected function addApprovalHistory()
    {
        $this->currentRow = $this->addSectionHeader('Approval History', $this->currentRow);

        // Headers
        $headers = ['Date & Time', 'Department', 'Status', 'Remarks'];
        $columns = ['A', 'B', 'C', 'D'];

        foreach (array_combine($columns, $headers) as $col => $header) {
            $this->sheet->setCellValue($col . $this->currentRow, $header);
        }

        $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::HEADER_BG_COLOR]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1]
        ]);
        $this->sheet->getRowDimension($this->currentRow)->setRowHeight(25);
        $this->currentRow++;

        foreach ($this->data['reviews'] as $index => $review) {
            $this->sheet->setCellValue('A' . $this->currentRow, $review['date']);
            $this->sheet->setCellValue('B' . $this->currentRow, $review['department']);
            $this->sheet->setCellValue('C' . $this->currentRow, $review['status']);
            $this->sheet->setCellValue('D' . $this->currentRow, $review['remarks']);

            $style = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['indent' => 1]
            ];

            if ($index % 2 === 1) {
                $style['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'F9FAFB']
                ];
            }

            $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
                ->applyFromArray($style);

            $this->sheet->getRowDimension($this->currentRow)->setRowHeight(25);
            $this->currentRow++;
        }
    }

    protected function addSignatures()
    {
        $this->currentRow = $this->addSectionHeader('Signatures', $this->currentRow);
        $this->currentRow++;

        $signatureStartRow = $this->currentRow;

        // Add signature labels in their own cells
        $this->sheet->setCellValue('A' . $this->currentRow, 'Signed by Datuk');
        $this->sheet->setCellValue('C' . $this->currentRow, 'Signed by Financial');

        // Merge title cells
        $this->sheet->mergeCells('A' . $this->currentRow . ':B' . $this->currentRow);
        $this->sheet->mergeCells('C' . $this->currentRow . ':D' . $this->currentRow);

        // Style the title cells with separate borders
        $titleStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'indent' => 1
            ],
            'font' => ['bold' => true]
        ];

        $this->sheet->getStyle('A' . $this->currentRow . ':B' . $this->currentRow)->applyFromArray($titleStyle);
        $this->sheet->getStyle('C' . $this->currentRow . ':D' . $this->currentRow)->applyFromArray($titleStyle);

        // Move to signature space
        $this->currentRow++;
        $signatureSpaceStart = $this->currentRow;

        // Add space for signatures (4 rows)
        $this->currentRow += 4;

        // Add date at the bottom
        $this->sheet->setCellValue('A' . $this->currentRow, 'Date:');
        $this->sheet->setCellValue('C' . $this->currentRow, 'Date:');

        // Style signature spaces
        $this->sheet->getStyle('A' . $signatureSpaceStart . ':B' . $this->currentRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'indent' => 1
            ],
            'font' => ['bold' => true]
        ]);

        $this->sheet->getStyle('C' . $signatureSpaceStart . ':D' . $this->currentRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'indent' => 1
            ],
            'font' => ['bold' => true]
        ]);

        // Merge signature space cells
        $this->sheet->mergeCells('A' . $signatureSpaceStart . ':B' . $this->currentRow);
        $this->sheet->mergeCells('C' . $signatureSpaceStart . ':D' . $this->currentRow);

        // Set row heights
        $this->sheet->getRowDimension($signatureStartRow)->setRowHeight(25);
        for ($row = $signatureSpaceStart; $row <= $this->currentRow; $row++) {
            $this->sheet->getRowDimension($row)->setRowHeight(25);
        }

        $this->currentRow++;
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
}
