<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tb_greensand_jsh', function (Blueprint $t) {
            // letakkan setelah total_sand
            $t->integer('add_water_bc10')->nullable()->after('total_sand');
            $t->integer('lama_bc10_jalan')->nullable()->after('add_water_bc10');
            $t->string('rating_pasir_es', 50)->nullable()->after('lama_bc10_jalan');
        });
    }

    public function down(): void {
        Schema::table('tb_greensand_jsh', function (Blueprint $t) {
            $t->dropColumn(['add_water_bc10', 'lama_bc10_jalan', 'rating_pasir_es']);
        });
    }
};
