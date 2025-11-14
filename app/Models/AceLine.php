<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AceLine extends Model
{


    protected $table = 'tb_greensand_check_ace';

    protected $fillable = [
        'date',
        'shift',
        'product_type_id',
        'product_type_name',
        'number',
        'no_mix',
        'sample_start',
        'sample_finish',
        'machine_no',
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
        'most',
        'dw29_vas',
        'dw29_debu',
        'dw31_vas',
        'dw31_id',
        'dw31_moldex',
        'dw31_sc',
        'bc13_cb',
        'bc13_c',
        'bc13_m',
        'created_log',
        'updated_log',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'p' => 'float',
        'c' => 'float',
        'gt' => 'float',
        'cb_lab' => 'float',
        'moisture' => 'float',
        'bakunetsu' => 'float',
        'ac' => 'float',
        'tc' => 'float',
        'vsd' => 'float',
        'ig' => 'float',
        'cb_weight' => 'float',
        'tp50_height' => 'float',
        'tp50_weight' => 'float',
        'ssi' => 'float',
        'most' => 'float',
        'dw29_vas' => 'float',
        'dw29_debu' => 'float',
        'dw31_vas' => 'float',
        'dw31_id' => 'float',
        'dw31_moldex' => 'float',
        'dw31_sc' => 'float',
        'bc13_cb' => 'float',
        'bc13_c' => 'float',
        'bc13_m' => 'float',
        'number' => 'integer',
        'no_mix' => 'integer',
        'product_type_id' => 'integer',
    ];
}
