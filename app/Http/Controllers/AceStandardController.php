<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AceStandard;
use Illuminate\Support\Facades\Validator; // <-- import Validator facade

class AceStandardController extends Controller
{
    // tampil halaman
    public function index()
    {
        $std = AceStandard::first();
        if (!$std) {
            $std = AceStandard::create([]);
        }

        return view('ace.standards', compact('std'));
    }

    public function update(Request $request)
    {
        $std = AceStandard::query()->firstOrCreate([]);

        $keys = [
            'p',
            'c',
            'gt',
            'cb_lab',
            'moisture',
            'bakunetsu',
            'ac',
            'tc',
            'vsd',
            'ig',
            'cb_weight',
            'tp50_height',
            'tp50_weight',
            'ssi',
            'bc13_cb',
            'bc13_c',
            'bc13_m',
        ];

        $allData = [];
        foreach ($keys as $k) {
            $minKey = $k . '_min';
            $maxKey = $k . '_max';

            $min = $request->input($minKey);
            $max = $request->input($maxKey);

            // normalisasi koma -> titik untuk decimal
            $min = ($min === null || $min === '') ? null : str_replace(',', '.', (string) $min);
            $max = ($max === null || $max === '') ? null : str_replace(',', '.', (string) $max);

            // jika kedua nilai ada dan min > max, tukar
            if ($min !== null && $max !== null && is_numeric($min) && is_numeric($max) && (float) $min > (float) $max) {
                [$min, $max] = [$max, $min];
            }

            $allData[$minKey] = $min;
            $allData[$maxKey] = $max;
        }

        $rules = [];
        foreach ($keys as $k) {
            $rules[$k . '_min'] = ['nullable', 'numeric'];
            $rules[$k . '_max'] = ['nullable', 'numeric'];
        }

        // gunakan Validator facade yang sudah di-import
        $v = Validator::make($allData, $rules);

        if ($v->fails()) {
            // withInput() tanpa argumen otomatis mengambil input saat ini
            return back()->withErrors($v)->withInput();
        }

        $std->update($allData);

        return back()->with('status', 'Standards updated.');
    }
}
