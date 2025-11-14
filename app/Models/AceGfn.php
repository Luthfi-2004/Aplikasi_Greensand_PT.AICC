<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AceGfn extends Model
{


    protected $table = 'tb_greensand_gfn_ace';

    protected $fillable = [
        'gfn_date',
        'shift',
        'mesh',
        'gram',
        'percentage',
        'index',
        'percentage_index',
        'total_gram',
        'total_percentage_index',
        'created_log',
        'updated_log',
    ];

    protected $casts = [
        'gfn_date' => 'date',
        'gram' => 'decimal:2',
        'percentage' => 'decimal:2',
        'index' => 'integer',
        'percentage_index' => 'decimal:2',
        'total_gram' => 'decimal:2',
        'total_percentage_index' => 'decimal:2',
    ];
}
