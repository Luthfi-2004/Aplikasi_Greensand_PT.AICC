<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('tb_ace_standards', function (Blueprint $t) {
      $t->id();

      // --- MM Sample ---
      $t->decimal('p_min', 10, 3)->nullable();
      $t->decimal('p_max', 10, 3)->nullable();
      $t->decimal('c_min', 10, 3)->nullable();
      $t->decimal('c_max', 10, 3)->nullable();
      $t->decimal('gt_min', 10, 3)->nullable();
      $t->decimal('gt_max', 10, 3)->nullable();
      $t->decimal('cb_lab_min', 10, 3)->nullable();
      $t->decimal('cb_lab_max', 10, 3)->nullable();
      $t->decimal('moisture_min', 10, 3)->nullable();
      $t->decimal('moisture_max', 10, 3)->nullable();

      $t->decimal('bakunetsu_min', 10, 3)->nullable();
      $t->decimal('bakunetsu_max', 10, 3)->nullable();
      $t->decimal('ac_min', 10, 3)->nullable();
      $t->decimal('ac_max', 10, 3)->nullable();
      $t->decimal('tc_min', 10, 3)->nullable();
      $t->decimal('tc_max', 10, 3)->nullable();
      $t->decimal('vsd_min', 10, 3)->nullable();
      $t->decimal('vsd_max', 10, 3)->nullable();
      $t->decimal('ig_min', 10, 3)->nullable();
      $t->decimal('ig_max', 10, 3)->nullable();

      $t->decimal('cb_weight_min', 10, 3)->nullable();
      $t->decimal('cb_weight_max', 10, 3)->nullable();
      $t->decimal('tp50_weight_min', 10, 3)->nullable();
      $t->decimal('tp50_weight_max', 10, 3)->nullable();
      $t->decimal('ssi_min', 10, 3)->nullable();
      $t->decimal('ssi_max', 10, 3)->nullable();

      // machine_no & most itu non-judge → tidak perlu kolom (boleh tambah kalau mau, tapi tak dipakai judge)

      // --- BC13 ---
      $t->decimal('bc13_cb_min', 10, 3)->nullable();
      $t->decimal('bc13_cb_max', 10, 3)->nullable();
      $t->decimal('bc13_c_min', 10, 3)->nullable();
      $t->decimal('bc13_c_max', 10, 3)->nullable();
      $t->decimal('bc13_m_min', 10, 3)->nullable();
      $t->decimal('bc13_m_max', 10, 3)->nullable();

      $t->timestamps();
    });
  }

  public function down(): void {
    Schema::dropIfExists('tb_ace_standards');
  }
};
