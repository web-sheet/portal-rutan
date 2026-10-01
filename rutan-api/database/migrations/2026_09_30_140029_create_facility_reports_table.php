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
      Schema::create('facility_reports', function (Blueprint $table) {
            $table->id();
            
            // Relasi Pelapor (Pegawai)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Relasi Lokasi & Jenis Fasilitas
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('facility_type_id')->constrained('facility_types')->onDelete('cascade');
            
            // Data Input Pelapor
            $table->text('description'); // Kendala / Kerusakan
            $table->string('photo_before'); // Path foto before
            
            // Status Laporan
            $table->enum('status', ['pending', 'completed'])->default('pending'); 
            // pending = Belum Ditindaklanjuti, completed = Sudah Ditindaklanjuti

            // Data Input Kaur Perlengkapan / Sarpras (Nullable saat awal buat laporan)
            $table->foreignId('handler_id')->nullable()->constrained('users')->onDelete('set null'); // Kaur Perlengkapan
            $table->string('budget_source')->nullable(); // Sumber Anggaran (DIPA, Non-DIPA, dll)
            $table->decimal('cost', 15, 2)->nullable(); // Nominal Biaya
            $table->string('photo_after')->nullable(); // Path foto after
            $table->text('repair_notes')->nullable(); // Catatan perbaikan
            $table->timestamp('handled_at')->nullable(); // Waktu perbaikan selesai

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_reports');
    }
};
