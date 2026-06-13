<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Fungsi: Admin bisa lihat semua user, ban user, dan hapus user.
 */
class UserAdminController extends Controller
{
    // Tampilkan semua user
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = User::orderBy('created_at', 'desc');

        // Search by nama atau email
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'regex', new \MongoDB\BSON\Regex($search, 'i'))
                  ->orWhere('email', 'regex', new \MongoDB\BSON\Regex($search, 'i'));
            });
        }

        $users = $query->paginate(15);

        return view('admin.users.index', compact('users', 'search'));
    }

    // Ban / unban user
    public function ban($id)
    {
        $user = User::findOrFail($id);

        // Toggle ban
        $user->update([
            'is_banned' => !$user->is_banned
        ]);

        $pesan = $user->is_banned ? 'User berhasil di-ban.' : 'User berhasil di-unban.';
        return back()->with('success', $pesan);
    }

    // Hapus user
    public function destroy($id)
    {
        // Jangan sampai admin hapus dirinya sendiri
        if (auth()->id() == $id) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        User::findOrFail($id)->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }
}