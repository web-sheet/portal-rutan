<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        return response()->json(Location::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $location = Location::create(['name' => $request->name]);

        return response()->json(['message' => 'Lokasi berhasil ditambahkan', 'data' => $location], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $location = Location::findOrFail($id);
        $location->update(['name' => $request->name]);

        return response()->json(['message' => 'Lokasi berhasil diperbarui', 'data' => $location]);
    }

    public function destroy($id)
    {
        Location::findOrFail($id)->delete();
        return response()->json(['message' => 'Lokasi berhasil dihapus']);
    }
}