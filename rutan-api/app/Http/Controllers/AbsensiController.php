<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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



    public function getDashboardStats(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $tanggalHariIni = date('Y-m-d');

        // 1. Snapshot Cards
        $totalPegawai = Pegawai::where('status', 'aktif')->count();
        $hadirHariIni = Absensi::where('tanggal', $tanggalHariIni)
            ->where('status', 'hadir')->count();
        $izinSakitCuti = Absensi::where('tanggal', $tanggalHariIni)
            ->whereIn('status', ['izin', 'sakit', 'cuti'])
            ->count();

        $persentase = $totalPegawai > 0 ? round(($hadirHariIni / $totalPegawai) * 100) : 0;

        // 2. Tren Bulanan (Bar Chart)
        $trenData = Absensi::select(
            'tanggal',
            DB::raw("SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir"),
            DB::raw("SUM(CASE WHEN status != 'hadir' THEN 1 ELSE 0 END) as tidak_hadir")
        )
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->groupBy('tanggal')
            ->get();

        // 3. Distribusi Status (Pie Chart)
        $distribusi = Absensi::select('status', DB::raw('count(*) as total'))
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->groupBy('status')
            ->get();

        return response()->json([
            'cards' => [
                'persentase_kehadiran' => $persentase,
                'jumlah_hadir' => $hadirHariIni,
                'total_pegawai' => $totalPegawai,
                'total_izin_sakit_cuti' => $izinSakitCuti
            ],
            'chart_tren' => [
                'labels' => $trenData->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->format('d')),
                'hadir' => $trenData->pluck('hadir'),
                'tidak_hadir' => $trenData->pluck('tidak_hadir')
            ],
            'distribusi_status' => [
                'labels' => $distribusi->pluck('status')->map(fn($s) => ucfirst($s)),
                'data' => $distribusi->pluck('total')
            ]
        ]);
    }
}
