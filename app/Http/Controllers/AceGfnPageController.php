<?php

namespace App\Http\Controllers;

use App\Exports\AceGfnExport;
use App\Models\AceGfn;
use App\Models\AceTotalGfn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AceGfnPageController extends Controller
{
    private array $meshes = ['18,5', '26', '36', '50', '70', '100', '140', '200', '280', 'PAN'];
    private array $indices = [10, 20, 30, 40, 50, 70, 100, 140, 200, 300];

    // tampil data
    public function index(Request $req)
    {
        $date = $req->query('date');
        $shift = $req->query('shift');
        $q = $req->query('q');

        if (!$date || !$shift) {
            $now = now('Asia/Jakarta');
            $h = (int) $now->format('H');
            $autoShift = ($h >= 6 && $h < 17) ? 'D' : (($h >= 22 || $h < 6) ? 'N' : 'S');
            $date = $date ?: $now->toDateString();
            $shift = $shift ?: $autoShift;
        }

        $latestRecap = AceTotalGfn::query()
            ->whereDate('gfn_date', $date)
            ->when($shift, fn($qq) => $qq->where('shift', $shift))
            ->orderByDesc('created_at')
            ->first();

        if (!$latestRecap) {
            return view('aceline-gfn.index', [
                'meshes' => $this->meshes,
                'indices' => $this->indices,
                'displayRows' => collect(),
                'displayRecap' => null,
                'filters' => ['date' => $date, 'shift' => $shift, 'q' => $q],
            ]);
        }

        [$displayRows, $displayRecap] = $this->setDisplayForLatest($latestRecap->gfn_date, $latestRecap->shift);

        return view('aceline-gfn.index', [
            'meshes' => $this->meshes,
            'indices' => $this->indices,
            'displayRows' => $displayRows,
            'displayRecap' => $displayRecap,
            'filters' => ['date' => $date, 'shift' => $shift, 'q' => $q],
        ]);
    }

    // simpan data (tanpa batas "hari ini")
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gfn_date' => ['required', 'date'],
            'shift' => ['required', 'in:D,S,N'],
            'grams' => ['required', 'array', 'size:10'],
            'grams.*' => ['nullable', 'numeric', 'min:0'],
        ], [], [
            'gfn_date' => 'Tanggal',
            'shift' => 'Shift',
            'grams' => 'Gram',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_modal', true);
        }

        $date = $request->input('gfn_date');
        $shift = $request->input('shift');

        $dupe = AceTotalGfn::query()
            ->whereDate('gfn_date', $date)
            ->where('shift', $shift)
            ->exists();

        if ($dupe) {
            return back()
                ->withErrors(['gfn_date' => "Data untuk tanggal {$date} (shift {$shift}) sudah ada. Hapus dulu jika ingin input ulang."])
                ->withInput()
                ->with('open_modal', true);
        }

        [$grams, $percentages, $percentageIndices, $totalGram, $sumPI] = $this->computeFromGrams($request->input('grams', []));

        DB::transaction(function () use ($request, $grams, $percentages, $percentageIndices, $totalGram, $sumPI) {
            for ($i = 0; $i < 10; $i++) {
                AceGfn::create([
                    'gfn_date' => $request->gfn_date,
                    'shift' => $request->shift,
                    'mesh' => $this->meshes[$i],
                    'gram' => $grams[$i],
                    'percentage' => $percentages[$i],
                    'index' => $this->indices[$i],
                    'percentage_index' => $percentageIndices[$i],
                    'total_gram' => $totalGram,
                    'total_percentage_index' => $sumPI,
                ]);
            }

            AceTotalGfn::create([
                'gfn_date' => $request->gfn_date,
                'shift' => $request->shift,
                'nilai_gfn' => round($sumPI / 100, 2),
                'mesh_total140' => round($percentages[6] ?? 0, 2),
                'mesh_total70' => round(($percentages[3] ?? 0) + ($percentages[4] ?? 0) + ($percentages[5] ?? 0), 2),
                'meshpan' => round(($percentages[8] ?? 0) + ($percentages[9] ?? 0), 2),
                'judge_mesh_140' => ($percentages[6] >= 3.50 && $percentages[6] <= 8.00) ? 'OK' : 'NG',
                'judge_mesh_70' => ((($percentages[3] ?? 0) + ($percentages[4] ?? 0) + ($percentages[5] ?? 0)) >= 64.00) ? 'OK' : 'NG',
                'judge_meshpan' => ((($percentages[8] ?? 0) + ($percentages[9] ?? 0)) <= 1.40) ? 'OK' : 'NG',
                'total_gram' => $totalGram,
                'total_percentage_index' => $sumPI,
            ]);
        });

        return redirect()->route('acelinegfn.index', ['date' => $date, 'shift' => $shift])
            ->with('status', 'Data GFN ACE LINE berhasil disimpan.');
    }

    // update data (boleh tanggal lampau)
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gfn_date' => ['required', 'date'],
            'shift' => ['required', 'in:D,S,N'],
            'grams' => ['required', 'array', 'size:10'],
            'grams.*' => ['nullable', 'numeric', 'min:0'],
        ], [], [
            'gfn_date' => 'Tanggal',
            'shift' => 'Shift',
            'grams' => 'Gram',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_modal', true);
        }

        $gfnDate = $request->input('gfn_date');
        $shift = $request->input('shift');

        $existsAny = AceTotalGfn::whereDate('gfn_date', $gfnDate)
            ->where('shift', $shift)
            ->exists();

        if (!$existsAny) {
            return back()->withErrors([
                'gfn_date' => "Data tanggal {$gfnDate} (shift {$shift}) belum ada, tidak bisa diupdate."
            ])->withInput()->with('open_modal', true);
        }

        [$grams, $percentages, $percentageIndices, $totalGram, $sumPI] = $this->computeFromGrams($request->input('grams', []));

        DB::transaction(function () use ($gfnDate, $shift, $grams, $percentages, $percentageIndices, $totalGram, $sumPI) {
            AceGfn::whereDate('gfn_date', $gfnDate)->where('shift', $shift)->delete();
            AceTotalGfn::whereDate('gfn_date', $gfnDate)->where('shift', $shift)->delete();

            for ($i = 0; $i < 10; $i++) {
                AceGfn::create([
                    'gfn_date' => $gfnDate,
                    'shift' => $shift,
                    'mesh' => $this->meshes[$i],
                    'gram' => $grams[$i],
                    'percentage' => $percentages[$i],
                    'index' => $this->indices[$i],
                    'percentage_index' => $percentageIndices[$i],
                    'total_gram' => $totalGram,
                    'total_percentage_index' => $sumPI,
                ]);
            }

            AceTotalGfn::create([
                'gfn_date' => $gfnDate,
                'shift' => $shift,
                'nilai_gfn' => round($sumPI / 100, 2),
                'mesh_total140' => round($percentages[6] ?? 0, 2),
                'mesh_total70' => round(($percentages[3] ?? 0) + ($percentages[4] ?? 0) + ($percentages[5] ?? 0), 2),
                'meshpan' => round(($percentages[8] ?? 0) + ($percentages[9] ?? 0), 2),
                'judge_mesh_140' => ($percentages[6] >= 3.50 && $percentages[6] <= 8.00) ? 'OK' : 'NG',
                'judge_mesh_70' => ((($percentages[3] ?? 0) + ($percentages[4] ?? 0) + ($percentages[5] ?? 0)) >= 64.00) ? 'OK' : 'NG',
                'judge_meshpan' => ((($percentages[8] ?? 0) + ($percentages[9] ?? 0)) <= 1.40) ? 'OK' : 'NG',
                'total_gram' => $totalGram,
                'total_percentage_index' => $sumPI,
            ]);
        });

        return redirect()->route('acelinegfn.index', ['date' => $gfnDate, 'shift' => $shift])
            ->with('status', 'Data GFN ACE LINE berhasil diupdate.');
    }

    // HAPUS SET (sekarang boleh tanggal mana pun)
    public function deleteTodaySet(Request $request)
    {
        $gfnDate = $request->input('gfn_date');
        $shift = $request->input('shift');

        if (!$gfnDate || !$shift) {
            return back()->withErrors(['delete' => 'gfn_date dan shift wajib diisi.']);
        }

        $exists = AceTotalGfn::whereDate('gfn_date', $gfnDate)
            ->where('shift', $shift)
            ->exists();

        if (!$exists) {
            return back()->withErrors([
                'delete' => "Data tanggal {$gfnDate} (shift {$shift}) tidak ditemukan."
            ]);
        }

        DB::transaction(function () use ($gfnDate, $shift) {
            AceGfn::whereDate('gfn_date', $gfnDate)->where('shift', $shift)->delete();
            AceTotalGfn::whereDate('gfn_date', $gfnDate)->where('shift', $shift)->delete();
        });

        return redirect()->route('acelinegfn.index', ['date' => $gfnDate, 'shift' => $shift])
            ->with('status', "Data tanggal {$gfnDate} (shift {$shift}) berhasil dihapus.");
    }

    // export excel
    public function export(Request $request)
    {
        $date = $request->query('date');
        $shift = $request->query('shift');
        $filename = sprintf('aceline_gfn_%s.xlsx', now()->format('Ymd_His'));

        return Excel::download(new AceGfnExport(date: $date, shift: $shift), $filename);
    }

    // cek data
    public function checkExists(Request $r)
    {
        $date = $r->query('date');
        $shift = $r->query('shift');

        if (!$date) {
            return response()->json(['exists' => false, 'reason' => 'missing_date']);
        }

        $exists = AceTotalGfn::query()
            ->whereDate('gfn_date', $date)
            ->when($shift, fn($q) => $q->where('shift', $shift))
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    // tampil recap
    private function setDisplayForLatest($gfnDate, $shift): array
    {
        $rows = AceGfn::query()
            ->whereDate('gfn_date', $gfnDate)
            ->where('shift', $shift)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->keyBy('mesh');

        $ordered = collect();

        for ($i = 0; $i < 10; $i++) {
            $mesh = $this->meshes[$i];
            $r = $rows->get($mesh);

            $ordered->push($r ?: (object) [
                'mesh' => $mesh,
                'gram' => 0,
                'percentage' => 0,
                'index' => $this->indices[$i],
                'percentage_index' => 0,
            ]);
        }

        $recap = AceTotalGfn::query()
            ->whereDate('gfn_date', $gfnDate)
            ->where('shift', $shift)
            ->orderByDesc('created_at')
            ->first();

        $displayRecap = $recap ? [
            'gfn_date' => $recap->gfn_date,
            'shift' => $recap->shift,
            'nilai_gfn' => (float) $recap->nilai_gfn,
            'mesh_total140' => (float) $recap->mesh_total140,
            'mesh_total70' => (float) $recap->mesh_total70,
            'meshpan' => (float) $recap->meshpan,
            'judge_mesh_140' => $recap->judge_mesh_140,
            'judge_mesh_70' => $recap->judge_mesh_70,
            'judge_meshpan' => $recap->judge_meshpan,
            'total_gram' => (float) ($recap->total_gram ?? $ordered->sum('gram')),
            'total_percentage_index' => (float) ($recap->total_percentage_index ?? $ordered->sum('percentage_index')),
        ] : null;

        return [$ordered, $displayRecap];
    }

    // hitung ulang
    private function computeFromGrams(array $rawGrams): array
    {
        $grams = array_map(fn($v) => ($v === null || $v === '') ? 0.0 : (float) $v, $rawGrams);

        if (count($grams) !== 10) {
            $grams = array_slice(array_pad($grams, 10, 0.0), 0, 10);
        }

        $totalGram = array_sum($grams);

        if ($totalGram <= 0) {
            return [$grams, array_fill(0, 10, 0.0), array_fill(0, 10, 0.0), 0.0, 0.0];
        }

        $percentages = [];
        $percentageIndices = [];
        $sumPI = 0.0;

        for ($i = 0; $i < 10; $i++) {
            $pct = ($totalGram > 0) ? ($grams[$i] / $totalGram) * 100 : 0.0;
            $pctIdx = $pct * $this->indices[$i];

            $percentages[$i] = round($pct, 2);
            $percentageIndices[$i] = round($pctIdx, 1);
            $sumPI += $percentageIndices[$i];
        }

        return [$grams, $percentages, $percentageIndices, $totalGram, $sumPI];
    }
}
