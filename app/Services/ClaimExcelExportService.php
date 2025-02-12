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
            ['Accommodation Cost', 'RM ' . ($this->data['accommodation_cost'] ?? '0.00')],
            ['Total Amount', 'RM ' . $this->data['total_amount']]
        ];

        foreach ($summary as $index => $item) {
            $this->sheet->setCellValue('A' . $this->currentRow, $item[0]);
            $this->sheet->mergeCells('B' . $this->currentRow . ':D' . $this->currentRow);
            $this->sheet->setCellValue('B' . $this->currentRow, $item[1]);

            if ($index === count($summary) - 1) {
                // Total Amount row
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
                    ]
                ];

                $this->sheet->getStyle('A' . $this->currentRow . ':D' . $this->currentRow)
                    ->applyFromArray($totalRowStyle);

                // Align label left and value right
                $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $this->sheet->getStyle('B' . $this->currentRow . ':D' . $this->currentRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            } else {
                // Regular rows
                $this->sheet->getStyle('A' . $this->currentRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $this->sheet->getStyle('B' . $this->currentRow . ':D' . $this->currentRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
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
            $this->sheet->setCellValue('C' . $this->currentRow, $this->formatApprovalStatus($review['department'], $review['status']));
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

        // Create a border around the entire signature section
        $startRow = $this->currentRow;

        // First row: Claim Owner and Admin
        $this->addSignaturePairWithBorder(
            $this->claim->user->signature_path ?? null,
            $this->claim->user->first_name . ' ' . $this->claim->user->second_name,
            'Claim Owner',
            $this->getSignatureForRole('Admin'),
            $this->getReviewerName('Admin'),
            'Admin',
            $this->currentRow
        );
        $this->currentRow += 3;

        // Second row: Manager and HR
        $this->addSignaturePairWithBorder(
            $this->getSignatureForRole('Manager'),
            $this->getReviewerName('Manager'),
            'Manager',
            $this->getSignatureForRole('HR'),
            $this->getReviewerName('HR'),
            'HR',
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
        $this->sheet->getRowDimension($row)->setRowHeight(90);

        // Add signatures
        if ($path1) $this->addSignatureImage('A', $row, $path1, 70);
        if ($path2) $this->addSignatureImage('C', $row, $path2, 70);

        // Add names and roles
        $nameRow = $row + 1;
        $roleRow = $row + 2;

        // Merge cells for name and role
        $this->sheet->mergeCells("A{$nameRow}:B{$nameRow}");
        $this->sheet->mergeCells("C{$nameRow}:D{$nameRow}");
        $this->sheet->mergeCells("A{$roleRow}:B{$roleRow}");
        $this->sheet->mergeCells("C{$roleRow}:D{$roleRow}");

        // Set text and alignment
        $this->sheet->setCellValue("A{$nameRow}", "Approved by " . $name1);
        $this->sheet->setCellValue("C{$nameRow}", "Approved by " . $name2);
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
        $this->sheet->getRowDimension($row)->setRowHeight(90);

        // Add signature
        $this->addSignatureImage('B', $row, 'signatures/signature-datuk.png', 70);

        // Add name and role
        $nameRow = $row + 1;
        $roleRow = $row + 2;

        // Merge cells for name and role
        $this->sheet->mergeCells("A{$nameRow}:D{$nameRow}");
        $this->sheet->mergeCells("A{$roleRow}:D{$roleRow}");

        // Set text
        $this->sheet->setCellValue("A{$nameRow}", "Approved by Datuk");
        $this->sheet->setCellValue("A{$roleRow}", "Datuk");

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
            $drawing = new Drawing();
            $drawing->setName('Signature');
            $drawing->setDescription('Signature');
            
            // Handle both public and storage paths
            $fullPath = $signaturePath;
            if (strpos($signaturePath, 'signatures/') === 0) {
                $fullPath = storage_path('app/public/' . $signaturePath);
            } else if (strpos($signaturePath, 'public/') === 0) {
                $fullPath = storage_path('app/' . $signaturePath);
            }

            if (file_exists($fullPath)) {
                // Get original image dimensions
                list($imgWidth, $imgHeight) = getimagesize($fullPath);
                $aspectRatio = $imgWidth / $imgHeight;
                
                // Set drawing height and calculate width based on aspect ratio
                $drawing->setPath($fullPath);
                $drawing->setHeight($height);
                $signatureWidth = $height * $aspectRatio;
                
                // Calculate merged cell width in points (1 Excel width unit ≈ 7.2 points)
                $cellWidth = 0;
                if ($column === 'A' || $column === 'B') {
                    $cellWidth = ($this->sheet->getColumnDimension('A')->getWidth() + 
                                $this->sheet->getColumnDimension('B')->getWidth()) * 7.2;
                    $column = 'A'; // Always use A for first pair
                } else if ($column === 'C' || $column === 'D') {
                    $cellWidth = ($this->sheet->getColumnDimension('C')->getWidth() + 
                                $this->sheet->getColumnDimension('D')->getWidth()) * 7.2;
                    $column = 'C'; // Always use C for second pair
                } else {
                    // For Datuk signature (centered across all columns)
                    $cellWidth = ($this->sheet->getColumnDimension('A')->getWidth() +
                                $this->sheet->getColumnDimension('B')->getWidth() +
                                $this->sheet->getColumnDimension('C')->getWidth() +
                                $this->sheet->getColumnDimension('D')->getWidth()) * 7.2;
                    $column = 'A'; // Use A for full width
                }
                
                // Calculate center position
                $xOffset = max(0, ($cellWidth - $signatureWidth) / 2);
                $rowHeight = $this->sheet->getRowDimension($row)->getRowHeight();
                $yOffset = max(0, ($rowHeight - $height) / 2);
                
                // Set coordinates and offsets
                $drawing->setCoordinates($column . $row);
                $drawing->setOffsetX((int)$xOffset);
                $drawing->setOffsetY((int)$yOffset);
                
                $drawing->setWorksheet($this->sheet);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error loading signature image', [
                'path' => $signaturePath,
                'error' => $e->getMessage()
            ]);
        }
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
