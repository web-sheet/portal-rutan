<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Services\ItemRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Tambahkan ini untuk database transaction
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->items as $itemData) {
                    $item = Item::findOrFail($itemData['item_id']);

                    if ($item->stock < $itemData['qty']) {
                        throw new \Exception("Stok {$item->name} tidak cukup");
                    }

                    ItemRequest::create([
                        'employee_name'    => $request->employee_name,
                        'division'         => $request->division,
                        'item_id'          => $item->id,
                        'item_name'        => $item->name,
                        'category'         => $item->category,
                        'stock_requested'  => $itemData['qty'],
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


    public function approveStaf(Request $request, $id)
    {
        // 1. Ambil data permohonan
        $itemRequest = ItemRequest::findOrFail($id);

        // 2. Ambil quantity dari request
        $qty = $request->input('stock_requested');

        // 3. Panggil method di service (Anda harus membuat/mengupdate method ini di Service class)
        $this->service->approveStaf($itemRequest, $qty);

        return response()->json([
            'message' => 'Permohonan berhasil dikonfirmasi oleh Staf Perlengkapan'
        ]);
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
        $type = $request->input('type'); // 'perlengkapan' atau 'staf_perlengkapan'

        foreach ($request->items as $itemData) {
            $req = ItemRequest::findOrFail($itemData['id']);

            if ($type === 'perlengkapan') {
                // Panggil logic service yang sudah Anda buat untuk Kaur
                $this->service->approveKaur($req, $itemData['qty']);
            } elseif ($type === 'staf_perlengkapan') {
                // Panggil logic service yang sudah Anda buat untuk Staf
                $this->service->approveStaf($req, $itemData['qty']);
            }
        }

        return response()->json(['message' => 'Berhasil']);
    }
}
