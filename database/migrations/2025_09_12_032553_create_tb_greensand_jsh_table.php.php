<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tb_greensand_jsh', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date')->nullable();
            $table->enum('shift', ['D', 'S', 'N'])->nullable();
            $table->string('mm')->nullable();       // MM1 / MM2
            $table->integer('mix_ke')->nullable();
            $table->time('mix_start')->nullable();
            $table->time('mix_finish')->nullable();

            // contoh beberapa kolom sample (lengkapi sesuai kebutuhan)
            $table->decimal('mm_p', 8, 2)->nullable();
            $table->decimal('mm_c', 8, 2)->nullable();
            $table->decimal('mm_gt', 8, 2)->nullable();
            $table->decimal('mm_cb_mm', 8, 2)->nullable();
            $table->decimal('mm_cb_lab', 8, 2)->nullable();
            $table->decimal('mm_m', 8, 2)->nullable();
            $table->decimal('mm_bakunetsu', 8, 2)->nullable();
            $table->decimal('mm_ac', 8, 2)->nullable();
            $table->decimal('mm_tc', 8, 2)->nullable();
            $table->decimal('mm_vsd', 8, 2)->nullable();
            $table->decimal('mm_ig', 8, 2)->nullable();
            $table->decimal('mm_cb_weight', 8, 2)->nullable();
            $table->decimal('mm_tp50_weight', 8, 2)->nullable();
            $table->decimal('mm_ssi', 8, 2)->nullable();

            // Additive
            $table->decimal('add_m3', 8, 2)->nullable();
            $table->decimal('add_vsd', 8, 2)->nullable();
            $table->decimal('add_sc', 8, 2)->nullable();

            // BC Sample
            $table->decimal('bc12_cb', 8, 2)->nullable();
            $table->decimal('bc12_m', 8, 2)->nullable();
            $table->decimal('bc11_ac', 8, 2)->nullable();
            $table->decimal('bc11_vsd', 8, 2)->nullable();
            $table->decimal('bc16_cb', 8, 2)->nullable();
            $table->decimal('bc16_m', 8, 2)->nullable();

            // Return Sand
            $table->time('rs_time')->nullable();
            $table->string('rs_type')->nullable();
            $table->decimal('bc9_moist', 8, 2)->nullable();
            $table->decimal('bc10_moist', 8, 2)->nullable();
            $table->decimal('bc11_moist', 8, 2)->nullable();
            $table->decimal('bc9_temp', 8, 2)->nullable();
            $table->decimal('bc10_temp', 8, 2)->nullable();
            $table->decimal('bc11_temp', 8, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_greensand_jsh');
    }
};
