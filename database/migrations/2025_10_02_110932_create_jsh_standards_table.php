<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tb_jsh_standards', function (Blueprint $table) {
            $table->id();

            // ==== MM SAMPLE ====
            $table->decimal('mm_p_min', 8, 3)->nullable();
            $table->decimal('mm_p_max', 8, 3)->nullable();

            $table->decimal('mm_c_min', 8, 3)->nullable();
            $table->decimal('mm_c_max', 8, 3)->nullable();

            $table->decimal('mm_gt_min', 8, 3)->nullable();
            $table->decimal('mm_gt_max', 8, 3)->nullable();

            $table->decimal('mm_cb_mm_min', 8, 3)->nullable();
            $table->decimal('mm_cb_mm_max', 8, 3)->nullable();

            $table->decimal('mm_cb_lab_min', 8, 3)->nullable();
            $table->decimal('mm_cb_lab_max', 8, 3)->nullable();

            $table->decimal('mm_m_min', 8, 3)->nullable();
            $table->decimal('mm_m_max', 8, 3)->nullable();

            $table->decimal('mm_bakunetsu_min', 8, 3)->nullable();
            $table->decimal('mm_bakunetsu_max', 8, 3)->nullable();

            $table->decimal('mm_ac_min', 8, 3)->nullable();
            $table->decimal('mm_ac_max', 8, 3)->nullable();

            $table->decimal('mm_tc_min', 8, 3)->nullable();
            $table->decimal('mm_tc_max', 8, 3)->nullable();

            $table->decimal('mm_vsd_min', 8, 3)->nullable();
            $table->decimal('mm_vsd_max', 8, 3)->nullable();

            $table->decimal('mm_ig_min', 8, 3)->nullable();
            $table->decimal('mm_ig_max', 8, 3)->nullable();

            $table->decimal('mm_cb_weight_min', 8, 3)->nullable();
            $table->decimal('mm_cb_weight_max', 8, 3)->nullable();

            $table->decimal('mm_tp50_weight_min', 8, 3)->nullable();
            $table->decimal('mm_tp50_weight_max', 8, 3)->nullable();

            $table->decimal('mm_tp50_height_min', 8, 3)->nullable();
            $table->decimal('mm_tp50_height_max', 8, 3)->nullable();

            $table->decimal('mm_ssi_min', 8, 3)->nullable();
            $table->decimal('mm_ssi_max', 8, 3)->nullable();

            // ==== BC SAMPLE ====
            $table->decimal('bc12_cb_min', 8, 3)->nullable();
            $table->decimal('bc12_cb_max', 8, 3)->nullable();

            $table->decimal('bc12_m_min', 8, 3)->nullable();
            $table->decimal('bc12_m_max', 8, 3)->nullable();

            $table->decimal('bc11_ac_min', 8, 3)->nullable();
            $table->decimal('bc11_ac_max', 8, 3)->nullable();

            $table->decimal('bc11_vsd_min', 8, 3)->nullable();
            $table->decimal('bc11_vsd_max', 8, 3)->nullable();

            $table->decimal('bc16_cb_min', 8, 3)->nullable();
            $table->decimal('bc16_cb_max', 8, 3)->nullable();

            $table->decimal('bc16_m_min', 8, 3)->nullable();
            $table->decimal('bc16_m_max', 8, 3)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_jsh_standards');
    }
};
