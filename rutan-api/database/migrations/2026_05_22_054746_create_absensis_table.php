<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pegawai_id')
                ->constrained()
                ->onDelete('cascade');

            $table->date('tanggal');

            $table->enum('status', [
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
            ]);

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
