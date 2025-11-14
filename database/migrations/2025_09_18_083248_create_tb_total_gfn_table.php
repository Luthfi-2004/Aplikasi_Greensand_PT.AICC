<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_total_gfn', function (Blueprint $table) {
            $table->id();

            // metadata input (tanpa batch/login)
            $table->date('gfn_date')->nullable();
            $table->enum('shift', ['D','S','N'])->nullable();

            // hasil utama
            $table->decimal('nilai_gfn', 10, 2)->default(0);

            // rekap %
            $table->decimal('mesh_total140', 8, 2)->default(0); // % mesh 140
            $table->decimal('mesh_total70', 8, 2)->default(0);  // Σ% mesh 50 + 70 + 100
            $table->decimal('meshpan', 8, 2)->default(0);       // Σ% mesh 280 + PAN

            // judge
            $table->string('judge_mesh_140', 8)->default('NG');
            $table->string('judge_mesh_70', 8)->default('NG');
            $table->string('judge_meshpan', 8)->default('NG');

            // opsional tapi berguna
            $table->decimal('total_gram', 10, 2)->default(0);
            $table->decimal('total_percentage_index', 12, 2)->default(0);

            $table->timestamps();

            // index untuk filter cepat
            $table->index(['gfn_date', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_total_gfn');
    }
};
