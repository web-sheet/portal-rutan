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
    public function dashboardStats()
    {
        $totalReports = FacilityReport::count();
        $completedReports = FacilityReport::where('status', 'completed')->count();
        $pendingReports = FacilityReport::where('status', 'pending')->count();

        // Grafik Laporan Terbanyak per Jenis Fasilitas
        $byFacility = FacilityReport::select('facility_types.name', DB::raw('count(*) as total'))
            ->join('facility_types', 'facility_reports.facility_type_id', '=', 'facility_types.id')
            ->groupBy('facility_types.name')
            ->get();

        // Grafik Laporan Terbanyak per Lokasi
        $byLocation = FacilityReport::select('locations.name', DB::raw('count(*) as total'))
            ->join('locations', 'facility_reports.location_id', '=', 'locations.id')
            ->groupBy('locations.name')
            ->get();

        // Grafik Laporan Masuk per Bulan (Tahun Ini)
        $monthlyReports = FacilityReport::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('count(*) as total')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Galeri Foto Before After (10 Laporan Selesai Terakhir)
        $gallery = FacilityReport::where('status', 'completed')
            ->whereNotNull('photo_after')
            ->with(['location', 'facilityType'])
            ->latest('handled_at')
            ->limit(10)
            ->get(['id', 'location_id', 'facility_type_id', 'photo_before', 'photo_after', 'handled_at']);

        return response()->json([
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
