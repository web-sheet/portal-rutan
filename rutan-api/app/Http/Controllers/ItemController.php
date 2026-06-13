<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    // GET ALL
    public function index()
    {
        return Item::latest()->get();
    }

    // CREATE
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'category' => 'required',
            'stock' => 'required|integer',
            'description' => 'nullable',
        ]);

        return Item::create($data);
    }

    // UPDATE 🔥
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $data = $request->validate([
            'name' => 'required',
            'category' => 'required',
            'stock' => 'required|integer',
            'description' => 'nullable',
        ]);

        $item->update($data);

        return response()->json([
            'message' => 'updated',
            'data' => $item
        ]);
    }

    // DELETE 🔥
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'deleted'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:items,id' // memastikan ID-nya memang ada di database
        ]);

        // Menghapus semua ID yang dikirim dalam satu operasi database
        \App\Models\Item::whereIn('id', $request->ids)->delete();

        return response()->json([
            'status' => 'success',
            'message' => count($request->ids) . ' barang berhasil dihapus sekaligus.'
        ]);
    }

    public function importExcel(Request $request)
    {
        // 1. Validasi pastikan data yang dikirim berupa array barang
        $request->validate([
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.category' => 'required|string',
            'items.*.stock' => 'required|integer',
            'items.*.description' => 'nullable|string',
        ]);

        $items = $request->input('items');
        $dataToInsert = [];
        $timestamp = now(); // Ambil waktu sekarang untuk created_at dan updated_at

        // 2. Siapkan data beserta timestamp logistik rutan
        foreach ($items as $item) {
            $dataToInsert[] = [
                'name'        => $item['name'],
                'category'    => $item['category'],
                'stock'       => $item['stock'],
                'description' => $item['description'] ?? '-',
                'created_at'  => $timestamp,
                'updated_at'  => $timestamp,
            ];
        }

        // 3. Masukkan semua data sekaligus ke database dalam 1 query tunggal
        \App\Models\Item::insert($dataToInsert);

        return response()->json([
            'status' => 'success',
            'message' => count($dataToInsert) . ' barang berhasil di-import ke gudang rutan.'
        ]);
    }
}
