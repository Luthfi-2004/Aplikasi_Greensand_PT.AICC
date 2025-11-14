<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tb_greensand_ace', function (Blueprint $table) {
            // Letakkan setelah 'ssi' sesuai urutan UI
            $table->decimal('most', 8, 2)->nullable()->after('ssi');
        });
    }

    public function down(): void
    {
        Schema::table('tb_greensand_ace', function (Blueprint $table) {
            $table->dropColumn('most');
        });
    }
};
