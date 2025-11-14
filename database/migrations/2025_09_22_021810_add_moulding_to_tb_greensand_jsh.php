<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tb_greensand_jsh', function (Blueprint $table) {
            // Data molding
            $table->decimal('add_water_mm', 8, 2)->nullable()->after('bc11_temp');
            $table->decimal('add_water_mm_2', 8, 2)->nullable()->after('add_water_mm');
            $table->decimal('temp_sand_mm_1', 8, 2)->nullable()->after('add_water_mm_2');
            $table->decimal('rcs_pick_up', 8, 2)->nullable()->after('temp_sand_mm_1');
            $table->integer('total_flask')->nullable()->after('rcs_pick_up');
            $table->decimal('rcs_avg', 8, 2)->nullable()->after('total_flask');
            $table->decimal('add_bentonite_ma', 8, 2)->nullable()->after('rcs_avg');
            $table->decimal('total_sand', 10, 2)->nullable()->after('add_bentonite_ma');
        });
    }

    public function down(): void
    {
        Schema::table('tb_greensand_jsh', function (Blueprint $table) {
            $table->dropColumn([
                'add_water_mm',
                'add_water_mm_2',
                'temp_sand_mm_1',
                'rcs_pick_up',
                'total_flask',
                'rcs_avg',
                'add_bentonite_ma',
                'total_sand',
            ]);
        });
    }
};
