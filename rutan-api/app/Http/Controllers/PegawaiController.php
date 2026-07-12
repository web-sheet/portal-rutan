<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Tambahkan ini untuk database transaction
use Illuminate\Support\Facades\Validator; // Tambahkan ini untuk validasi data massal

class PegawaiController extends Controller
{
    // GET all
    public function index()
    {
        return response()->json(Pegawai::latest()->get());
    }

    // POST
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'nip' => 'nullable|unique:pegawais',
            'jabatan' => 'nullable',
            'pangkat' => 'nullable',
            'golongan' => 'nullable',
            'ttd' => 'nullable',
            'status' => 'nullable',
        ]);

        $pegawai = Pegawai::create($validated);

        return response()->json([
            'message' => 'Data berhasil ditambahkan',
            'data' => $pegawai
        ], 201);
    }

    // GET by id
    public function show(string $id)
    {
        return response()->json(
            Pegawai::findOrFail($id)
        );
    }

    // PUT
    public function update(Request $request, string $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required',
            'nip' => 'nullable|unique:pegawais,nip,' . $pegawai->id,
            'pangkat' => 'nullable',
            'ttd' => 'nullable',
            'jabatan' => 'nullable',
            'golongan' => 'nullable',
            'status' => 'nullable',
        ]);

        $pegawai->update($validated);

        return response()->json([
            'message' => 'Data berhasil diupdate',
            'data' => $pegawai
        ]);
    }

    // DELETE
    public function destroy(string $id)
    {
        Pegawai::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);
    }

    /**
     * FUNGSI BARU: 5. Import Bulk dari Excel
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.nama' => 'required|string',
            'data.*.nip' => 'required|string',
        ], [
            'data.*.nama.required' => 'Ada kolom Nama yang kosong di file Excel Anda.',
            'data.*.nip.required' => 'Ada kolom NIP yang kosong di file Excel Anda.'
        ]);

        $items = $request->input('data');
        $successCount = 0;

        // Gunakan database transaction agar data konsisten jika terjadi error di tengah jalan
        DB::beginTransaction();
        try {
            foreach ($items as $index => $item) {
                // Validasi manual per baris untuk mendeteksi NIP duplikat
                $validator = Validator::make($item, [
                    'nip' => 'unique:pegawais,nip'
                ]);

                if ($validator->fails()) {
                    // Beri tahu baris ke berapa yang duplikat (Index Excel biasanya baris ke-2 setelah header)
                    $rowNum = $index + 2;
                    return response()->json([
                        'message' => "Gagal import. NIP '{$item['nip']}' pada baris ke-{$rowNum} sudah terdaftar di database."
                    ], 422);
                }

                Pegawai::create([
                    'nama' => $item['nama'],
                    'nip' => $item['nip'],
                    'jabatan' => $item['jabatan'] ?? '-',
                    'pangkat' => $item['pangkat'] ?? '-',
                    'golongan' => $item['golongan'] ?? '-',
                    'status' => $item['status'] ?? 'aktif',
                ]);

                $successCount++;
            }

            DB::commit();

            return response()->json([
                'message' => "Berhasil mengimpor {$successCount} data pegawai.",
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat memproses baris data excel.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * FUNGSI BARU: 6. Hapus Massal (Bulk Delete)
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pegawais,id' // Memastikan ID yang dikirim valid & ada di DB
        ], [
            'ids.required' => 'Tidak ada data pegawai yang dipilih untuk dihapus.',
            'ids.*.exists' => 'Salah satu data pegawai tidak ditemukan di database.'
        ]);

        $ids = $request->input('ids');

        // Eksekusi penghapusan massal
        Pegawai::whereIn('id', $ids)->delete();

        return response()->json([
            'message' => count($ids) . ' data pegawai berhasil dihapus secara massal.'
        ], 200);
    }
}
