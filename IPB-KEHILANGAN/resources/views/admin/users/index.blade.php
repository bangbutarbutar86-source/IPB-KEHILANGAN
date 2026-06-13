{{-- ============================================================
     FRONTEND — Halaman Manajemen User
     Lokasi di project: resources/views/admin/users/index.blade.php
     ============================================================
     Data dari UserAdminController@index:
       - $users  (paginated collection)
       - $search (keyword pencarian)
--}}
@extends('admin.layouts.admin')

@section('page-title', 'Manajemen User')

@push('styles')
<style>
    .page-header {
        display: flex; align-items: center;
        justify-content: space-between; margin-bottom: 20px;
    }
    .search-form {
        display: flex; gap: 8px; align-items: center;
    }
    .search-input {
        border: 1px solid #e5e7eb; border-radius: 8px;
        padding: 8px 14px; font-size: 13px;
        color: #1a1a2e; outline: none; width: 260px;
    }
    .search-input:focus { border-color: #3D4BA0; }
    .btn-search {
        background: #3D4BA0; color: #fff; border: none;
        border-radius: 8px; padding: 8px 16px;
        font-size: 13px; cursor: pointer;
    }
    .btn-search:hover { background: #2d3880; }

    .table-wrap {
        background: #fff; border: 1px solid #e5e7eb;
        border-radius: 12px; overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: #f9fafb; }
    th {
        font-size: 11px; font-weight: 600; color: #6b7280;
        padding: 12px 16px; text-align: left;
        border-bottom: 1px solid #e5e7eb;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    td { font-size: 13px; padding: 13px 16px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f9fafb; }

    .user-chip { display: flex; align-items: center; gap: 10px; }
    .user-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: #eef0fa; color: #3D4BA0;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 600; flex-shrink: 0;
    }
    .user-name { font-weight: 500; color: #1a1a2e; }
    .user-email { font-size: 11px; color: #6b7280; }

    .badge { display: inline-block; font-size: 11px; font-weight: 500; padding: 4px 10px; border-radius: 20px; }
    .badge-aktif  { background: #eaf3de; color: #3b6d11; }
    .badge-banned { background: #fcebeb; color: #a32d2d; }
    .badge-admin  { background: #eef0fa; color: #3D4BA0; }

    .action-group { display: flex; gap: 6px; }
    .btn-sm {
        font-size: 11px; font-weight: 500; padding: 5px 12px;
        border-radius: 6px; border: 1px solid #e5e7eb;
        background: #fff; color: #374151; cursor: pointer;
        transition: all 0.15s;
    }
    .btn-sm:hover { background: #f3f4f6; }
    .btn-ban   { color: #854f0b; border-color: #fac775; }
    .btn-ban:hover   { background: #faeeda; }
    .btn-unban { color: #3b6d11; border-color: #c0dd97; }
    .btn-unban:hover { background: #eaf3de; }
    .btn-del   { color: #a32d2d; border-color: #fecaca; }
    .btn-del:hover   { background: #fcebeb; }

    .pagination-wrap {
        display: flex; justify-content: flex-end;
        padding: 16px 20px; border-top: 1px solid #f3f4f6;
    }
    .empty-state { text-align: center; padding: 60px; color: #6b7280; font-size: 14px; }
</style>
@endpush

@section('content')

<div class="page-header">
    <form class="search-form" method="GET" action="{{ route('admin.users.index') }}">
        <input type="text" name="search" value="{{ $search }}"
               class="search-input" placeholder="Cari nama atau email...">
        <button type="submit" class="btn-search">Cari</button>
        @if($search)
            <a href="{{ route('admin.users.index') }}"
               style="font-size:12px; color:#6b7280; text-decoration:none;">✕ Reset</a>
        @endif
    </form>
    <div style="font-size:13px; color:#6b7280;">
        Total: <strong>{{ $users->total() }}</strong> user
    </div>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Role</th>
                <th>Bergabung</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $i => $user)
            <tr>
                <td style="color:#9ca3af; font-size:12px;">{{ $users->firstItem() + $i }}</td>
                <td>
                    <div class="user-chip">
                        <div class="user-avatar">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="user-name">{{ $user->name }}</div>
                            <div class="user-email">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @if($user->role === 'admin')
                        <span class="badge badge-admin">Admin</span>
                    @else
                        <span style="font-size:12px; color:#6b7280;">User</span>
                    @endif
                </td>
                <td style="font-size:12px; color:#6b7280;">
                    {{ \Carbon\Carbon::parse($user->created_at)->format('d M Y') }}
                </td>
                <td>
                    @if($user->is_banned)
                        <span class="badge badge-banned">Dibanned</span>
                    @else
                        <span class="badge badge-aktif">Aktif</span>
                    @endif
                </td>
                <td>
                    <div class="action-group">
                        {{-- Jangan tampilkan aksi untuk akun admin sendiri --}}
                        @if(auth()->id() != $user->id)

                            {{-- Ban / Unban --}}
                            <form action="{{ route('admin.users.ban', $user->id) }}"
                                  method="POST">
                                @csrf @method('PATCH')
                                @if($user->is_banned)
                                    <button type="submit" class="btn-sm btn-unban">Unban</button>
                                @else
                                    <button type="submit" class="btn-sm btn-ban">Ban</button>
                                @endif
                            </form>

                            {{-- Hapus --}}
                            <form action="{{ route('admin.users.destroy', $user->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin hapus user {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-sm btn-del">Hapus</button>
                            </form>

                        @else
                            <span style="font-size:11px; color:#9ca3af;">(Akun kamu)</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="empty-state">Tidak ada user ditemukan.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
    <div class="pagination-wrap">
        {{ $users->appends(request()->query())->links() }}
    </div>
    @endif
</div>

@endsection