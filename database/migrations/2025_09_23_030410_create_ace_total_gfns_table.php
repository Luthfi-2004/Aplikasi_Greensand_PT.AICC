<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tb_total_gfn_aceline', function (Blueprint $table) {
            $table->id();

            $table->date('gfn_date')->nullable();
            $table->enum('shift', ['D','S','N'])->nullable();

            // ringkasan + judge
            $table->decimal('nilai_gfn', 8, 2)->default(0);            // Σ %Index / 100
            $table->decimal('mesh_total140', 8, 2)->default(0);        // % mesh 140
            $table->decimal('mesh_total70', 8, 2)->default(0);         // Σ % mesh 50+70+100
            $table->decimal('meshpan', 8, 2)->default(0);              // % mesh 280 + PAN

            $table->string('judge_mesh_140', 8)->nullable();           // OK / NG
            $table->string('judge_mesh_70', 8)->nullable();
            $table->string('judge_meshpan', 8)->nullable();

            $table->decimal('total_gram', 10, 2)->default(0);
            $table->decimal('total_percentage_index', 12, 2)->default(0);

            $table->timestamps();

            $table->index(['gfn_date', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_total_gfn_aceline');
    }
};
