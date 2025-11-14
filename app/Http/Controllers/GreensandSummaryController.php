<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GreensandJsh;
use App\Models\JshStandard;
use Illuminate\Support\Facades\DB;

class GreensandSummaryController extends Controller
{
    // ringkas jsh
    public function jsh(Request $request)
    {
        $q = GreensandJsh::query();

        $d = $this->toYmd($request->input('date'));
        if ($d) {
            $q->whereDate('date', $d);
        }
        if ($request->filled('shift')) {
            $q->where('shift', $request->shift);
        }
        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $q->where(function ($x) use ($kw) {
                $x->where('mix_ke', 'like', "%{$kw}%")
                    ->orWhere('rs_type', 'like', "%{$kw}%");
            });
        }

        $fields = [
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
        ];

        // ambil spesifikasi
        $spec = JshStandard::specMap();

        // agregasi nilai
        $agg = [];
        foreach ($fields as $f) {
            $agg[] = DB::raw("MIN($f) as min_$f");
            $agg[] = DB::raw("MAX($f) as max_$f");
            $agg[] = DB::raw("AVG($f) as avg_$f");
        }
        $row = $q->select($agg)->first();

        // susun hasil
        $result = [];
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

            $result[] = [
                'field' => $f,
                'min' => $min,
                'max' => $max,
                'avg' => $avg !== null ? round($avg, 2) : null,
                'spec' => $spec[$f] ?? null,
                'judge' => $judge,
            ];
        }

        return response()->json(['summary' => $result]);
    }

    // parse ymd
    private function toYmd(?string $val): ?string
    {
        if (!$val)
            return null;
        foreach (['d-m-Y', 'Y-m-d', 'd/m/Y'] as $fmt) {
            try {
                return \Carbon\Carbon::createFromFormat($fmt, $val)->toDateString();
            } catch (\Throwable $e) {
            }
        }
        try {
            return \Carbon\Carbon::parse($val)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
