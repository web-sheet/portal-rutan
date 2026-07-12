<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Models\Item;
use App\Models\ItemRequest;
use App\Models\StockHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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







  

    // TAHAP 1 STAF: Barang Disiapkan & Stok Dikurangi
    public function readyStaf(ItemRequest $request, int $qty)
    {
        $item = Item::find($request->item_id);

        if ($item->stock < $qty) {
            throw new \Exception("Stok tidak cukup");
        }


        DB::transaction(function () use ($item, $request, $qty) {
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
                'status' => RequestStatus::READY->value, // Jauh lebih aman dan rapi
                'confirmed_by_staff_at' => now(),
                'confirmed_by_staff_by' => auth()->user()->name ?? 'system',
            ]);
        });
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
