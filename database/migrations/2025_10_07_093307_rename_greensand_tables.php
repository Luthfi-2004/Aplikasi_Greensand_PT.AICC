<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1
        if (Schema::hasTable('tb_ace_standards') && ! Schema::hasTable('tb_greensand_std_ace')) {
            Schema::rename('tb_ace_standards', 'tb_greensand_std_ace');
        }

        // 2
        if (Schema::hasTable('tb_jsh_standards') && ! Schema::hasTable('tb_greensand_std_jsh')) {
            Schema::rename('tb_jsh_standards', 'tb_greensand_std_jsh');
        }

        // 3
        if (Schema::hasTable('tb_gfn_aceline') && ! Schema::hasTable('tb_greensand_gfn_ace')) {
            Schema::rename('tb_gfn_aceline', 'tb_greensand_gfn_ace');
        }

        // 4
        if (Schema::hasTable('tb_gfn_jsh') && ! Schema::hasTable('tb_greensand_gfn_jsh')) {
            Schema::rename('tb_gfn_jsh', 'tb_greensand_gfn_jsh');
        }

        // 5
        if (Schema::hasTable('tb_greensand_ace') && ! Schema::hasTable('tb_greensand_check_ace')) {
            Schema::rename('tb_greensand_ace', 'tb_greensand_check_ace');
        }

        // 6
        if (Schema::hasTable('tb_greensand_jsh') && ! Schema::hasTable('tb_greensand_check_jsh')) {
            Schema::rename('tb_greensand_jsh', 'tb_greensand_check_jsh');
        }

        // 7
        if (Schema::hasTable('tb_total_gfn_aceline') && ! Schema::hasTable('tb_greensand_gfn_total_ace')) {
            Schema::rename('tb_total_gfn_aceline', 'tb_greensand_gfn_total_ace');
        }

        // 8
        if (Schema::hasTable('tb_total_gfn') && ! Schema::hasTable('tb_greensand_gfn_total_jsh')) {
            Schema::rename('tb_total_gfn', 'tb_greensand_gfn_total_jsh');
        }
    }

    public function down(): void
    {
        // rollback: balikkan semua
        if (Schema::hasTable('tb_greensand_std_ace') && ! Schema::hasTable('tb_ace_standards')) {
            Schema::rename('tb_greensand_std_ace', 'tb_ace_standards');
        }

        if (Schema::hasTable('tb_greensand_std_jsh') && ! Schema::hasTable('tb_jsh_standards')) {
            Schema::rename('tb_greensand_std_jsh', 'tb_jsh_standards');
        }

        if (Schema::hasTable('tb_greensand_gfn_ace') && ! Schema::hasTable('tb_gfn_aceline')) {
            Schema::rename('tb_greensand_gfn_ace', 'tb_gfn_aceline');
        }

        if (Schema::hasTable('tb_greensand_gfn_jsh') && ! Schema::hasTable('tb_gfn_jsh')) {
            Schema::rename('tb_greensand_gfn_jsh', 'tb_gfn_jsh');
        }

        if (Schema::hasTable('tb_greensand_check_ace') && ! Schema::hasTable('tb_greensand_ace')) {
            Schema::rename('tb_greensand_check_ace', 'tb_greensand_ace');
        }

        if (Schema::hasTable('tb_greensand_check_jsh') && ! Schema::hasTable('tb_greensand_jsh')) {
            Schema::rename('tb_greensand_check_jsh', 'tb_greensand_jsh');
        }

        if (Schema::hasTable('tb_greensand_gfn_total_ace') && ! Schema::hasTable('tb_total_gfn_aceline')) {
            Schema::rename('tb_greensand_gfn_total_ace', 'tb_total_gfn_aceline');
        }

        if (Schema::hasTable('tb_greensand_gfn_total_jsh') && ! Schema::hasTable('tb_total_gfn')) {
            Schema::rename('tb_greensand_gfn_total_jsh', 'tb_total_gfn');
        }
    }
};
