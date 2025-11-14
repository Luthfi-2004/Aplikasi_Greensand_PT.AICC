<?php

namespace App\Exports;

use App\Models\GreensandJsh;
use App\Models\JshStandard;
use App\Models\JshGfn;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GreensandJshTemplateExport
{
    private array $gfnMeshes = ['18,5', '26', '36', '50', '70', '100', '140', '200', '280', 'PAN'];

    public function __construct(
        protected ?string $date = null,
        protected ?string $shift = null,
        protected ?string $keyword = null,
        protected ?string $mm = null
    ) {
    }

    public function download(string $filename): StreamedResponse
    {
        $template = Storage::path('templates/green_sand_template.xlsx');
        $spreadsheet = IOFactory::load($template);
        $sheet = $spreadsheet->getSheetByName('Check Sheet') ?: $spreadsheet->getActiveSheet();

        // header set
        $displayDate = $this->fmtDate($this->date);
        $displayShift = $this->shift ?: '';
        $sheet->setCellValue('Y2', $displayDate);
        $sheet->setCellValue('Y4', $displayShift);

        // Inspector: ambil dari input pertama (created_at paling awal) pada tanggal tsb.
        $inspector = $this->findInspectorForDay() ?? $this->currentUserName();
        $sheet->setCellValue('Y6', $inspector);

        // duplicate header
        $sheet->setCellValue('Y48', $displayDate);
        $sheet->setCellValue('Y50', $displayShift);
        $sheet->setCellValue('Y52', $inspector);

        // standards write
        $this->fillStandardsAtFixedCells($sheet);

        // fetch data
        [$mm1, $mm2, $daily] = $this->getData();
        if ($this->mm === 'MM1')
            $mm2 = collect();
        if ($this->mm === 'MM2')
            $mm1 = collect();

        // fill data
        $this->fillMM1($sheet, $mm1);
        $this->fillMM2($sheet, $mm2);
        $this->fillRightPanels($sheet, $daily);

        // summary write
        $summary = $this->buildSummary();
        $this->writeSummaryAtFixedCells($sheet, $summary);

        // gfn grams
        $this->writeGfnGrams($sheet);

        return response()->streamDownload(function () use ($spreadsheet) {
            IOFactory::createWriter($spreadsheet, 'Xlsx')->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function fillMM1(Worksheet $s, $rows): void
    {
        $r = 0;
        foreach ($rows as $row) {
            $y = 17 + $r;

            $s->setCellValue("B{$y}", $row->mix_ke);
            $s->setCellValue("C{$y}", $this->fmtTime($row->mix_start));
            $s->setCellValue("D{$y}", $this->fmtTime($row->mix_finish));
            $s->setCellValue("E{$y}", $row->mm_p);
            $s->setCellValue("F{$y}", $row->mm_c);
            $s->setCellValue("G{$y}", $row->mm_gt);
            $s->setCellValue("H{$y}", $row->mm_cb_mm);
            $s->setCellValue("I{$y}", $row->mm_cb_lab);
            $s->setCellValue("J{$y}", $row->mm_m);
            $s->setCellValue("K{$y}", $row->machine_no);
            if ($y >= 17)
                $s->setCellValue("L{$y}", $row->mm_bakunetsu);

            $y33 = 33 + $r;
            $s->setCellValue("M{$y33}", $row->mm_ac);
            $s->setCellValue("N{$y33}", $row->mm_tc);
            $s->setCellValue("O{$y33}", $row->mm_vsd);
            $s->setCellValue("P{$y33}", $row->mm_ig);

            $s->setCellValue("Q{$y}", $row->mm_cb_weight);
            $s->setCellValue("R{$y}", $row->mm_tp50_height);
            $s->setCellValue("T{$y}", $row->mm_tp50_weight);
            $s->setCellValue("U{$y}", $row->mm_ssi);

            $s->setCellValue("V{$y}", $row->add_m3);
            $s->setCellValue("W{$y}", $row->add_vsd);
            $s->setCellValue("X{$y}", $row->add_sc);

            $s->setCellValue("Y{$y}", $row->bc12_cb);
            $s->setCellValue("Z{$y}", $row->bc12_m);
            $s->setCellValue("AA{$y}", $row->bc11_ac);
            $s->setCellValue("AB{$y}", $row->bc11_vsd);
            $s->setCellValue("AC{$y}", $row->bc16_cb);
            $s->setCellValue("AD{$y}", $row->bc16_m);

            $s->setCellValue("AE{$y}", $this->fmtTime($row->rs_time));
            $s->setCellValue("AF{$y}", $row->rs_type);
            $s->setCellValue("AG{$y}", $row->bc9_moist);
            $s->setCellValue("AH{$y}", $row->bc10_moist);
            $s->setCellValue("AI{$y}", $row->bc11_moist);
            $s->setCellValue("AJ{$y}", $row->bc9_temp);
            $s->setCellValue("AK{$y}", $row->bc10_temp);
            $s->setCellValue("AL{$y}", $row->bc11_temp);

            $r++;
            if ($r >= 9)
                break;
        }
    }

    private function fillMM2(Worksheet $s, $rows): void
    {
        $r = 0;
        foreach ($rows as $row) {
            $y = 26 + $r;

            $s->setCellValue("B{$y}", $row->mix_ke);
            $s->setCellValue("C{$y}", $this->fmtTime($row->mix_start));
            $s->setCellValue("D{$y}", $this->fmtTime($row->mix_finish));
            $s->setCellValue("E{$y}", $row->mm_p);
            $s->setCellValue("F{$y}", $row->mm_c);
            $s->setCellValue("G{$y}", $row->mm_gt);
            $s->setCellValue("H{$y}", $row->mm_cb_mm);
            $s->setCellValue("I{$y}", $row->mm_cb_lab);
            $s->setCellValue("J{$y}", $row->mm_m);
            $s->setCellValue("K{$y}", $row->machine_no);
            if ($y >= 26)
                $s->setCellValue("L{$y}", $row->mm_bakunetsu);

            $s->setCellValue("Q{$y}", $row->mm_cb_weight);
            $s->setCellValue("R{$y}", $row->mm_tp50_height);
            $s->setCellValue("T{$y}", $row->mm_tp50_weight);
            $s->setCellValue("U{$y}", $row->mm_ssi);

            $s->setCellValue("V{$y}", $row->add_m3);
            $s->setCellValue("W{$y}", $row->add_vsd);
            $s->setCellValue("X{$y}", $row->add_sc);

            $r++;
            if ($r >= 8)
                break;
        }
    }

    private function fillRightPanels(Worksheet $s, $d): void
    {
        if (!$d)
            return;

        // ===== MOLDING PANEL (DITUKAR POSISI) =====
        // Sekarang: AB22–AC23 = total_air & total_mixing
        $s->setCellValue('AB22', $d->total_air_mm1 ?? $d->total_air_bc9 ?? null);
        $s->setCellValue('AC22', $d->total_air_mm2 ?? null);
        $s->setCellValue('AB23', $d->total_mixing_mm1 ?? null);
        $s->setCellValue('AC23', $d->total_mixing_mm2 ?? null);

        // Lalu: AB24–AC25 = add_water & temp_sand
        $s->setCellValue('AB24', $d->add_water_mm);
        $s->setCellValue('AC24', $d->add_water_mm_2);
        $s->setCellValue('AB25', $d->temp_sand_mm_1);
        $s->setCellValue('AC25', $d->temp_sand_mm_2);

        // ===== OTHERS PANEL (tetap) =====
        $s->setCellValue('AB26', $d->total_air_bc9);
        $s->setCellValue('AB27', $d->total_flask);
        $s->setCellValue('AB28', $d->rcs_pick_up);
        $s->setCellValue('AB29', $d->rcs_avg);
        $s->setCellValue('AB30', $d->add_bentonite_ma);
        $s->setCellValue('AB31', $d->total_sand);
        $s->setCellValue('AB32', $d->add_water_bc10);
        $s->setCellValue('AB33', $d->lama_bc10_jalan);
        $s->setCellValue('AB34', $d->rating_pasir_es);

        // ===== IC / SSI (tetap) =====
        $s->setCellValue('Z40', $d->bc9_ic_moist);
        $s->setCellValue('AA40', $d->bc9_ic_ac);
        $s->setCellValue('AD43', $d->ssi1_awal);
        $s->setCellValue('AG43', $d->ssi1_akhir);
        $s->setCellValue('AD44', $d->ssi2_awal);
        $s->setCellValue('AG44', $d->ssi2_akhir);
    }

    private function fillStandardsAtFixedCells(Worksheet $sheet): void
    {
        $std = JshStandard::query()->first();
        if (!$std)
            return;

        $row = 12;

        $colsWithJudge = [
            'mm_p' => 'E',
            'mm_c' => 'F',
            'mm_gt' => 'G',
            'mm_cb_mm' => 'H',
            'mm_cb_lab' => 'I',
            'mm_m' => 'J',
            'mm_bakunetsu' => 'L',
            'mm_ac' => 'M',
            'mm_tc' => 'N',
            'mm_vsd' => 'O',
            'mm_ig' => 'P',
            'mm_cb_weight' => 'Q',
            'mm_tp50_height' => 'R',
            'mm_tp50_weight' => 'T',
            'mm_ssi' => 'U',
            'bc12_cb' => 'Y',
            'bc12_m' => 'Z',
            'bc11_ac' => 'AA',
            'bc11_vsd' => 'AB',
            'bc16_cb' => 'AC',
            'bc16_m' => 'AD',
        ];

        $returnSandCols = [
            'bc9_moist' => 'AG',
            'bc10_moist' => 'AH',
            'bc11_moist' => 'AI',
            'bc9_temp' => 'AJ',
            'bc10_temp' => 'AK',
            'bc11_temp' => 'AL',
        ];

        $fmt = function ($v) {
            if ($v === null || $v === '')
                return null;
            $s = str_replace(',', '.', (string) $v);
            if (is_numeric($s))
                $s = rtrim(rtrim($s, '0'), '.');
            return $s;
        };

        $putRange = function (string $cell, $min, $max) use ($sheet, $fmt) {
            $a = $fmt($min);
            $b = $fmt($max);
            if ($a !== null && $b !== null) {
                $sheet->setCellValueExplicit($cell, "{$a} ~ {$b}", DataType::TYPE_STRING);
            } elseif ($a !== null) {
                $sheet->setCellValueExplicit($cell, "{$a}", DataType::TYPE_STRING);
            } elseif ($b !== null) {
                $sheet->setCellValueExplicit($cell, "{$b}", DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue($cell, '');
            }
        };

        foreach ([$colsWithJudge, $returnSandCols] as $map) {
            foreach ($map as $field => $col) {
                $minKey = $field . '_min';
                $maxKey = $field . '_max';
                $putRange($col . $row, $std->{$minKey} ?? null, $std->{$maxKey} ?? null);
            }
        }
    }

    private function buildSummary(): array
    {
        $withJudge = [
            'mm_p',
            'mm_c',
            'mm_gt',
            'mm_cb_mm',
            'mm_cb_lab',
            'mm_m',
            'mm_bakunetsu',
            'mm_ac',
            'mm_tc',
            'mm_vsd',
            'mm_ig',
            'mm_cb_weight',
            'mm_tp50_height',
            'mm_tp50_weight',
            'mm_ssi',
            'bc12_cb',
            'bc12_m',
            'bc11_ac',
            'bc11_vsd',
            'bc16_cb',
            'bc16_m',
        ];

        $noJudge = [
            'bc9_moist',
            'bc10_moist',
            'bc11_moist',
            'bc9_temp',
            'bc10_temp',
            'bc11_temp',
        ];

        $fields = array_merge($withJudge, $noJudge);

        $agg = [];
        foreach ($fields as $f) {
            $agg[] = DB::raw("MIN($f) as min_$f");
            $agg[] = DB::raw("MAX($f) as max_$f");
            $agg[] = DB::raw("AVG($f) as avg_$f");
        }
        $row = $this->baseQuery()->reorder()->select($agg)->first();

        $spec = class_exists(JshStandard::class) && method_exists(JshStandard::class, 'specMap')
            ? JshStandard::specMap()
            : [];

        $out = [];
        foreach ($fields as $f) {
            $min = $row?->{"min_$f"};
            $max = $row?->{"max_$f"};
            $avg = $row?->{"avg_$f"};

            $judge = null;
            if ($avg !== null && isset($spec[$f])) {
                $minSpec = $spec[$f]['min'] ?? null;
                $maxSpec = $spec[$f]['max'] ?? null;
                if ($minSpec !== null && $maxSpec !== null) {
                    $judge = ($avg >= $minSpec && $avg <= $maxSpec) ? 'OK' : 'NG';
                }
            }

            $out[$f] = [
                'min' => $min,
                'max' => $max,
                'avg' => $avg !== null ? round($avg, 2) : null,
                'judge' => $judge,
            ];
        }

        return $out;
    }

    private function writeSummaryAtFixedCells(Worksheet $sheet, array $summary): void
    {
        $withJudgeCols = [
            'mm_p' => 'E',
            'mm_c' => 'F',
            'mm_gt' => 'G',
            'mm_cb_mm' => 'H',
            'mm_cb_lab' => 'I',
            'mm_m' => 'J',
            'mm_bakunetsu' => 'L',
            'mm_ac' => 'M',
            'mm_tc' => 'N',
            'mm_vsd' => 'O',
            'mm_ig' => 'P',
            'mm_cb_weight' => 'Q',
            'mm_tp50_height' => 'R',
            'mm_tp50_weight' => 'T',
            'mm_ssi' => 'U',
            'bc12_cb' => 'Y',
            'bc12_m' => 'Z',
            'bc11_ac' => 'AA',
            'bc11_vsd' => 'AB',
            'bc16_cb' => 'AC',
            'bc16_m' => 'AD',
        ];

        $noJudgeCols = [
            'bc9_moist' => 'AG',
            'bc10_moist' => 'AH',
            'bc11_moist' => 'AI',
            'bc9_temp' => 'AJ',
            'bc10_temp' => 'AK',
            'bc11_temp' => 'AL',
        ];

        $rowMin = 35;
        $rowMax = 36;
        $rowAvg = 37;
        $rowJudge = 38;

        $put = function (string $cell, $val) use ($sheet) {
            if ($val === null || $val === '') {
                $sheet->setCellValue($cell, '');
                return;
            }
            if (is_numeric($val)) {
                $sheet->setCellValue($cell, $val + 0);
            } else {
                $sheet->setCellValueExplicit($cell, (string) $val, DataType::TYPE_STRING);
            }
        };

        foreach ([$withJudgeCols, $noJudgeCols] as $map) {
            foreach ($map as $field => $col) {
                $s = $summary[$field] ?? null;
                $put($col . $rowMin, $s['min'] ?? null);
                $put($col . $rowMax, $s['max'] ?? null);
                $put($col . $rowAvg, $s['avg'] ?? null);
            }
        }

        foreach ($withJudgeCols as $field => $col) {
            $judge = $summary[$field]['judge'] ?? null;
            $put($col . $rowJudge, $judge ?: '');
            if ($judge) {
                $sheet->getStyle($col . $rowJudge)->getFont()->setBold(true);
                $sheet->getStyle($col . $rowJudge)->getFont()->getColor()->setARGB(
                    $judge === 'OK' ? 'FF2E7D32' : 'FFC62828'
                );
                $sheet->getStyle($col . $rowJudge)->getAlignment()->setHorizontal('center');
            }
        }

        $lastCol = 'AL';
        $sheet->getStyle('E' . $rowMin . ':' . $lastCol . $rowMin)
            ->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle('E' . $rowMin . ':' . $lastCol . $rowJudge)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    private function baseQuery(): Builder
    {
        $q = GreensandJsh::query();

        if ($this->mm)
            $q->where('mm', $this->mm);
        if ($this->date)
            $q->whereDate('date', $this->toYmd($this->date));
        if ($this->shift)
            $q->where('shift', $this->shift);
        if ($this->keyword) {
            $kw = $this->keyword;
            $q->where(function ($x) use ($kw) {
                $x->where('mix_ke', 'like', "%{$kw}%")
                    ->orWhere('rs_type', 'like', "%{$kw}%")
                    ->orWhere('machine_no', 'like', "%{$kw}%")
                    ->orWhere('rating_pasir_es', 'like', "%{$kw}%");
            });
        }

        return $q->orderBy('date')->orderBy('id');
    }

    private function getData(): array
    {
        $rows = $this->baseQuery()->get();
        $mm1 = $rows->where('mm', 'MM1')->values();
        $mm2 = $rows->where('mm', 'MM2')->values();

        $moldingFields = [
            'add_water_mm',
            'add_water_mm_2',
            'temp_sand_mm_1',
            'temp_sand_mm_2',
            'total_air_mm1',
            'total_air_mm2',
            'total_mixing_mm1',
            'total_mixing_mm2',
            'total_air_bc9',
            'total_flask',
            'rcs_pick_up',
            'rcs_avg',
            'add_bentonite_ma',
            'total_sand',
            'add_water_bc10',
            'lama_bc10_jalan',
            'rating_pasir_es',
        ];

        $daily = $rows->first(function ($r) use ($moldingFields) {
            foreach ($moldingFields as $f) {
                if (!is_null($r->{$f}))
                    return true;
            }
            return false;
        }) ?: $rows->first();

        return [$mm1, $mm2, $daily];
    }

    private function writeGfnGrams(Worksheet $sheet): void
    {
        if (!$this->date || !$this->shift)
            return;

        $dateYmd = $this->toYmd($this->date);
        if (!$dateYmd)
            return;

        $rows = JshGfn::query()
            ->whereDate('gfn_date', $dateYmd)
            ->where('shift', $this->shift)
            ->orderByDesc('created_at')
            ->get()
            ->keyBy('mesh');

        $startRow = 58;
        foreach ($this->gfnMeshes as $i => $mesh) {
            $cell = 'E' . ($startRow + $i);
            $val = $rows->has($mesh) ? ($rows[$mesh]->gram ?? null) : null;

            if ($val === null || $val === '') {
                $sheet->setCellValue($cell, '');
            } else {
                $num = is_numeric($val) ? (float) $val : (float) str_replace(',', '.', (string) $val);
                $sheet->setCellValue($cell, $num);
            }
        }
    }

    private function fmtTime($v): ?string
    {
        if (!$v)
            return null;
        try {
            return $v instanceof \DateTimeInterface ? $v->format('H:i') : Carbon::parse($v)->format('H:i');
        } catch (\Throwable) {
            return (string) $v;
        }
    }

    private function fmtDate($v): ?string
    {
        if (!$v)
            return null;
        try {
            return Carbon::parse($v)->format('d-M-y');
        } catch (\Throwable) {
            return (string) $v;
        }
    }

    private function toYmd(?string $val): ?string
    {
        if (!$val)
            return null;
        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y'] as $f) {
            try {
                return Carbon::createFromFormat($f, $val)->toDateString();
            } catch (\Throwable) {
            }
        }
        try {
            return Carbon::parse($val)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }


    private function currentUserName(): string
    {
        $u = Auth::user();
        $usr = trim((string) ($u?->usr ?? ''));
        $name = $usr !== '' ? strtoupper($usr) : ($u?->nama ?? $u?->email ?? 'User');
        return is_string($name) ? $name : 'User';
    }


    private function findInspectorForDay(): ?string
    {
        $dateYmd = $this->toYmd($this->date);
        if (!$dateYmd)
            return null;

        $row = GreensandJsh::query()
            ->whereDate('date', $dateYmd)
            ->when($this->shift, fn($q) => $q->where('shift', $this->shift))
            ->orderBy('created_at', 'asc')
            ->select('created_log')
            ->first();

        $name = trim((string) ($row?->created_log ?? ''));
        return $name !== '' ? strtoupper($name) : null;
    }
}
