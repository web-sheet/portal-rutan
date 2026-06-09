<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Services\ItemRequestService;
use Illuminate\Http\Request;

class ItemRequestController extends Controller
{
    // PUBLIC: submit request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_name' => 'required',
            'division' => 'required',
            'item_id' => 'required|exists:items,id',
            'stock_requested' => 'required|integer|min:1',
        ]);

        $item = Item::findOrFail($validated['item_id']);

        // optional: cek stok cukup
        if ($item->stock < $validated['stock_requested']) {
            return response()->json([
                'message' => 'Stok tidak cukup'
            ], 422);
        }

        $data = ItemRequest::create([
            'employee_name' => $validated['employee_name'],
            'division' => $validated['division'],
            'item_id' => $item->id,
            'item_name' => $item->name,
            'category' => $item->category,
            'stock_requested' => $validated['stock_requested'],
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return response()->json($data);
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

 
    public function approveKasi(Request $request, $id)
    {
        $itemRequest = ItemRequest::findOrFail($id);

        $qty = $request->input('stock_requested');

        $this->service->approveKasi($itemRequest, $qty);

        return response()->json([
            'message' => 'Approved Kasi'
        ]);
    }

    public function reject($id)
    {
        $request = ItemRequest::findOrFail($id);

        $this->service->reject($request);

        return response()->json(['message' => 'Rejected']);
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
}
