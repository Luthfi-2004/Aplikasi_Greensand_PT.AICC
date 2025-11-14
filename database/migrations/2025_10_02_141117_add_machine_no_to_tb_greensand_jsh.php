<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tb_greensand_jsh', function (Blueprint $t) {
            $t->string('machine_no', 50)->nullable()->after('mm_m');
        });
    }

    public function down(): void
    {
        Schema::table('tb_greensand_jsh', function (Blueprint $t) {
            $t->dropColumn('machine_no');
        });
    }

};
