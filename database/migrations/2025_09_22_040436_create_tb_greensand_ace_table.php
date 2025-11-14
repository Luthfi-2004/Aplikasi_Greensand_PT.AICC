<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tb_greensand_ace', function (Blueprint $table) {
            $table->id();

            // Meta waktu & shift
            $table->timestamp('date')->nullable();
            $table->enum('shift', ['D', 'S', 'N'])->nullable();

            // Product type
            $table->foreignId('product_type_id')->nullable()->index();
            $table->string('product_type_name', 100)->nullable();

            // Nomor / urutan
            $table->integer('number')->nullable();
            $table->integer('no_mix')->nullable();

            // Sample taking time
            $table->time('sample_start')->nullable();
            $table->time('sample_finish')->nullable();

            // Parameter utama
            $table->decimal('p', 8, 2)->nullable();
            $table->decimal('c', 8, 2)->nullable();
            $table->decimal('gt', 8, 2)->nullable();
            $table->decimal('cb_lab', 8, 2)->nullable();
            $table->decimal('moisture', 8, 2)->nullable();

            // Mesin & kondisi
            $table->string('machine_no', 50)->nullable();
            $table->decimal('bakunetsu', 8, 2)->nullable();
            $table->decimal('ac', 8, 2)->nullable();
            $table->decimal('tc', 8, 2)->nullable();
            $table->decimal('vsd', 8, 2)->nullable();
            $table->decimal('ig', 8, 2)->nullable();

            // Bobot
            $table->decimal('cb_weight', 8, 2)->nullable();
            $table->decimal('tp50_weight', 8, 2)->nullable();

            // Lain-lain
            $table->decimal('ssi', 8, 2)->nullable();

            // DW29
            $table->decimal('dw29_vas', 8, 2)->nullable();
            $table->decimal('dw29_debu', 8, 2)->nullable();

            // DW31
            $table->decimal('dw31_vas', 8, 2)->nullable();
            $table->decimal('dw31_id', 8, 2)->nullable();
            $table->decimal('dw31_moldex', 8, 2)->nullable();
            $table->decimal('dw31_sc', 8, 2)->nullable();

            // BC13
            $table->decimal('bc13_cb', 8, 2)->nullable();
            $table->decimal('bc13_c', 8, 2)->nullable();
            $table->decimal('bc13_m', 8, 2)->nullable();

            // Index
            $table->index(['date', 'shift'], 'ace_date_shift_index');
            $table->index(['sample_start', 'sample_finish'], 'ace_sample_time_index');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_greensand_ace');
    }
};
