<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tb_gfn_aceline', function (Blueprint $table) {
            $table->id();

            $table->date('gfn_date')->nullable();
            $table->enum('shift', ['D','S','N'])->nullable();
            $table->string('mesh')->nullable();               // ex: 18,5 / 26 / PAN
            $table->decimal('gram', 8, 2)->default(0);
            $table->decimal('percentage', 8, 2)->default(0);
            $table->integer('index')->nullable();
            $table->decimal('percentage_index', 10, 2)->default(0);

            // total snapshot (disalin ke setiap baris detail)
            $table->decimal('total_gram', 10, 2)->default(0);
            $table->decimal('total_percentage_index', 12, 2)->default(0);

            $table->timestamps();

            // index untuk filter cepat
            $table->index(['gfn_date', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_gfn_aceline');
    }
};
