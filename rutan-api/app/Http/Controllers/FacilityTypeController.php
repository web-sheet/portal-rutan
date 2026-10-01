<?php

namespace App\Http\Controllers;

use App\Models\FacilityType;
use App\Models\Location;
use Illuminate\Http\Request;

class FacilityTypeController extends Controller
{
    public function index()
    {
        return response()->json(FacilityType::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $facility = FacilityType::create(['name' => $request->name]);

        return response()->json(['message' => 'Fasilitas berhasil ditambahkan', 'data' => $facility], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $item = Location::findOrFail($id);
        $item->update(['name' => $request->name]);

        return response()->json(['message' => 'Data berhasil diperbarui', 'data' => $item]);
    }

    public function destroy($id)
    {
        $item = Location::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}
