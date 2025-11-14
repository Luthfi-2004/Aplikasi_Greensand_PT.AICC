<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AceTotalGfn extends Model
{
    protected $table = 'tb_greensand_gfn_total_ace';

    protected $fillable = [
        'gfn_date','shift','nilai_gfn','mesh_total140','mesh_total70','meshpan',
        'judge_mesh_140','judge_mesh_70','judge_meshpan',
        'total_gram','total_percentage_index',
    ];

    protected $casts = [
        'gfn_date' => 'date',
        'nilai_gfn' => 'decimal:2',
        'mesh_total140' => 'decimal:2',
        'mesh_total70' => 'decimal:2',
        'meshpan' => 'decimal:2',
        'total_gram' => 'decimal:2',
        'total_percentage_index' => 'decimal:2',
    ];
}
