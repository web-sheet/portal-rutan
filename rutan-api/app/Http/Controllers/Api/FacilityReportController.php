<?php

namespace App\Http\Controllers\Api;


use App\Models\FacilityReport;
use App\Models\FacilityType;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class FacilityReportController extends Controller
{
    // 1. Get Master Data Option untuk Dropdown FE
    public function getOptions()
    {
        return response()->json([
            'locations' => Location::all(['id', 'name']),
            'facility_types' => FacilityType::all(['id', 'name']),
        ]);
    }

    // 2. Get List Laporan (Paginated & Filterable)
    public function index(Request $request)
    {
        $query = FacilityReport::with(['reporter:id,nama', 'handler:id,name', 'location', 'facilityType'])
            ->latest();

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(10));
    }

    // 3. Post Laporan Kerusakan Baru (Oleh Pegawai)
    public function store(Request $request)
    {
        $request->validate([
            'reporter_id'      => 'required|exists:pegawais,id',
            'location_id' => 'required|exists:locations,id',
            'facility_type_id' => 'required|exists:facility_types,id',
            'description' => 'required|string',
            'photo_before' => 'required|image|mimes:jpg,jpeg,png|max:5120', // Max 5MB
        ]);

        $photoPath = $request->file('photo_before')->store('reports/before', 'public');

        $report = FacilityReport::create([
            'reporter_id' => $request->reporter_id, // Mengambil ID Pegawai dari dropdown
            'location_id' => $request->location_id,
            'facility_type_id' => $request->facility_type_id,
            'description' => $request->description,
            'photo_before' => $photoPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Laporan kerusakan berhasil dibuat',
            'data' => $report
        ], 201);
    }



    // FacilityReportController.php
    public function updateRepair(Request $request, $id)
    {
        $report = FacilityReport::findOrFail($id);

        $request->validate([
            'budget_source' => 'required|string',
            'cost'          => 'nullable|numeric|min:0',
            // UBAH 'required' MENJADI 'nullable'
            'photo_after'   => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'repair_notes'  => 'nullable|string',
        ]);

        $data = [
            'handler_id'    => auth()->id(),
            'repaired_at'   => $request->repaired_at ? Carbon::parse($request->repaired_at) : now(),
            'budget_source' => $request->budget_source,
            'cost'          => $request->cost,
            'repair_notes'  => $request->repair_notes,
            'status'        => 'completed',
            'handled_at'    => now(),
        ];

        // Hanya update foto jika user mengunggah foto baru
        if ($request->hasFile('photo_after')) {
            $photoPath = $request->file('photo_after')->store('reports/after', 'public');
            $data['photo_after'] = $photoPath;
        }

        $report->update($data);

        return response()->json([
            'message' => 'Laporan perbaikan berhasil diperbarui',
            'data'    => $report
        ]);
    }



    public function destroy($id)
    {
        $report = FacilityReport::findOrFail($id);
        $report->delete();

        return response()->json([
            'message' => 'Laporan berhasil dihapus'
        ]);
    }

    // 5. Data Dashboard & Grafik
    // public function dashboardStats()
    // {
    //     $totalReports = FacilityReport::count();
    //     $completedReports = FacilityReport::where('status', 'completed')->count();
    //     $pendingReports = FacilityReport::where('status', 'pending')->count();

    //     // Grafik Laporan Terbanyak per Jenis Fasilitas
    //     $byFacility = FacilityReport::select('facility_types.name', DB::raw('count(*) as total'))
    //         ->join('facility_types', 'facility_reports.facility_type_id', '=', 'facility_types.id')
    //         ->groupBy('facility_types.name')
    //         ->orderByDesc('total')
    //         ->get();

    //     if ($byFacility->count() > 8) {
    //         $top = $byFacility->take(7);
    //         $othersTotal = $byFacility->skip(7)->sum('total');

    //         $byFacility = $top->push([
    //             'name' => 'Lain-lain',
    //             'total' => $othersTotal
    //         ]);
    //     } else {
    //         $byFacility = $byFacility;
    //     }

    //     // Grafik Laporan Terbanyak per Lokasi
    //     $byLocation = FacilityReport::select('locations.name', DB::raw('count(*) as total'))
    //         ->join('locations', 'facility_reports.location_id', '=', 'locations.id')
    //         ->groupBy('locations.name')
    //         ->get();

    //     // PERBAIKAN GRAFIK BULANAN:
    //     // Cek tahun dari laporan terbaru jika ada data, jika tidak pakai tahun ini
    //     $latestReportYear = FacilityReport::latest('created_at')->value('created_at')
    //         ? FacilityReport::latest('created_at')->value('created_at')->format('Y')
    //         : date('Y');

    //     $rawMonthly = FacilityReport::select(
    //         DB::raw('MONTH(created_at) as month'),
    //         DB::raw('COUNT(*) as total')
    //     )
    //         ->whereYear('created_at', $latestReportYear)
    //         ->groupBy(DB::raw('MONTH(created_at)'))
    //         ->pluck('total', 'month')
    //         ->toArray();

    //     // Buat struktur 12 Bulan (Jan - Des)
    //     $monthlyReports = [];
    //     for ($m = 1; $m <= 12; $m++) {
    //         // Pastikan nilai di-cast ke (int)
    //         $monthlyReports[] = [
    //             'month' => $m,
    //             'total' => (int) ($rawMonthly[$m] ?? 0)
    //         ];
    //     }

    //     // Galeri Foto Before After (10 Laporan Selesai Terakhir)
    //     $gallery = FacilityReport::where('status', 'completed')
    //         ->whereNotNull('photo_after')
    //         ->with(['location', 'facilityType'])
    //         ->latest('handled_at')
    //         ->limit(10)
    //         ->get(['id', 'location_id', 'facility_type_id', 'photo_before', 'photo_after', 'handled_at']);

    //     return response()->json([
    //         'summary' => [
    //             'total' => $totalReports,
    //             'completed' => $completedReports,
    //             'pending' => $pendingReports,
    //         ],
    //         'chart_by_facility' => $byFacility,
    //         'chart_by_location' => $byLocation,
    //         'chart_monthly' => $monthlyReports,
    //         'gallery' => $gallery,
    //     ]);
    // }

    public function dashboardStats(Request $request)
    {
        // 1. Ambil tahun dari request (Default: tahun berjalan)
        $year = $request->input('year', date('Y'));

        // 2. Summary counts berdasarkan tahun yang dipilih
        $totalReports = FacilityReport::whereYear('created_at', $year)->count();
        $completedReports = FacilityReport::whereYear('created_at', $year)
            ->where('status', 'completed')
            ->count();
        $pendingReports = FacilityReport::whereYear('created_at', $year)
            ->where('status', 'pending')
            ->count();

        // 3. Grafik Laporan Terbanyak per Jenis Fasilitas (Filtered by Year)
        $byFacility = FacilityReport::select('facility_types.name', DB::raw('count(*) as total'))
            ->join('facility_types', 'facility_reports.facility_type_id', '=', 'facility_types.id')
            ->whereYear('facility_reports.created_at', $year)
            ->groupBy('facility_types.name')
            ->orderByDesc('total')
            ->get();

        // 4. Grafik Laporan Terbanyak per Lokasi (Filtered by Year)
        $byLocation = FacilityReport::select('locations.name', DB::raw('count(*) as total'))
            ->join('locations', 'facility_reports.location_id', '=', 'locations.id')
            ->whereYear('facility_reports.created_at', $year)
            ->groupBy('locations.name')
            ->orderByDesc('total')
            ->get();

        // 5. Grafik Laporan Masuk per Bulan (12 Bulan untuk Tahun yang Dipilih)
        $rawMonthly = FacilityReport::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('total', 'month')
            ->toArray();

        // Susun struktur 12 bulan
        $monthlyReports = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyReports[] = [
                'month' => $m,
                'total' => (int) ($rawMonthly[$m] ?? 0)
            ];
        }

        // 6. Galeri Foto Before/After (Laporan Selesai pada Tahun yang Dipilih)
        $gallery = FacilityReport::where('status', 'completed')
            ->whereNotNull('photo_after')
            ->whereYear('created_at', $year)
            ->with(['location', 'facilityType'])
            ->latest('handled_at')
            ->limit(10)
            ->get(['id', 'location_id', 'facility_type_id', 'photo_before', 'photo_after', 'handled_at']);

        return response()->json([
            'year' => (int) $year,
            'summary' => [
                'total' => $totalReports,
                'completed' => $completedReports,
                'pending' => $pendingReports,
            ],
            'chart_by_facility' => $byFacility,
            'chart_by_location' => $byLocation,
            'chart_monthly' => $monthlyReports,
            'gallery' => $gallery,
        ]);
    }
}
