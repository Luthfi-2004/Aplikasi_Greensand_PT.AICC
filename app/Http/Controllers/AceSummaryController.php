<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AceSummaryController extends Controller
{
    // ringkas data
    public function __invoke(Request $r)
    {
        $date = $r->query('date');
        $shift = $r->query('shift');
        $pid = $r->query('product_type_id');

        $q = DB::table('tb_greensand_check_ace');
        if ($date)
            $q->whereDate('date', $date);
        if ($shift)
            $q->where('shift', $shift);
        if ($pid)
            $q->where('product_type_id', $pid);

        $keys = [
            'p',
            'c',
            'gt',
            'cb_lab',
            'moisture',
            'machine_no',
            'bakunetsu',
            'ac',
            'tc',
            'vsd',
            'ig',
            'cb_weight',
            'tp50_height',
            'tp50_weight',
            'ssi',
            'most',
            'no_mix',
            'bc13_cb',
            'bc13_c',
            'bc13_m',
        ];

        $nonNumeric = ['machine_no', 'most', 'no_mix'];
        $std = DB::table('tb_greensand_std_ace')->first();

        $rows = [];
        foreach ($keys as $k) {
            if (in_array($k, $nonNumeric, true)) {
                $rows[$k] = ['min' => '', 'max' => '', 'avg' => ''];
                continue;
            }

            $agg = (clone $q)
                ->selectRaw("MIN($k) as min_val, MAX($k) as max_val, AVG($k) as avg_val")
                ->first();

            $rows[$k] = [
                'min' => $agg && $agg->min_val !== null ? round((float) $agg->min_val, 2) : '',
                'max' => $agg && $agg->max_val !== null ? round((float) $agg->max_val, 2) : '',
                'avg' => $agg && $agg->avg_val !== null ? round((float) $agg->avg_val, 2) : '',
            ];
        }

        $judgeRow = [];
        foreach ($keys as $k) {
            if (in_array($k, $nonNumeric, true)) {
                $judgeRow[$k] = '';
                continue;
            }

            $val = $rows[$k]['avg'];
            if ($val === '' || $val === null) {
                $judgeRow[$k] = '';
                continue;
            }

            $min = $std?->{$k . '_min'} ?? null;
            $max = $std?->{$k . '_max'} ?? null;

            if ($min === null && $max === null) {
                $judgeRow[$k] = '';
                continue;
            }

            $v = (float) $val;
            $ok = true;
            if ($min !== null && $v < (float) $min)
                $ok = false;
            if ($max !== null && $v > (float) $max)
                $ok = false;

            $judgeRow[$k] = $ok ? 'OK' : 'NG';
        }

        $summary = [];
        foreach ($keys as $k) {
            $summary[] = [
                'field' => $k,
                'min' => $rows[$k]['min'],
                'max' => $rows[$k]['max'],
                'avg' => $rows[$k]['avg'],
                'judge' => $judgeRow[$k],
            ];
        }

        return response()->json([
            'summary' => $summary,
        ]);
    }
}
