<?php

namespace App\Exports;

use App\Models\JshGfn;
use App\Models\TotalGfn;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class JshGfnExport implements FromCollection, WithHeadings, WithEvents, WithTitle
{
    public function __construct(
        protected ?string $date = null,
        protected ?string $shift = null
    ) {
    }

    protected array $meshes = ['18,5', '26', '36', '50', '70', '100', '140', '200', '280', 'PAN'];
    protected array $indices = [10, 20, 30, 40, 50, 70, 100, 140, 200, 300];

    protected ?array $cached = null;

    public function title(): string
    {
        return 'GFN';
    }

    protected function loadRows(): array
    {
        if ($this->cached !== null)
            return $this->cached;

        $date = $this->date;
        $shift = $this->shift;

        if (!$date) {
            $latest = TotalGfn::query()->orderByDesc('created_at')->first();
            if ($latest) {
                $date = optional($latest->gfn_date)->format('Y-m-d');
                $shift = $shift ?: $latest->shift;
            }
        }

        $latestAt = JshGfn::query()
            ->when($date, fn($q) => $q->whereDate('gfn_date', $date))
            ->when($shift, fn($q) => $q->where('shift', $shift))
            ->max('created_at');

        $rows = collect();
        if ($latestAt) {
            $rows = JshGfn::query()
                ->when($date, fn($q) => $q->whereDate('gfn_date', $date))
                ->when($shift, fn($q) => $q->where('shift', $shift))
                ->where('created_at', $latestAt)
                ->get()
                ->keyBy('mesh');
        }

        $ordered = [];
        $totalGram = 0.0;
        $totalPI = 0.0;

        foreach ($this->meshes as $i => $mesh) {
            $r = $rows->get($mesh);
            $gram = (float) ($r->gram ?? 0);
            $pct = (float) ($r->percentage ?? 0);
            $idx = (int) ($r->index ?? $this->indices[$i]);
            $pi = (float) ($r->percentage_index ?? 0);

            $ordered[] = ['mesh' => $mesh, 'gram' => $gram, 'pct' => $pct, 'idx' => $idx, 'pi' => $pi];
            $totalGram += $gram;
            $totalPI += $pi;
        }

        $recap = TotalGfn::query()
            ->when($date, fn($q) => $q->whereDate('gfn_date', $date))
            ->when($shift, fn($q) => $q->where('shift', $shift))
            ->orderByDesc('created_at')
            ->first();

        $meshPct = array_column($ordered, 'pct');
        $nilaiGFN = $recap?->nilai_gfn ?? round($totalPI / 100, 2);
        $meshTotal140 = $recap?->mesh_total140 ?? round($meshPct[6] ?? 0, 2);
        $meshTotal70 = $recap?->mesh_total70 ?? round(($meshPct[3] ?? 0) + ($meshPct[4] ?? 0) + ($meshPct[5] ?? 0), 2);
        $meshPan = $recap?->meshpan ?? round(($meshPct[8] ?? 0) + ($meshPct[9] ?? 0), 2);
        $judge140 = ($meshTotal140 >= 3.50 && $meshTotal140 <= 8.00) ? 'OK' : 'NG';
        $judge70 = ($meshTotal70 >= 64.00) ? 'OK' : 'NG';
        $judgePan = ($meshPan <= 1.40) ? 'OK' : 'NG';

        return $this->cached = [
            'rows' => $ordered,
            'total' => ['gram' => round($totalGram, 2), 'pi' => round($totalPI, 1)],
            'recap' => [
                'nilai_gfn' => round((float) $nilaiGFN, 2),
                'mesh140' => round((float) $meshTotal140, 2),
                'mesh70' => round((float) $meshTotal70, 2),
                'meshpan' => round((float) $meshPan, 2),
                'judge140' => $judge140,
                'judge70' => $judge70,
                'judgepan' => $judgePan,
            ],
        ];
    }

    public function collection()
    {
        $d = $this->loadRows();
        $rows = [];

        foreach ($d['rows'] as $i => $r) {
            $rows[] = [
                $i + 1,         // No
                $r['mesh'],     // Mesh
                $r['gram'],     // Gram
                $r['pct'],      // %
                $r['idx'],      // Index
                $r['pi'],       // %Index
            ];
        }

        // TOTAL row (di bawah tabel)
        $rows[] = ['', 'TOTAL', $d['total']['gram'], null, null, $d['total']['pi']];

        return new Collection($rows);
    }

    public function headings(): array
    {
        return [['NO', 'MESH', 'GRAM', '%', 'INDEX', '% INDEX']];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                /** @var Worksheet $s */
                $s = $e->sheet->getDelegate();
                $d = $this->loadRows();

                // ===== Header
                $s->getStyle('A1:F1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '343A40']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $s->freezePane('A2');

                // ===== Widths
                $s->getColumnDimension('A')->setWidth(5.5);
                $s->getColumnDimension('B')->setWidth(12);
                $s->getColumnDimension('C')->setWidth(12);
                $s->getColumnDimension('D')->setWidth(9);
                $s->getColumnDimension('E')->setWidth(10);
                $s->getColumnDimension('F')->setWidth(14);
                $s->getColumnDimension('G')->setWidth(10); // kolom JUDGE (untuk rekap)
    
                // ===== Number formats
                $last = $s->getHighestRow();
                // body
                $s->getStyle("C2:C{$last}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00); // Gram
                // persentase untuk 10 baris data saja (biar total kosong tidak dipaksa 0)
                $s->getStyle("D2:D11")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $s->getStyle("F2:F{$last}")->getNumberFormat()->setFormatCode('0.0'); // %Index
    
                // center & border body
                $s->getStyle("A2:F{$last}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $s->getStyle("A1:F{$last}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // ===== TOTAL row (abu-abu ringan + bold)
                $totalRow = $last;
                $s->mergeCells("A{$totalRow}:B{$totalRow}");
                $s->setCellValue("A{$totalRow}", 'TOTAL');
                $s->getStyle("A{$totalRow}:F{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // ===== REKAP di BAWAH tabel
                $gap = 1;                      // 1 baris kosong
                $headRow = $totalRow + $gap + 1;

                // Header rekap: merge A..E judul, F nilai GFN, G "JUDGE"
                $s->mergeCells("A{$headRow}:E{$headRow}");
                $s->setCellValue("A{$headRow}", 'Nilai GFN (Σ %Index / 100)');
                $s->setCellValue("F{$headRow}", $d['recap']['nilai_gfn']);
                $s->setCellValue("G{$headRow}", 'JUDGE');

                $s->getStyle("A{$headRow}:G{$headRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '343A40']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $s->getStyle("F{$headRow}")->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

                // 3 baris item
                $r1 = $headRow + 1;
                $r2 = $headRow + 2;
                $r3 = $headRow + 3;

                foreach ([
                    [$r1, '% MESH 140 (STD : 3.5 ~ 8.0 %)', $d['recap']['mesh140'], $d['recap']['judge140']],
                    [$r2, 'Σ MESH 50, 70 & 100 (Min 64 %)', $d['recap']['mesh70'], $d['recap']['judge70']],
                    [$r3, '% MESH 280 + PAN (STD : 0.00 ~ 1.40 %)', $d['recap']['meshpan'], $d['recap']['judgepan']],
                ] as [$row, $label, $val, $judge]) {

                    $s->mergeCells("A{$row}:E{$row}");
                    $s->setCellValue("A{$row}", $label);
                    $s->setCellValue("F{$row}", $val);
                    $s->setCellValue("G{$row}", $judge);
                    $s->getStyle("F{$row}")->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

                    // style baris
                    $s->getStyle("A{$row}:G{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);

                    if ($judge === 'OK' || $judge === 'NG') {
                        $s->getStyle("G{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => $judge === 'OK' ? '2E7D32' : 'C62828']],
                        ]);
                    }
                }

                // zoom agar proporsional
                $s->getSheetView()->setZoomScale(115);
            },
        ];
    }
}
