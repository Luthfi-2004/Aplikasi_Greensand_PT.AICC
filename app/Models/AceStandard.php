<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AceStandard extends Model
{
    protected $table = 'tb_greensand_std_ace';

    protected $fillable = [
        // MM
        'p_min','p_max','c_min','c_max','gt_min','gt_max','cb_lab_min','cb_lab_max',
        'moisture_min','moisture_max','bakunetsu_min','bakunetsu_max','ac_min','ac_max',
        'tc_min','tc_max','vsd_min','vsd_max','ig_min','ig_max','cb_weight_min','cb_weight_max', 
        'tp50_height_min','tp50_height_max','tp50_weight_min','tp50_weight_max','ssi_min','ssi_max',
        // BC13
        'bc13_cb_min','bc13_cb_max','bc13_c_min','bc13_c_max','bc13_m_min','bc13_m_max',
    ];
}
