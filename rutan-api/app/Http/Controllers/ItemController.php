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
}