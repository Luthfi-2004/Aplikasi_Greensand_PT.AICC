<?php

namespace App\Exports;

use App\Models\AceLine;
use App\Models\AceStandard;
use App\Models\AceGfn;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AceLineTemplateExport
{
    public function __construct(
        protected ?string $date = null,
        protected ?string $shift = null,
        protected ?string $productTypeId = null,
        protected string $templateName = 'sandlab_templates.xlsx'
    ) {
    }

    public function download(string $filename = null): StreamedResponse
    {
        $path = $this->resolveTemplatePath();
        if (!is_file($path)) {
            abort(404, "Template not found at: {$path}");
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        // header set
        $this->fillHeader($sheet);
        // std write
        $this->fillStandards($sheet);
        // body fill (tabel utama mulai baris 18)
        $this->fillBody($sheet, 18);
        // VERTICAL: AC/TC/VSD/IG mulai L33/M33/N33/O33 ke bawah
        $this->writeAcTcVsdIgVertical($sheet, 33);
        // VERTICAL: MOST mulai U28 ke bawah
        $this->writeMostVertical($sheet, 28);

        // summary set
        $this->fillSummary($sheet);
        // gfn grams
        $this->writeGfnGrams($sheet);

        $filename ??= 'ACE_' .
            ($this->date ? str_replace('-', '', $this->date) : date('Ymd')) .
            ($this->shift ? '_' . $this->shift : '') .
            ($this->productTypeId ? '_PT' . $this->productTypeId : '') .
            '_' . date('His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            IOFactory::createWriter($spreadsheet, 'Xlsx')->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    protected function resolveTemplatePath(): string
    {
        return storage_path('app/private/templates/' . ltrim($this->templateName, '/\\'));
    }

    protected function rows()
    {
        $q = AceLine::query();

        if ($this->date) {
            try {
                $ymd = Carbon::parse($this->date)->format('Y-m-d');
                $q->whereRaw('DATE(`date`) = ?', [$ymd]);
            } catch (\Throwable) {
            }
        }
        if ($this->shift)
            $q->where('shift', $this->shift);
        if ($this->productTypeId)
            $q->where('product_type_id', $this->productTypeId);

        return $q->orderBy('date')->orderBy('number')->orderBy('id')->get();
    }

    protected function currentUserName(): string
    {
        try {
            $u = auth()->user();
            $usr = trim((string) ($u?->usr ?? ''));
            if ($usr !== '')
                return $usr;
            return $u?->name ?? $u?->email ?? 'User';
        } catch (\Throwable) {
            return 'User';
        }
    }

    protected function fillHeader(Worksheet $s): void
    {
        $first = $this->rows()->first();

        // gunakan format tanggal konsisten d-M-y
        $rawDate = $this->date
            ? Carbon::parse($this->date)->toDateString()
            : ($first?->date ? Carbon::parse($first->date)->toDateString() : null);

        $date = $this->fmtDisplayDate($rawDate);
        $shift = $this->shift ?: ($first->shift ?? '');

        // Inspector = created_log paling awal pada tanggal tsb (per shift diaktifkan)
        $inspector = $this->findInspectorForDay() ?? $this->currentUserName();
        $inspector = strtoupper($inspector);

        $s->setCellValue('U2', $date);
        $s->setCellValue('U4', $shift);
        $s->setCellValue('U6', $inspector);
        $s->setCellValue('U48', $date);
        $s->setCellValue('U50', $shift);
        $s->setCellValue('U52', $inspector);
    }

    protected function toHm(?string $t): string
    {
        if (!$t)
            return '';
        try {
            if (preg_match('/^\d{2}:\d{2}/', $t))
                return substr($t, 0, 5);
            return Carbon::parse($t)->format('H:i');
        } catch (\Throwable) {
            return (string) $t;
        }
    }

    protected function fillStandards(Worksheet $s): void
    {
        $std = AceStandard::first();
        if (!$std)
            return;

        $map = [
            'p' => 'E12',
            'c' => 'F12',
            'gt' => 'G12',
            'cb_lab' => 'H12',
            'moisture' => 'I12',
            'bakunetsu' => 'K12',
            'ac' => 'L12',
            'tc' => 'M12',
            'vsd' => 'N12',
            'ig' => 'O12',
            'cb_weight' => 'P12',
            'tp50_height' => 'Q12',
            'tp50_weight' => 'S12',
            'ssi' => 'T12',
            'bc13_cb' => 'AB12',
            'bc13_c' => 'AC12',
            'bc13_m' => 'AD12',
        ];

        $fmt = function ($v) {
            if ($v === null || $v === '')
                return null;
            $n = str_replace(',', '.', (string) $v);
            if (is_numeric($n)) {
                $n = rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
            }
            return $n;
        };

        foreach ($map as $f => $cell) {
            $min = $fmt($std->{$f . '_min'} ?? null);
            $max = $fmt($std->{$f . '_max'} ?? null);
            if ($min !== null && $max !== null) {
                $s->setCellValueExplicit($cell, "{$min} ~ {$max}", DataType::TYPE_STRING);
            } elseif ($min !== null) {
                $s->setCellValueExplicit($cell, "≥ {$min}", DataType::TYPE_STRING);
            } elseif ($max !== null) {
                $s->setCellValueExplicit($cell, "≤ {$max}", DataType::TYPE_STRING);
            } else {
                $s->setCellValue($cell, '');
            }
        }
    }

    // nama saja
    protected function nameOnly(?string $s): string
    {
        if (!$s)
            return '';
        // potong "NO - NAME"
        $parts = explode(' - ', $s, 2);
        return count($parts) === 2 ? trim($parts[1]) : trim($s);
    }

    protected function fillBody(Worksheet $s, int $startRow = 18): void
    {
        $r = $startRow;

        foreach ($this->rows() as $row) {
            $s->setCellValue("A{$r}", $this->nameOnly($row->product_type_name ?? ''));
            $s->setCellValue("B{$r}", $row->number ?? '');
            $s->setCellValue("C{$r}", $this->toHm($row->sample_start));
            $s->setCellValue("D{$r}", $this->toHm($row->sample_finish));

            $s->setCellValue("E{$r}", $row->p);
            $s->setCellValue("F{$r}", $row->c);
            $s->setCellValue("G{$r}", $row->gt);
            $s->setCellValue("H{$r}", $row->cb_lab);
            $s->setCellValue("I{$r}", $row->moisture);
            $s->setCellValue("J{$r}", $row->machine_no);
            $s->setCellValue("K{$r}", $row->bakunetsu);
            $s->setCellValue("P{$r}", $row->cb_weight);
            $s->setCellValue("Q{$r}", $row->tp50_height);
            $s->setCellValue("S{$r}", $row->tp50_weight);
            $s->setCellValue("T{$r}", $row->ssi);

            // additive set
            $s->setCellValue("U{$r}", $row->dw29_vas);
            $s->setCellValue("V{$r}", $row->dw29_debu);
            $s->setCellValue("W{$r}", $row->dw31_vas);
            $s->setCellValue("X{$r}", $row->dw31_id);
            $s->setCellValue("Y{$r}", $row->dw31_moldex);
            $s->setCellValue("Z{$r}", $row->dw31_sc);

            // bc13 set
            $s->setCellValue("AA{$r}", $row->no_mix);
            $s->setCellValue("AB{$r}", $row->bc13_cb);
            $s->setCellValue("AC{$r}", $row->bc13_c);
            $s->setCellValue("AD{$r}", $row->bc13_m);

            $r++;
        }
    }

    /**
     * Tulis AC, TC, VSD, IG secara vertikal:
     * L33 (AC), M33 (TC), N33 (VSD), O33 (IG) ke bawah mengikuti urutan rows()
     */
    protected function writeAcTcVsdIgVertical(Worksheet $s, int $startRow = 33): void
    {
        $r = $startRow;
        foreach ($this->rows() as $row) {
            $s->setCellValue("L{$r}", $row->ac);
            $s->setCellValue("M{$r}", $row->tc);
            $s->setCellValue("N{$r}", $row->vsd);
            $s->setCellValue("O{$r}", $row->ig);
            $r++;
        }
    }

    /**
     * Tulis MOST secara vertikal mulai U28 ke bawah sesuai urutan rows()
     * (fallback ke moisture jika kolom 'most' tidak tersedia)
     */
    protected function writeMostVertical(Worksheet $s, int $startRow = 28): void
    {
        $r = $startRow;
        foreach ($this->rows() as $row) {
            $val = $row->most ?? $row->moisture ?? null;
            $s->setCellValue("U{$r}", $val ?? '');
            $r++;
        }
    }

    protected function computeAgg(): array
    {
        $f = ['p', 'c', 'gt', 'cb_lab', 'moisture', 'bakunetsu', 'ac', 'tc', 'vsd', 'ig', 'cb_weight', 'tp50_height', 'tp50_weight', 'ssi'];

        $q = AceLine::query();
        if ($this->date) {
            try {
                $q->whereDate('date', Carbon::parse($this->date)->format('Y-m-d'));
            } catch (\Throwable) {
            }
        }
        if ($this->shift)
            $q->where('shift', $this->shift);
        if ($this->productTypeId)
            $q->where('product_type_id', $this->productTypeId);

        $select = collect($f)->map(fn($x) => "MIN($x) min_$x,MAX($x) max_$x,AVG($x) avg_$x")->join(',');
        $agg = $q->selectRaw($select)->first();

        $fmt = fn($v) => $v === null ? '' : round((float) $v, 2);

        $r = ['min' => [], 'max' => [], 'avg' => [], 'judge' => []];
        foreach ($f as $x) {
            $r['min'][$x] = $fmt($agg?->{"min_$x"});
            $r['max'][$x] = $fmt($agg?->{"max_$x"});
            $r['avg'][$x] = $fmt($agg?->{"avg_$x"});
        }

        // judge spec
        $spec = [];
        if ($std = AceStandard::first()) {
            foreach ($f as $x) {
                $a = $std->{$x . '_min'};
                $b = $std->{$x . '_max'};
                if ($a !== null || $b !== null) {
                    $min = $a !== null ? (float) str_replace(',', '.', (string) $a) : null;
                    $max = $b !== null ? (float) str_replace(',', '.', (string) $b) : null;
                    if ($min !== null && $max !== null && $min > $max)
                        [$min, $max] = [$max, $min];
                    $spec[$x] = [];
                    if ($min !== null)
                        $spec[$x]['min'] = $min;
                    if ($max !== null)
                        $spec[$x]['max'] = $max;
                }
            }
        }

        foreach ($r['avg'] as $x => $v) {
            if ($v === '' || !isset($spec[$x])) {
                $r['judge'][$x] = '';
                continue;
            }
            $ok = true;
            if (isset($spec[$x]['min']) && $v < $spec[$x]['min'])
                $ok = false;
            if (isset($spec[$x]['max']) && $v > $spec[$x]['max'])
                $ok = false;
            $r['judge'][$x] = $ok ? 'OK' : 'NG';
        }

        return $r;
    }

    protected function fillSummary(Worksheet $s): void
    {
        $rowMin = 35;
        $rowMax = 36;
        $rowAvg = 37;
        $rowJudge = 38;
        $map = [
            'p' => 'E',
            'c' => 'F',
            'gt' => 'G',
            'cb_lab' => 'H',
            'moisture' => 'I',
            'bakunetsu' => 'K',
            'ac' => 'L',
            'tc' => 'M',
            'vsd' => 'N',
            'ig' => 'O',
            'cb_weight' => 'P',
            'tp50_height' => 'Q',
            'tp50_weight' => 'S',
            'ssi' => 'T',
        ];
        $d = $this->computeAgg();

        foreach ($map as $f => $c) {
            $s->setCellValue("{$c}{$rowMin}", $d['min'][$f] ?? '');
            $s->setCellValue("{$c}{$rowMax}", $d['max'][$f] ?? '');
            $s->setCellValue("{$c}{$rowAvg}", $d['avg'][$f] ?? '');
            $s->setCellValue("{$c}{$rowJudge}", $d['judge'][$f] ?? '');
        }

        // borders set
        $last = 'T';
        $s->getStyle("E{$rowMin}:{$last}{$rowMin}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
        $s->getStyle("E{$rowMin}:{$last}{$rowJudge}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // judge style
        foreach ($map as $f => $c) {
            $cell = "{$c}{$rowJudge}";
            $v = strtoupper(trim((string) $s->getCell($cell)->getValue()));
            if (!$v)
                continue;
            $s->getStyle($cell)->getFont()->setBold(true);
            $s->getStyle($cell)->getAlignment()->setHorizontal('center');
            $s->getStyle($cell)->getFont()->getColor()->setARGB($v === 'OK' ? 'FF2E7D32' : 'FFC62828');
        }
    }

    protected function writeGfnGrams(Worksheet $sheet): void
    {
        $meshes = ['18,5', '26', '36', '50', '70', '100', '140', '200', '280', 'PAN'];

        // ctx detect
        $rowsFirst = $this->rows()->first();
        $targetDate = $this->date
            ? $this->toYmd($this->date)
            : ($rowsFirst?->date ? Carbon::parse($rowsFirst->date)->toDateString() : null);
        $targetShift = $this->shift ?: ($rowsFirst->shift ?? null);

        if (!$targetDate || !$targetShift) {
            for ($i = 0; $i < 10; $i++)
                $sheet->setCellValue('E' . (58 + $i), '');
            return;
        }

        // latest set
        $latest = AceGfn::query()
            ->whereDate('gfn_date', $targetDate)
            ->where('shift', $targetShift)
            ->orderByDesc('created_at')
            ->get()
            ->keyBy('mesh');

        // write rows
        $startRow = 58;
        foreach ($meshes as $i => $mesh) {
            $cell = 'E' . ($startRow + $i);
            $val = $latest->has($mesh) ? ($latest[$mesh]->gram ?? null) : null;
            if ($val === null || $val === '') {
                $sheet->setCellValue($cell, '');
            } else {
                $num = is_numeric($val) ? (float) $val : (float) str_replace(',', '.', (string) $val);
                $sheet->setCellValue($cell, $num);
            }
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

    protected function fmtDisplayDate(?string $d): string
    {
        if (!$d)
            return '';
        try {
            return Carbon::parse($d)->format('d-M-y');
        } catch (\Throwable) {
            return (string) $d;
        }
    }


    protected function findInspectorForDay(): ?string
    {
        // tentukan tanggal target
        $targetDate = null;
        if ($this->date) {
            try {
                $targetDate = Carbon::parse($this->date)->toDateString();
            } catch (\Throwable) {
            }
        }
        if (!$targetDate) {
            $first = $this->rows()->first();
            if ($first?->date) {
                try {
                    $targetDate = Carbon::parse($first->date)->toDateString();
                } catch (\Throwable) {
                }
            }
        }
        if (!$targetDate)
            return null;

        $q = AceLine::query()
            ->whereDate('date', $targetDate)
            ->orderBy('created_at', 'asc')
            ->select('created_log');

        // PER-SHIFT DI-AKTIFKAN
        if ($this->shift) {
            $q->where('shift', $this->shift);
        }

        $row = $q->first();
        $name = trim((string) ($row?->created_log ?? ''));
        return $name !== '' ? strtoupper($name) : null;
    }
}
