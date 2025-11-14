<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AceStandard;
use Illuminate\Support\Facades\Validator;

class AceStandardController extends Controller
{
    /**
     * Show standards page.
     *
     * @return \Illuminate\View\View
     */
    public function index(): \Illuminate\View\View
    {
        $std = AceStandard::first();
        if (! $std) {
            $std = AceStandard::create([]);
        }

        return view('ace.standards', compact('std'));
    }

    /**
     * Update standard values.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Ambil atau buat row standar pertama
        $std = AceStandard::query()->firstOrCreate([]);

        // daftar field "base" (tanpa _min/_max)
        $keys = [
            'p','c','gt','cb_lab','moisture','bakunetsu','ac','tc','vsd','ig',
            'cb_weight','tp50_height','tp50_weight','ssi','bc13_cb','bc13_c','bc13_m',
        ];

        $allData = [];

        foreach ($keys as $k) {
            $minKey = $k . '_min';
            $maxKey = $k . '_max';

            // pastikan kita menggunakan $request yang di-inject -- jangan reassign $request
            $min = $request->input($minKey);
            $max = $request->input($maxKey);

            // normalisasi koma -> titik, dan treat empty as null
            $min = ($min === null || $min === '') ? null : str_replace(',', '.', (string) $min);
            $max = ($max === null || $max === '') ? null : str_replace(',', '.', (string) $max);

            // jika kedua ada dan min > max, tukar
            if ($min !== null && $max !== null && is_numeric($min) && is_numeric($max) && (float) $min > (float) $max) {
                [$min, $max] = [$max, $min];
            }

            $allData[$minKey] = $min;
            $allData[$maxKey] = $max;
        }

        // rules validasi
        $rules = [];
        foreach ($keys as $k) {
            $rules[$k . '_min'] = ['nullable', 'numeric'];
            $rules[$k . '_max'] = ['nullable', 'numeric'];
        }

        $validator = Validator::make($allData, $rules);

        if ($validator->fails()) {
            // withInput() cukup tanpa argumen — Laravel ambil request saat ini
            return back()->withErrors($validator)->withInput();
        }

        // simpan
        $std->update($allData);

        return back()->with('status', 'Standards updated.');
    }
}
