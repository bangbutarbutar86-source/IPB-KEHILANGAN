<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * ============================================================
 *  BACKEND — Controller
 *  Lokasi di project: app/Http/Controllers/Admin/AdminController.php
 * ============================================================
 * Fungsi: Mengambil data dari MongoDB dan mengirimkannya
 *         ke halaman dashboard admin (view).
 *
 *  ⚠️ Sesuaikan nama Model dengan project kamu:
 *     - Report → nama model Report kamu
 *     - User    → nama model user kamu
 */
class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $search = $request->query('search');

        // Hitung statistik dari MongoDB
        $totalReport   = Report::count();
        $totalDicari    = Report::where('status', 'dicari')->count();
        $totalDitemukan = Report::where('status', 'ditemukan')->count();
        $totalUser      = User::count();

        // Ambil 10 Report terbaru untuk tabel di dashboard
        $query = Report::with('user')->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'regex', new \MongoDB\BSON\Regex($search, 'i'))
                  ->orWhere('location', 'regex', new \MongoDB\BSON\Regex($search, 'i'))
                  ->orWhere('description', 'regex', new \MongoDB\BSON\Regex($search, 'i'));
            });
        }

        $ReportTerbaru = $query->limit(10)->get();

        // Kirim data ke view dashboard
        return view('admin.dashboard', compact(
            'totalReport',
            'totalDicari',
            'totalDitemukan',
            'totalUser',
            'ReportTerbaru',
            'search'
        ));
    }
}