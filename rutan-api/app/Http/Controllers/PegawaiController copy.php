<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;

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

 
}
