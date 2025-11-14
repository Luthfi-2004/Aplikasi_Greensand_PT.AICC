<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\GreensandJsh;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GreensandExportFull;
use App\Exports\GreensandJshTemplateExport;

class GreensandJshController extends Controller
{
    private function moldingFields(): array
    {
        return [
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
    }

    private function hasAnyMoldingInput(array $in): bool
    {
        foreach ($this->moldingFields() as $f) {
            if (array_key_exists($f, $in) && $in[$f] !== null && $in[$f] !== '')
                return true;
        }
        return false;
    }

    private function findExistingMolding(string $shift, string $dayYmd, ?int $ignoreId = null): ?GreensandJsh
    {
        $fields = $this->moldingFields();
        $q = GreensandJsh::query()
            ->whereDate('date', $dayYmd)
            ->where('shift', $shift)
            ->where(function ($x) use ($fields) {
                foreach ($fields as $f)
                    $x->orWhereNotNull($f);
            });
        if ($ignoreId)
            $q->where('id', '!=', $ignoreId);
        return $q->first();
    }

    private function icssiFields(): array
    {
        return [
            'bc9_ic_moist',
            'bc9_ic_ac',
            'ssi1_awal',
            'ssi1_akhir',
            'ssi2_awal',
            'ssi2_akhir',
        ];
    }

    private function hasAnyICSSIInput(array $in): bool
    {
        foreach ($this->icssiFields() as $f) {
            if (array_key_exists($f, $in) && $in[$f] !== null && $in[$f] !== '')
                return true;
        }
        return false;
    }

    private function findExistingICSSI(string $shift, string $dayYmd, ?int $ignoreId = null): ?GreensandJsh
    {
        $fields = $this->icssiFields();
        $q = GreensandJsh::query()
            ->whereDate('date', $dayYmd)
            ->where('shift', $shift)
            ->where(function ($x) use ($fields) {
                foreach ($fields as $f)
                    $x->orWhereNotNull($f);
            });
        if ($ignoreId)
            $q->where('id', '!=', $ignoreId);
        return $q->first();
    }

    private function critical2Fields(): array
    {
        return ['mm_ac', 'mm_tc', 'mm_vsd', 'mm_ig'];
    }

    private function nonEmptyInputFields(array $in, array $fields): array
    {
        $filled = [];
        foreach ($fields as $f) {
            if (array_key_exists($f, $in) && $in[$f] !== null && $in[$f] !== '')
                $filled[] = $f;
        }
        return $filled;
    }

    private function countExistingFilled(string $field, string $shift, string $dayYmd, ?int $ignoreId = null): int
    {
        $q = GreensandJsh::query()
            ->whereDate('date', $dayYmd)
            ->where('shift', $shift)
            ->whereNotNull($field);
        if ($ignoreId)
            $q->where('id', '!=', $ignoreId);
        return (int) $q->count();
    }

    private function listExistingFilledLabel(string $field, string $shift, string $dayYmd, ?int $ignoreId = null): string
    {
        $q = GreensandJsh::query()
            ->whereDate('date', $dayYmd)
            ->where('shift', $shift)
            ->whereNotNull($field)
            ->orderBy('mix_ke', 'asc')
            ->orderBy('mm', 'asc');
        if ($ignoreId)
            $q->where('id', '!=', $ignoreId);
        $rows = $q->limit(2)->get(['mix_ke', 'mm']);
        if ($rows->isEmpty())
            return '';
        return $rows->map(fn($r) => "Mix ke {$r->mix_ke} ({$r->mm})")->implode(', ');
    }

    public function dataMM1(Request $request)
    {
        return $this->makeResponse($request, 'MM1');
    }

    public function dataMM2(Request $request)
    {
        return $this->makeResponse($request, 'MM2');
    }

    public function dataAll(Request $request)
    {
        return $this->makeResponse($request, null);
    }

    public function export(Request $r)
    {
        $date = $r->query('date');
        $shift = $r->query('shift');
        $keyword = $r->query('keyword');
        $mm = $r->query('mm');
        $fname = 'Greensand_'
            . ($mm ? $mm . '_' : '')
            . ($date ? str_replace(['/', '-'], '', $date) : now('Asia/Jakarta')->format('Ymd'))
            . ($shift ? '_' . $shift : '')
            . '_' . now('Asia/Jakarta')->format('His')
            . '.xlsx';
        return (new GreensandJshTemplateExport($date, $shift, $keyword, $mm))->download($fname);
    }

    public function store(Request $request)
    {
        $in = $this->normalizeAllDecimals($request->all());
        $v = $this->validator($in, 'store');
        if ($v->fails())
            return response()->json(['errors' => $v->errors()], 422);
        $mm = $this->normalizeMm($in['mm'] ?? null);
        $shift = $in['shift'];
        $day = $this->toYmd($in['date'] ?? null) ?: now('Asia/Jakarta')->toDateString();
        $mixKe = (int) ($in['mix_ke'] ?? 0);
        if ($this->isDuplicateMix($mm, $shift, $mixKe, $day, null)) {
            return response()->json(['errors' => ['mix_ke' => ["Mix ke {$mixKe} sudah dipakai untuk {$mm} di shift {$shift} pada {$day}."]]], 422);
        }
        if ($this->hasAnyMoldingInput($in)) {
            if ($ex = $this->findExistingMolding($shift, $day, null)) {
                return response()->json(['errors' => ['molding' => ["Data molding sudah ADA pada Mix ke {$ex->mix_ke} ({$ex->mm}) untuk shift {$shift} tanggal {$day}. Silakan EDIT baris tersebut, tidak boleh input baru."]]], 422);
            }
        }
        if ($this->hasAnyICSSIInput($in)) {
            if ($ex = $this->findExistingICSSI($shift, $day, null)) {
                return response()->json(['errors' => ['icssi' => ["IC/SSI sudah ada di {$ex->mm} Mix {$ex->mix_ke} (Shift {$shift} {$day}). Edit data lama."]]], 422);
            }
        }
        $filledCrit = $this->nonEmptyInputFields($in, $this->critical2Fields());
        $overLimit = false;
        foreach ($filledCrit as $f) {
            if ($this->countExistingFilled($f, $shift, $day, null) >= 2) {
                $overLimit = true;
                break;
            }
        }
        if ($overLimit) {
            return response()->json(['errors' => ['limit2' => ["AC/TC/VSD/IG sudah 2× untuk Shift {$shift} {$day}. Edit data lama."]]], 422);
        }
        $data = $this->mapRequestToModel($in, null, $day);
        $data['created_log'] = 'USER';
        $data['updated_log'] = 'USER';
        
        $row = GreensandJsh::create($data);
        return response()->json(['message' => 'Created', 'id' => $row->id]);
    }

    public function show($id)
    {
        $row = GreensandJsh::findOrFail($id);
        return response()->json(['data' => $row]);
    }

    public function update(Request $request, $id)
    {
        $row = GreensandJsh::findOrFail($id);
        $in = $this->normalizeAllDecimals($request->all());
        $v = $this->validator($in, 'update');
        if ($v->fails())
            return response()->json(['errors' => $v->errors()], 422);
        $mm = $this->normalizeMm($in['mm'] ?? $row->mm);
        $shift = $row->shift;
        $mixKe = isset($in['mix_ke']) ? (int) $in['mix_ke'] : (int) $row->mix_ke;
        $day = $this->dayString($row->date);
        if ($this->isDuplicateMix($mm, $shift, $mixKe, $day, (int) $row->id)) {
            return response()->json(['errors' => ['mix_ke' => ["Mix ke {$mixKe} sudah dipakai untuk {$mm} di shift {$shift} pada {$day}."]]], 422);
        }
        if ($this->hasAnyMoldingInput($in)) {
            if ($ex = $this->findExistingMolding($shift, $day, $row->id)) {
                return response()->json(['errors' => ['molding' => ["Data molding sudah ADA pada Mix ke {$ex->mix_ke} ({$ex->mm}) untuk shift {$shift} tanggal {$day}. Silakan EDIT baris tersebut, tidak boleh memindahkan ke baris lain sebelum menghapus molding di baris lama."]]], 422);
            }
        }
        if ($this->hasAnyICSSIInput($in)) {
            if ($ex = $this->findExistingICSSI($shift, $day, (int) $row->id)) {
                return response()->json(['errors' => ['icssi' => ["IC/SSI sudah ada di {$ex->mm} Mix {$ex->mix_ke} (Shift {$shift} {$day}). Edit data lama."]]], 422);
            }
        }
        $filledCrit = $this->nonEmptyInputFields($in, $this->critical2Fields());
        $overLimit = false;
        foreach ($filledCrit as $f) {
            if ($this->countExistingFilled($f, $shift, $day, (int) $row->id) >= 2) {
                $overLimit = true;
                break;
            }
        }
        if ($overLimit) {
            return response()->json(['errors' => ['limit2' => ["AC/TC/VSD/IG sudah 2× untuk Shift {$shift} {$day}. Edit data lama."]]], 422);
        }
        $data = $this->mapRequestToModel($in, $row, $day, true, true);
        unset($data['created_log']);
        $data['updated_log'] = 'USER';
        
        $row->update($data);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy($id)
    {
        $row = GreensandJsh::findOrFail($id);
        $row->delete();
        return response()->json(['message' => 'Deleted']);
    }

    private function makeResponse(Request $request, ?string $mmFilter)
    {
        try {
            $base = GreensandJsh::query()->from('tb_greensand_check_jsh as a');
            if ($mmFilter)
                $base->where('a.mm', $mmFilter);
            $d = $request->filled('date') ? $this->toYmd($request->date) : null;
            if ($d)
                $base->whereDate('a.date', $d);
            if ($request->filled('shift'))
                $base->where('a.shift', $request->shift);
            if ($request->filled('keyword')) {
                $kw = $request->keyword;
                $base->where(function ($x) use ($kw) {
                    $x->where('a.mix_ke', 'like', "%{$kw}%")
                        ->orWhere('a.rs_type', 'like', "%{$kw}%")
                        ->orWhere('a.machine_no', 'like', "%{$kw}%")
                        ->orWhere('a.rating_pasir_es', 'like', "%{$kw}%");
                });
            }
            $q = $base->select([
                'a.id',
                'a.date',
                'a.shift',
                'a.mm',
                'a.mix_ke',
                'a.mix_start',
                'a.mix_finish',
                'a.mm_p',
                'a.mm_c',
                'a.mm_gt',
                'a.mm_cb_mm',
                'a.mm_cb_lab',
                'a.mm_m',
                'a.machine_no',
                'a.mm_bakunetsu',
                'a.mm_ac',
                'a.mm_tc',
                'a.mm_vsd',
                'a.mm_ig',
                'a.mm_cb_weight',
                'a.mm_tp50_weight',
                'a.mm_tp50_height',
                'a.mm_ssi',
                'a.add_m3',
                'a.add_vsd',
                'a.add_sc',
                'a.bc12_cb',
                'a.bc12_m',
                'a.bc11_ac',
                'a.bc11_vsd',
                'a.bc16_cb',
                'a.bc16_m',
                'a.rs_time',
                'a.rs_type',
                'a.bc9_moist',
                'a.bc10_moist',
                'a.bc11_moist',
                'a.bc9_temp',
                'a.bc10_temp',
                'a.bc11_temp',
                'a.add_water_mm',
                'a.add_water_mm_2',
                'a.temp_sand_mm_1',
                'a.temp_sand_mm_2',
                'a.total_air_mm1',
                'a.total_air_mm2',
                'a.total_mixing_mm1',
                'a.total_mixing_mm2',
                'a.total_air_bc9',
                'a.total_flask',
                'a.rcs_pick_up',
                'a.rcs_avg',
                'a.add_bentonite_ma',
                'a.total_sand',
                'a.add_water_bc10',
                'a.lama_bc10_jalan',
                'a.rating_pasir_es',
                'a.bc9_ic_moist',
                'a.bc9_ic_ac',
                'a.ssi1_awal',
                'a.ssi1_akhir',
                'a.ssi2_awal',
                'a.ssi2_akhir',
            ])->addSelect([
                        'pic' => GreensandJsh::query()->from('tb_greensand_check_jsh as x')
                            ->select('x.created_log')
                            ->whereColumn('x.date', 'a.date')
                            ->whereColumn('x.shift', 'a.shift')
                            ->orderBy('x.created_at', 'asc')->limit(1)
                    ]);
            
            return DataTables::of($q)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group btn-group-sm se-2">';
                    $html .= '<button class="btn btn-outline-warning btn-sm mr-2 btn-edit-gs" data-id="' . $row->id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                    $html .= '<button class="btn btn-outline-danger btn-sm btn-delete-gs" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    $html .= '</div>';
                    return $html;
                })
                ->editColumn('mm', fn($row) => $row->mm === 'MM1' ? 1 : ($row->mm === 'MM2' ? 2 : $row->mm))
                ->editColumn('date', function ($row) {
                    if (!$row->date)
                        return null;
                    try {
                        return Carbon::parse($row->date)->format('Y-m-d H:i:s');
                    } catch (\Throwable $e) {
                        return (string) $row->date;
                    }
                })
                ->rawColumns(['action'])
                ->toJson();
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile()], 500);
        }
    }

    private function validator(array $in, string $mode = 'store')
    {
        $in['mm'] = $this->normalizeMm($in['mm'] ?? null);
        return Validator::make($in, [
            'mm' => 'required|in:MM1,MM2',
            'shift' => 'required|in:D,S,N',
            'mix_ke' => 'required|integer|min:1',
            'mix_start' => 'nullable|date_format:H:i',
            'mix_finish' => 'nullable|date_format:H:i',
            'rs_time' => 'nullable|date_format:H:i',
            'machine_no' => 'nullable|string|max:50',
            'add_water_bc10' => 'nullable|numeric|min:0',
            'lama_bc10_jalan' => 'nullable|numeric|min:0',
            'rating_pasir_es' => 'nullable|numeric',
            'temp_sand_mm_2' => 'nullable|numeric',
            'total_air_mm1' => 'nullable|numeric',
            'total_air_mm2' => 'nullable|numeric',
            'total_mixing_mm1' => 'nullable|numeric',
            'total_mixing_mm2' => 'nullable|numeric',
            'bc9_ic_moist' => 'nullable|numeric',
            'bc9_ic_ac' => 'nullable|numeric',
            'ssi1_awal' => 'nullable|numeric',
            'ssi1_akhir' => 'nullable|numeric',
            'ssi2_awal' => 'nullable|numeric',
            'ssi2_akhir' => 'nullable|numeric',
        ]);
    }

    private function fromInput(array $in, ?GreensandJsh $existing, string $key)
    {
        if (array_key_exists($key, $in)) {
            $v = $in[$key];
            return $v === '' ? null : $v;
        }
        return $existing ? ($existing->{$key} ?? null) : null;
    }

    private function normalizeAllDecimals(array $in): array
    {
        $numericFields = [
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
            'mm_tp50_weight',
            'mm_tp50_height',
            'mm_ssi',
            'add_m3',
            'add_vsd',
            'add_sc',
            'bc12_cb',
            'bc12_m',
            'bc11_ac',
            'bc11_vsd',
            'bc16_cb',
            'bc16_m',
            'bc9_moist',
            'bc10_moist',
            'bc11_moist',
            'bc9_temp',
            'bc10_temp',
            'bc11_temp',
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
            'bc9_ic_moist',
            'bc9_ic_ac',
            'ssi1_awal',
            'ssi1_akhir',
            'ssi2_awal',
            'ssi2_akhir',
        ];
        foreach ($numericFields as $key) {
            if (!array_key_exists($key, $in))
                continue;
            $v = $in[$key];
            if ($v === '' || $v === null) {
                $in[$key] = null;
                continue;
            }
            if (is_string($v)) {
                $v = trim($v);
                $v = str_replace(',', '.', $v);
            }
            $in[$key] = $v;
        }
        if (array_key_exists('mix_ke', $in) && $in['mix_ke'] === '')
            $in['mix_ke'] = null;
        return $in;
    }

    private function normalizeMm($val): ?string
    {
        if ($val === null || $val === '')
            return null;
        $str = strtoupper((string) $val);
        if ($str === '1' || $str === 'MM1')
            return 'MM1';
        if ($str === '2' || $str === 'MM2')
            return 'MM2';
        return null;
    }

    private function dayString($value): string
    {
        if ($value instanceof \DateTimeInterface)
            return Carbon::instance($value)->toDateString();
        return Carbon::parse($value)->toDateString();
    }

    private function toYmd(?string $val): ?string
    {
        if (!$val)
            return null;
        foreach (['d-m-Y', 'Y-m-d', 'd/m/Y'] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $val)->toDateString();
            } catch (\Throwable $e) {
            }
        }
        try {
            return Carbon::parse($val)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function isDuplicateMix(string $mm, string $shift, int $mixKe, string $dayYmd, ?int $ignoreId = null): bool
    {
        $q = GreensandJsh::query()
            ->whereDate('date', $dayYmd)
            ->where('shift', $shift)
            ->where('mm', $mm)
            ->where('mix_ke', $mixKe);
        if ($ignoreId)
            $q->where('id', '!=', $ignoreId);
        return $q->exists();
    }

    private function mapRequestToModel(
        array $in,
        ?GreensandJsh $existing = null,
        ?string $dayYmd = null,
        bool $lockDate = false,
        bool $lockShift = false
    ): array {
        $mm = $this->normalizeMm($in['mm'] ?? ($existing->mm ?? null));
        if ($existing && $lockDate) {
            try {
                $dateTime = $existing->date instanceof \DateTimeInterface
                    ? Carbon::instance($existing->date)->format('Y-m-d H:i:s')
                    : Carbon::parse($existing->date)->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                $dateTime = now('Asia/Jakarta')->format('Y-m-d H:i:s');
            }
        } else {
            $day = $dayYmd ?: now('Asia/Jakarta')->toDateString();
            $timeNow = now('Asia/Jakarta')->format('H:i:s');
            $dateTime = "{$day} {$timeNow}";
        }
        $shiftVal = $lockShift ? ($existing->shift ?? null) : ($in['shift'] ?? ($existing->shift ?? null));
        $data = ['date' => $dateTime, 'shift' => $shiftVal, 'mm' => $mm];
        $keys = [
            'mix_ke',
            'mix_start',
            'mix_finish',
            'mm_p',
            'mm_c',
            'mm_gt',
            'mm_cb_mm',
            'mm_cb_lab',
            'mm_m',
            'machine_no',
            'mm_bakunetsu',
            'mm_ac',
            'mm_tc',
            'mm_vsd',
            'mm_ig',
            'mm_cb_weight',
            'mm_tp50_weight',
            'mm_tp50_height',
            'mm_ssi',
            'add_m3',
            'add_vsd',
            'add_sc',
            'bc12_cb',
            'bc12_m',
            'bc11_ac',
            'bc11_vsd',
            'bc16_cb',
            'bc16_m',
            'rs_time',
            'rs_type',
            'bc9_moist',
            'bc10_moist',
            'bc11_moist',
            'bc9_temp',
            'bc10_temp',
            'bc11_temp',
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
            'bc9_ic_moist',
            'bc9_ic_ac',
            'ssi1_awal',
            'ssi1_akhir',
            'ssi2_awal',
            'ssi2_akhir',
        ];
        foreach ($keys as $k)
            $data[$k] = $this->fromInput($in, $existing, $k);
        return $data;
    }
}