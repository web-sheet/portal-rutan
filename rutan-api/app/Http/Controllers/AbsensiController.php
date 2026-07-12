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
            // Ganti 'required' menjadi 'nullable' agar nilai null/reset bisa lolos validasi
            'status' => 'nullable|in:hadir,izin,sakit,cuti,alpha,dinas_luar,lepas_piket,pengganti_libur',
        ]);

        // Jika status bernilai null (ini adalah perintah Reset dari frontend)
        if (is_null($request->status)) {
            // Cari dan langsung hapus baris absensinya dari database agar bersih
            Absensi::where('pegawai_id', $validated['pegawai_id'])
                ->where('tanggal', $validated['tanggal'])
                ->delete();

            return response()->json([
                'message' => 'Absensi berhasil di-reset (dihapus)',
                'data' => null
            ]);
        }

        // Jika status berisi (hadir, izin, dll), lakukan update atau create seperti biasa
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

    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'pegawai_ids' => 'required|array',
            'pegawai_ids.*' => 'exists:pegawais,id',
            'tanggal' => 'required|date',
            'status' => 'nullable|in:hadir,izin,sakit,cuti,alpha,dinas_luar,lepas_piket,pengganti_libur',
        ]);

        $tanggal = $validated['tanggal'];
        $status = $validated['status'];
        $pegawaiIds = $validated['pegawai_ids'];

        // Bungkus dengan Database Transaction agar aman dan cepat
        DB::transaction(function () use ($pegawaiIds, $tanggal, $status) {
            if (is_null($status)) {
                // Jika reset, hapus semua data absensi pegawai terpilih pada tanggal tersebut sekaligus
                Absensi::whereIn('pegawai_id', $pegawaiIds)
                    ->where('tanggal', $tanggal)
                    ->delete();
            } else {
                // Jika isi status, lakukan insert/update massal
                foreach ($pegawaiIds as $id) {
                    Absensi::updateOrCreate(
                        ['pegawai_id' => $id, 'tanggal' => $tanggal],
                        ['status' => $status]
                    );
                }
            }
        });

        return response()->json([
            'message' => 'Absensi massal berhasil diproses'
        ]);
    }






    public function getDashboardStats(Request $request)
    {
        $tanggalHariIni = $request->input('tanggal', date('Y-m-d'));

        $bulan = $request->filled('tanggal')
            ? date('m', strtotime($tanggalHariIni))
            : $request->input('bulan', date('m'));

        $tahun = $request->filled('tanggal')
            ? date('Y', strtotime($tanggalHariIni))
            : $request->input('tahun', date('Y'));

        // 1. Snapshot Cards
        $totalPegawai = Pegawai::where('status', 'aktif')->count();

        // AMAN: Ditambahkan pengecekan status aktif pegawai
        $hadirHariIni = Absensi::where('tanggal', $tanggalHariIni)
            ->where('status', 'hadir')
            ->whereHas('pegawai', function ($q) {
                $q->where('status', 'aktif');
            })
            ->count();

        // AMAN: Ditambahkan pengecekan status aktif pegawai
        $izinSakitCuti = Absensi::where('tanggal', $tanggalHariIni)
            ->whereIn('status', ['izin', 'sakit', 'cuti', 'alpha', 'lepas_piket', 'pengganti_libur'])
            ->whereHas('pegawai', function ($q) {
                $q->where('status', 'aktif');
            })
            ->count();

        $persentase = $totalPegawai > 0 ? round(($hadirHariIni / $totalPegawai) * 100) : 0;

        // 2. Tren Bulanan (Bar Chart)
        // AMAN: Ditambahkan pengecekan status aktif pegawai agar mantan pegawai tidak merusak tren
        $trenData = Absensi::select(
            'tanggal',
            DB::raw("SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir"),
            DB::raw("SUM(CASE WHEN status != 'hadir' THEN 1 ELSE 0 END) as tidak_hadir")
        )
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->whereHas('pegawai', function ($q) {
                $q->where('status', 'aktif');
            })
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // 3. Distribusi Status (Pie Chart)
        // AMAN: Menggunakan whereHas untuk menyaring absensi milik pegawai aktif saja
        $absensiHariIni = Absensi::whereHas('pegawai', function ($q) {
            $q->where('status', 'aktif');
        })
            ->with('pegawai:id,nama')
            ->where('tanggal', $tanggalHariIni)
            ->get();

        $statuses = ['hadir', 'izin', 'sakit', 'cuti', 'alpha', 'lepas_piket', 'pengganti_libur'];
        $chartLabels = [];
        $chartData = [];
        $pegawaiList = [];

        foreach ($statuses as $status) {
            $filteredAbsensi = $absensiHariIni->where('status', $status);
            $chartLabels[] = ucfirst($status);
            $chartData[] = $filteredAbsensi->count();
            $pegawaiList[] = $filteredAbsensi->map(fn($a) => $a->pegawai ? $a->pegawai->nama : 'Tanpa Nama')->values()->toArray();
        }

        // 4. Rekap Izin Pegawai Bulanan (SUDAH AMAN dari awal)

        $rekapanIzin = Pegawai::where('status', 'aktif')
            ->withCount(['absensi' => function ($query) use ($bulan, $tahun) {
                $query->where('status', 'izin')
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun);
            }])
            ->with(['absensi' => function ($query) use ($bulan, $tahun) {
                $query->where('status', 'izin')
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun) // Sudah diperbaiki menjadi 'tanggal'
                    ->orderBy('tanggal', 'asc');
            }])
            ->get()
            ->filter(function ($pegawai) {
                return $pegawai->absensi_count > 0;
            })
            ->map(function ($pegawai) {
                $listAbsensi = $pegawai->absensi ?: collect([]);
                return [
                    'nama' => $pegawai->nama,
                    'total_izin' => $pegawai->absensi_count,
                    'tanggal_list' => $listAbsensi->map(function ($a) {
                        return \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d F Y');
                    })->values()->toArray()
                ];
            })
            ->sortByDesc('total_izin')
            ->values();

        // 5. Detail Tidak Hadir Harian (Modal Pop-up Baru)
        // AMAN: Ditambahkan pengecekan status aktif pegawai
        $detailTidakHadir = Absensi::with('pegawai:id,nama')
            ->where('tanggal', $tanggalHariIni)
            ->whereIn('status', ['izin', 'sakit', 'cuti', 'alpha', 'lepas_piket', 'pengganti_libur'])
            ->whereHas('pegawai', function ($q) {
                $q->where('status', 'aktif');
            })
            ->get()
            ->map(function ($absensi) {
                return [
                    'nama' => $absensi->pegawai ? $absensi->pegawai->nama : 'Tanpa Nama',
                    'status' => ucfirst($absensi->status)
                ];
            })
            ->values();

        return response()->json([
            'cards' => [
                'persentase_kehadiran' => $persentase,
                'jumlah_hadir' => $hadirHariIni,
                'total_pegawai' => $totalPegawai,
                'total_izin_sakit_cuti' => $izinSakitCuti,
                'detail_tidak_hadir' => $detailTidakHadir
            ],
            'chart_tren' => [
                'labels' => $trenData->pluck('tanggal')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d')),
                'hadir' => $trenData->pluck('hadir'),
                'tidak_hadir' => $trenData->pluck('tidak_hadir')
            ],
            'distribusi_status' => [
                'labels' => $chartLabels,
                'data' => $chartData,
                'pegawai_list' => $pegawaiList
            ],
            'rekap_izin_pegawai' => [
                'labels' => $rekapanIzin->pluck('nama'),
                'data' => $rekapanIzin->pluck('total_izin'),
                'detail_tanggal' => $rekapanIzin->pluck('tanggal_list')
            ]
        ]);
    }
}
