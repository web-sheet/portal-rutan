<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\StockHistory;
use Illuminate\Support\Facades\Auth;

class ItemRequestService
{
    public function approveKaur(ItemRequest $request, int $qty)
    {
        $request->update([
            'adjusted_stock_requested' => $qty,
            'status' => RequestStatus::APPROVED_KAUR,
            'approved_kaur_at' => now(),
            'approved_kaur_by' => auth()->user()->name ?? 'system',
        ]);
    }



  

public function approveStaf(ItemRequest $request, int $qty)
{
    $item = Item::find($request->item_id);

    // 1. Validasi Stok
    if ($item->stock < $qty) {
        throw new \Exception("Stok tidak cukup");
    }

    $stockBefore = $item->stock;

    // 2. Kurangi Stok
    $item->decrement('stock', $qty);

    $stockAfter = $item->fresh()->stock;

    // 3. Catat Riwayat Stok
    StockHistory::create([
        'item_id' => $item->id,
        'item_request_id' => $request->id,
        'item_name' => $item->name,
        'quantity' => $qty,
        'type' => 'OUT',
        'stock_before' => $stockBefore,
        'stock_after' => $stockAfter,
        'action_by' => auth()->user()->name ?? 'system',
    ]);

    // 4. Update Status Permohonan
    // Kita set status ke 'confirmed_by_staff'
    $request->update([
        'final_approved_stock' => $qty,
       'status' => RequestStatus::CONFIRMED_BY_STAFF,
        
        // Sesuaikan kolom berikut dengan nama kolom di database Anda
        'confirmed_by_staff_at' => now(),
        'confirmed_by_staff_by' => auth()->user()->name ?? 'system',
        
        // Karena ini langkah terakhir, kita set juga completed_at
        'completed_at' => now(), 
    ]);
}
    public function reject(ItemRequest $request)
    {
        $request->update([
            'status' => RequestStatus::REJECTED,

            'rejected_at' => now(),
            'rejected_by' => auth()->user()->name ?? 'system',
        ]);
    }
}
