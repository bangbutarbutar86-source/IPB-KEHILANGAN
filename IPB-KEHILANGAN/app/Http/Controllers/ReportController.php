<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

// 🔥 CLOUDINARY
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class ReportController extends Controller
{
    //  HOME (FEED)
    public function index()
    {
        $reports = Report::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('home', compact('reports'));
    }
    // 📌 STORE (UPLOAD)
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:Dicari,Ditemukan',
            'location'    => 'required|string|max:255',
            'images'      => 'required|array|min:1|max:3',
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'required|string',
        ]);

        // CONFIG CLOUDINARY
        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => ['secure' => true]
        ]);

        // MULTI UPLOAD
        $imageUrls = [];

        foreach ($request->file('images') as $file) {
            $result = (new UploadApi())->upload(
                $file->getRealPath(),
                ['folder' => 'reports']
            );

            $imageUrls[] = $result['secure_url'];
        }

        // 💾 SIMPAN DATA
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:Dicari,Ditemukan',
            'location'    => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // 🚫 Anti spam (maks 5 upload per hari)
        if (Report::where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->count() >= 5
        ) {
            return back()->with('error', 'Batas upload harian tercapai');
        }

        // 🧠 Normalisasi status berdasarkan type
        $status = strtolower($validated['type']) === 'dicari' ? 'dicari' : 'ditemukan';

        // 💾 Simpan data
        Report::create([
            'user_id'     => auth()->id(),
            'title'       => $validated['title'],
            'type'        => $validated['type'],
            'location'    => $validated['location'],
            'images'      => $imageUrls ?? [], // aman kalau kosong
            'description' => $validated['description'],
            'status'      => $status
        ]);

        return redirect()->route('home')->with('success', 'Laporan berhasil diupload!');
    }

    // DETAIL
    public function show($id)
    {
        $report = Report::with('user')->findOrFail($id);

        $relatedReports = Report::where('type', $report->type)
            ->where('_id', '!=', $id)
            ->limit(4)
            ->get();

        return view('detail', compact('report', 'relatedReports'));
    }

    //  EDIT
    public function edit($id)
    {
        $report = Report::findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() != $report->user_id) {
            abort(403);
        }

        return view('edit', compact('report'));
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() != $report->user_id) {
            abort(403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:Dicari,Ditemukan',
            'location'    => 'required|string|max:255',
            'description' => 'required|string',
            'images'      => 'nullable|array|max:3',
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->only(['title', 'type', 'location', 'description']);

        // 🔥 UPDATE GAMBAR
        if ($request->hasFile('images')) {

            Configuration::instance([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
                'url' => ['secure' => true]
            ]);

            $imageUrls = [];

            foreach ($request->file('images') as $file) {
                $result = (new UploadApi())->upload(
                    $file->getRealPath(),
                    ['folder' => 'reports']
                );

                $imageUrls[] = $result['secure_url'];
            }

            $data['images'] = $imageUrls;
        }

        $report->update($data);

        return redirect()->route('home')->with('success', 'Data berhasil diupdate!');
    }
    // DELETE
    public function destroy($id)
    {
        $report = Report::findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() != $report->user_id) {
            abort(403);
        }

        $report->delete();

        return redirect()->route('home')->with('success', 'Postingan berhasil dihapus!');
    }

    // MY REPORTS
    public function myReports()
    {
        $reports = Report::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('laporan', compact('reports'));
    }

    // TOGGLE STATUS
    public function toggleStatus($id)
    {
        $report = Report::findOrFail($id);

        if (auth()->id() != $report->user_id) {
            abort(403);
        }

        $report->status = $report->status === 'selesai' ? 'dicari' : 'selesai';
        $report->save();

        return back()->with('success', 'Status berhasil diubah!');
    }
}
