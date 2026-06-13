<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use MongoDB\BSON\Regex;

/**
 * Fungsi: CRUD laporan dari sisi admin.
 *         Bisa filter berdasarkan status, hapus laporan,
 *         dan ubah status laporan.
 */
class LaporanAdminController extends Controller
{
    // Tampilkan semua laporan, bisa difilter by status dan search
    public function index(Request $request)
    {
        $status = $request->query('status'); // 'dicari' | 'ditemukan' | null
        $search = $request->query('search');

        $query = Report::with('user')->orderBy('created_at', 'desc');

        // Filter hanya kalau ada parameter status
        if ($status && in_array($status, ['dicari', 'ditemukan'])) {
            $query->where('status', $status);
        }

        // Search by title, location, or description
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'regex', new Regex($search, 'i'))
                  ->orWhere('location', 'regex', new Regex($search, 'i'))
                  ->orWhere('description', 'regex', new Regex($search, 'i'));
            });
        }

        // Pakai kelipatan 12 agar pas dengan grid layout (3 atau 4 kolom)
        $laporan = $query->paginate(12);

        return view('admin.laporan.index', compact('laporan', 'status', 'search'));
    }

    // Detail satu laporan
    public function show($id)
    {
        $laporan = Report::with('user')->findOrFail($id);
        return view('admin.laporan.show', compact('laporan'));
    }

    // Ubah status laporan (dicari <-> ditemukan)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:dicari,ditemukan',
        ]);

        $laporan = Report::findOrFail($id);
        $laporan->update(['status' => $request->status]);

        return back()->with('success', 'Status laporan berhasil diperbarui.');
    }

    // Hapus laporan
    public function destroy($id)
    {
        $laporan = Report::findOrFail($id);
        $laporan->delete();

        return back()->with('success', 'Laporan berhasil dihapus.');
    }
}