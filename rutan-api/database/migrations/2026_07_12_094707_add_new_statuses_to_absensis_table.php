<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Langsung panggil DB::statement di sini, tanpa dibungkus Schema::table
        DB::statement("ALTER TABLE absensis MODIFY COLUMN status ENUM(
            'hadir',
            'tidak hadir',
            'izin',
            'tanpa keterangan',
            'sakit',
            'cuti',
            'cuti alasan penting',
            'cuti melahirkan',
            'alpha',
            'dinas_luar',
            'dinas_luar_full',
            'dinas_luar_half',
            'lepas_piket',
            'pengganti_libur'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum semula jika migration di-rollback
        DB::statement("ALTER TABLE absensis MODIFY COLUMN status ENUM(
            'hadir',
            'tidak hadir',
            'izin',
            'tanpa keterangan',
            'sakit',
            'cuti',
            'cuti alasan penting',
            'cuti melahirkan',
            'alpha',
            'dinas_luar',
            'dinas_luar_full',
            'dinas_luar_half'
        ) NOT NULL");
    }
};