<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tb_greensand_jsh', function (Blueprint $table) {
            // taruh persis setelah mm_tp50_weight biar rapi
            $table->decimal('mm_tp50_height', 8, 2)->nullable()->after('mm_tp50_weight')
                  ->comment('Tinggi TP 50 (mm)');
        });
    }

    public function down(): void
    {
        Schema::table('tb_greensand_jsh', function (Blueprint $table) {
            $table->dropColumn('mm_tp50_height');
        });
    }
};
