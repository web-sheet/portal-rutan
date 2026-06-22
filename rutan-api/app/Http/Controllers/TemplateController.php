<?php
// app/Http/Controllers/TemplateController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    public function getTemplate()
    {
        // Ambil template absensi, jika belum ada buat default kosongan
        $template = DB::table('templates')->where('key', 'absensi_pdf')->first();
        
        return response()->json([
            'html' => $template ? $template->html : null
        ]);
    }

    public function saveTemplate(Request $request)
    {
        $request->validate(['html' => 'required|string']);

        DB::table('templates')->updateOrInsert(
            ['key' => 'absensi_pdf'],
            [
                'html' => $request->html,
                'updated_at' => now()
            ]
        );

        return response()->json(['message' => 'Template berhasil disimpan']);
    }
}