<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tb_greensand_jsh', function (Blueprint $table) {
            $table->decimal('add_water_bc10', 8, 2)->nullable()->change();
            $table->decimal('lama_bc10_jalan', 8, 2)->nullable()->change();
            $table->decimal('rating_pasir_es', 8, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tb_greensand_jsh', function (Blueprint $table) {
            $table->integer('add_water_bc10')->nullable()->change();
            $table->integer('lama_bc10_jalan')->nullable()->change();
            $table->string('rating_pasir_es')->nullable()->change();
        });
    }
};
