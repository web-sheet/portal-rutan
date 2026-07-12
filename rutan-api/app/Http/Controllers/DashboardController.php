<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemRequest;


class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([

            'cards' => [
                'total_items' => Item::count(),
                'total_requests' => ItemRequest::count(),
                'pending_requests' => ItemRequest::where('status', 'pending')->count(),
                'low_stock' => Item::where('stock', '<=', 5)->count(),
            ],

            'low_stock_items' => Item::where('stock', '<=', 5)
                ->latest()
                ->take(5)
                ->get(),

            'top_requested_items' => ItemRequest::selectRaw('item_name, SUM(final_approved_stock) as total')
                ->whereNotNull('final_approved_stock')
                ->groupBy('item_name')
                ->orderByDesc('total')
                ->take(5)
                ->get(),

            'monthly_requests' => ItemRequest::selectRaw("
        MONTH(created_at) as month,
        COUNT(*) as total
    ")
                ->groupBy('month')
                ->orderBy('month')
                ->get(),


      
            'approval_queue' => ItemRequest::with('item')
                // Ambil SEMUA yang statusnya bukan 'completed' atau 'rejected'
                // supaya Vue bisa memfilter sesuai role login
                ->whereIn('status', ['pending', 'approved_kaur'])
                ->latest()
                ->take(30) // Tambahkan limit agar tidak terlalu berat
                ->get(),

        ]);
    }
}
