<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facility_reports', function (Blueprint $table) {
            // Hapus kolom user_id jika masih ada (tanpa drop foreign key)
            if (Schema::hasColumn('facility_reports', 'user_id')) {
                $table->dropColumn('user_id');
            }

            // Tambah kolom reporter_id jika belum ada
            if (!Schema::hasColumn('facility_reports', 'reporter_id')) {
                $table->foreignId('reporter_id')
                      ->after('id')
                      ->constrained('pegawais')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('facility_reports', function (Blueprint $table) {
            if (Schema::hasColumn('facility_reports', 'reporter_id')) {
                $table->dropForeign(['reporter_id']);
                $table->dropColumn('reporter_id');
            }
        });
    }
};