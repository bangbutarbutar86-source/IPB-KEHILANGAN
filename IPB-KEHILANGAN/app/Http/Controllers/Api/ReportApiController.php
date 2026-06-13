<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use MongoDB\BSON\Regex;

class ReportApiController extends Controller
{
    // 📱 FEED (LIST DATA)
    public function index(Request $request)
    {
        $query = Report::with('user')
            ->where('status', '!=', 'selesai')
            ->orderBy('created_at', 'desc');

        // 🔍 FILTER TYPE
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // 🔍 SEARCH (MongoDB regex)
        if ($request->filled('search')) {
            $query->where('title', 'regex', new Regex($request->search, 'i'));
        }

        $reports = $query->paginate(10);

        return response()->json([
            'message' => 'Data berhasil diambil',
            'data' => $reports
        ]);
    }

    // 📄 DETAIL
    public function show($id)
    {
        $report = Report::with('user')->find($id);

        if (!$report) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Detail data',
            'data' => $report
        ]);
    }

    // 📤 UPLOAD REPORT
    public function mine(Request $request)
    {
        $query = Report::with('user')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('title', 'regex', new Regex($request->search, 'i'));
        }

        if ($request->status === 'selesai') {
            $query->where('status', 'selesai');
        } elseif ($request->status === 'belum_selesai') {
            $query->where('status', '!=', 'selesai');
        } elseif ($request->filled('type')) {
            $query->where('type', $request->type)
                ->where('status', '!=', 'selesai');
        }

        return response()->json([
            'message' => 'Data laporan saya',
            'data' => $query->get()
        ]);
    }

    public function store(Request $request)
    {
        // 🔐 CEK LOGIN
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Dicari,Ditemukan',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'images' => 'required|array|min:1|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // 🚫 ANTI SPAM (maks 5 per hari)
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        if (Report::where('user_id', auth()->id())
            ->where('created_at', '>=', $todayStart)
            ->where('created_at', '<=', $todayEnd)
            ->count() >= 5) {
            return response()->json([
                'message' => 'Batas upload harian tercapai'
            ], 429);
        }

        // 🧠 NORMALISASI DATA
        $type = ucfirst(strtolower($request->type)); // Dicari / Ditemukan
        $status = strtolower($request->type); // dicari / ditemukan
        $imageUrls = [];

        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => ['secure' => true]
        ]);

        foreach ($request->file('images') as $file) {
            $result = (new UploadApi())->upload(
                $file->getRealPath(),
                ['folder' => 'reports']
            );

            $imageUrls[] = $result['secure_url'];
        }

        $report = Report::create([
            'user_id'     => auth()->id(),
            'title'       => $request->title,
            'type'        => $type,
            'location'    => $request->location,
            'description' => $request->description,
            'images'      => $imageUrls,
            'status'      => $status
        ]);

        return response()->json([
            'message' => 'Berhasil upload',
            'data' => $report
        ]);
    }

    // ✏️ UPDATE
    public function update(Request $request, $id)
    {
        $report = Report::find($id);

        if (!$report) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // 🔐 AUTH CHECK + ADMIN
        if (!auth()->check() || ($report->user_id != auth()->id() && auth()->user()->role !== 'admin')) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'type' => 'nullable|in:Dicari,Ditemukan',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:dicari,ditemukan,selesai,diabaikan'
        ]);

        $data = $request->only([
            'title',
            'type',
            'location',
            'description',
            'status'
        ]);

        // 🧠 NORMALISASI TYPE (kalau diubah)
        if (isset($data['type'])) {
            $data['type'] = ucfirst(strtolower($data['type']));

            if (!isset($data['status']) && $report->status !== 'selesai') {
                $data['status'] = strtolower($data['type']);
            }
        }

        $report->update($data);

        return response()->json([
            'message' => 'Berhasil update',
            'data' => $report
        ]);
    }

    // 🗑️ DELETE
    public function destroy($id)
    {
        $report = Report::find($id);

        if (!$report) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // 🔐 AUTH CHECK + ADMIN
        if (!auth()->check() || ($report->user_id != auth()->id() && auth()->user()->role !== 'admin')) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        $report->delete();

        return response()->json([
            'message' => 'Berhasil dihapus'
        ]);
    }
}
