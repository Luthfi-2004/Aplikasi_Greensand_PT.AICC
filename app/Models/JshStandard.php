<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JshStandard extends Model
{
    protected $table = 'tb_greensand_std_jsh';

    protected $guarded = [];

    // daftar field standar (kunci = nama kolom data di tabel JSH)
    public static function fields(): array
    {
        return [
            // MM Sample
            'mm_p','mm_c','mm_gt','mm_cb_mm','mm_cb_lab','mm_m',
            'mm_bakunetsu','mm_ac','mm_tc','mm_vsd','mm_ig',
            'mm_cb_weight','mm_tp50_weight','mm_tp50_height','mm_ssi',

            // BC Sample
            'bc12_cb','bc12_m','bc11_ac','bc11_vsd','bc16_cb','bc16_m',
        ];
    }

    // Ambil map "field => ['min'=>..., 'max'=>...]" dari row pertama
    public static function specMap(): array
    {
        $row = static::query()->first();
        if (!$row) return [];

        $map = [];
        foreach (static::fields() as $f) {
            $min = $row->{$f . '_min'};
            $max = $row->{$f . '_max'};
            if ($min !== null || $max !== null) {
                $map[$f] = ['min' => $min, 'max' => $max];
            }
        }
        return $map;
    }
}
