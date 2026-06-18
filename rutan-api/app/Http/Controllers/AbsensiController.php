<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{

 

    public function index()
    {
        $bulan = request('bulan');
        $tahun = request('tahun');

        $pegawais = Pegawai::where('status', 'aktif')->with([
            'absensi' => function ($query) use ($bulan, $tahun) {

                $query->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun);
            }
        ])->get();

        $result = $pegawais->map(function ($pegawai) {

            $dataAbsensi = [];

            foreach ($pegawai->absensi as $item) {

                $tanggal = date('j', strtotime($item->tanggal));

                $dataAbsensi[$tanggal] = $item->status;
            }

            return [
                'id' => $pegawai->id,
                'nama' => $pegawai->nama,
                'nip' => $pegawai->nip,
                'jabatan' => $pegawai->jabatan,
                'pangkat' => $pegawai->pangkat,
                'golongan' => $pegawai->golongan,
                'status' => $pegawai->status,
                'absensi' => $dataAbsensi,
            ];
        });

        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,cuti,alpha,dinas_luar',
        ]);

        $absensi = Absensi::updateOrCreate(
            [
                'pegawai_id' => $validated['pegawai_id'],
                'tanggal' => $validated['tanggal'],
            ],
            [
                'status' => $validated['status'],
            ]
        );

        return response()->json([
            'message' => 'Absensi berhasil disimpan',
            'data' => $absensi
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Absensi $absensi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Absensi $absensi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Absensi $absensi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Absensi $absensi)
    {
        //
    }
}
