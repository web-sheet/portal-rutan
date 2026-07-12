<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Services\ItemRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Tambahkan ini untuk database transaction
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ItemRequestController extends Controller
{

    public function store(Request $request)
    {
        // Menggunakan Validator manual untuk menangkap error
        $validator = Validator::make($request->all(), [
            'employee_name' => 'required',
            'division'      => 'required',
            'items'         => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            // LOG INI AKAN MEMBERI TAHU KITA PERSIS FIELD MANA YANG SALAH
            Log::error('Validasi Gagal:', $validator->errors()->toArray());
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }


        $requestCode = 'REQ-' . Carbon::now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        $now = Carbon::now();

        try {
            DB::transaction(function () use ($request,$requestCode) {
                foreach ($request->items as $itemData) {
                    $item = Item::findOrFail($itemData['item_id']);

                    if ($item->stock < $itemData['qty']) {
                        throw new \Exception("Stok {$item->name} tidak cukup");
                    }

                    ItemRequest::create([

                        'request_code'    => $requestCode,
                        'employee_name'    => $request->employee_name,
                        'division'         => $request->division,
                        'item_id'          => $item->id,
                        'item_name'        => $item->name,
                        'category'         => $item->category,
                        'stock_requested'  => $itemData['qty'],
                        'signature'        => $request->signature,
                        'status'           => 'pending',
                        'requested_at'     => now(),
                    ]);
                }
            });

            return response()->json(['message' => 'Berhasil mengajukan permintaan'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }


    public function byEmployee($name)
    {
        return ItemRequest::with('item')
            ->where('employee_name', $name)
            ->latest()
            ->get();
    }

    // ADMIN (nanti dipakai)
    public function index()
    {
        return ItemRequest::with('item')
            ->latest()
            ->get()
            ->append(['final_stock_requested']);
    }
    public function __construct(
        private ItemRequestService $service
    ) {}

    public function approveKaur(Request $request, $id)
    {
        $itemRequest = ItemRequest::findOrFail($id);

        $qty = $request->input('stock_requested');

        $this->service->approveKaur($itemRequest, $qty);

        return response()->json(['message' => 'Approved Kaur']);
    }


    /* 1. EDIT & GANTI: approveStaf yang lama dipecah menjadi readyStaf */
    public function readyStaf(Request $request, $id)
    {
        $itemRequest = ItemRequest::findOrFail($id);

        // Ambil qty yang dikirim dari frontend, jika kosong gunakan data approval kaur sebelumnya
        $qty = $request->input('stock_requested', $itemRequest->adjusted_stock_requested);

        try {
            $this->service->readyStaf($itemRequest, $qty);
            return response()->json(['message' => 'Barang berhasil disiapkan dan stok telah dikurangi']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }


    public function reject($id)
    {
        $request = ItemRequest::findOrFail($id);

        $this->service->reject($request);

        return response()->json(['message' => 'Rejected']);
    }

    // Backend Laravel
    public function rejectBulk(Request $request)
    {
        $request->validate(['ids' => 'required|array']);

        // Update semua status menjadi 'rejected'
        ItemRequest::whereIn('id', $request->ids)->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->user()->name
        ]);

        return response()->json(['message' => 'Success']);
    }


    public function destroy($id)
    {
        $request = ItemRequest::findOrFail($id);

        // optional safety: jangan hapus kalau sudah completed
        if ($request->status === 'completed') {
            return response()->json([
                'message' => 'Request yang sudah selesai tidak bisa dihapus'
            ], 422);
        }

        $request->delete();

        return response()->json([
            'message' => 'Request berhasil dihapus'
        ]);
    }

    public function approveBulk(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'items' => 'required|array'
        ]);

        $type = $request->input('type');

        try {
            DB::transaction(function () use ($type, $request) {

                // --- KHUSUS UNTUK SELESAI STAF (GENERATE SATU NOMOR BON SAMA) ---
                if ($type === 'staf_complete') {
                    $currentYear = Carbon::now()->year;

                    // 1. Ambil nomor urut tertinggi satu kali saja di awal
                    $lastBonInCurrentYear = DB::table('item_requests')
                        ->whereNotNull('bon_number')
                        ->whereYear('completed_at', $currentYear)
                        ->max('bon_number');

                    // 2. Tentukan nomor urut berikutnya (satu nomor untuk semua item di batch ini)
                    $nextBonNumber = $lastBonInCurrentYear ? (int)$lastBonInCurrentYear + 1 : 1;

                    // 3. Buat format string nota klien
                    $paddedNumber = str_pad($nextBonNumber, 3, '0', STR_PAD_LEFT);
                    $formatNomorKlien = "W.10.PAS-PAS.12-PB.02.01-{$paddedNumber}";

                    $now = Carbon::now();

                    // 4. Kumpulkan semua ID item yang dikirim dari frontend
                    $itemIds = collect($request->items)->pluck('id')->toArray();

                    // 5. Eksekusi SATU QUERY untuk semua barang (Pasti dapet nomor sama & bypass Enum Cast)
                    DB::table('item_requests')
                        ->whereIn('id', $itemIds)
                        ->where('status', '!=', 'completed') // Hanya update yang belum selesai
                        ->update([
                            'status'               => 'completed',
                            'completed_at'         => $now,
                            'bon_number'           => $nextBonNumber,
                            'bon_number_formatted' => $formatNomorKlien,
                            'updated_at'           => $now
                        ]);
                } else {
                    // --- UNTUK APPROVAL LAIN (KAUR / STAF READY) TETAP PAKAI SERVICE LAMA ---
                    foreach ($request->items as $itemData) {
                        $req = ItemRequest::findOrFail($itemData['id']);

                        if ($type === 'perlengkapan') {
                            $this->service->approveKaur($req, $itemData['qty']);
                        } elseif ($type === 'staf_ready') {
                            $this->service->readyStaf($req, $itemData['qty']);
                        }
                    }
                }
            });

            return response()->json(['message' => 'Proses massal berhasil diselesaikan']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
