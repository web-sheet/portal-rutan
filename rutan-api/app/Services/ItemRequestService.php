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



  

    public function approveKasi(ItemRequest $request, int $qty)
    {
        $item = Item::find($request->item_id);

        if ($item->stock < $qty) {
            throw new \Exception("Stok tidak cukup");
        }

        $stockBefore = $item->stock;

        $item->decrement('stock', $qty);

        $stockAfter = $item->fresh()->stock;

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

        $request->update([
            'final_approved_stock' => $qty,

            'status' => RequestStatus::COMPLETED,

            'approved_kasi_at' => now(),
            'approved_kasi_by' => auth()->user()->name ?? 'system',

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
