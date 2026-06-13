{{-- ============================================================
     FRONTEND — Halaman Kelola Laporan (Tampilan Grid Card)
     Lokasi di project: resources/views/admin/laporan/index.blade.php
     ============================================================
     Data dari LaporanAdminController@index:
       - $laporan (paginated collection of Report)
       - $status  (filter aktif saat ini)
       - $search  (pencarian aktif saat ini)
--}}
@extends('admin.layouts.admin')

@section('page-title', 'Kelola Laporan')

@push('styles')
<style>
    .page-header {
        display: flex; 
        align-items: center;
        justify-content: space-between; 
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .filter-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .filter-pill {
        font-size: 12px; padding: 6px 16px;
        border-radius: 20px; border: 1px solid #e5e7eb;
        color: #6b7280; background: #fff;
        text-decoration: none; transition: all 0.15s;
    }
    .filter-pill:hover { border-color: #3D4BA0; color: #3D4BA0; }
    .filter-pill.active { background: #3D4BA0; color: #fff; border-color: #3D4BA0; }

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

    /* Reports Card Grid */
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 30px;
        width: 100%;
    }
    .report-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
    }
    .report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
    }
    .card-header {
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f3f4f6;
    }
    .reporter-info {
        display: flex;
        flex-direction: column;
    }
    .reporter-name {
        font-size: 13px;
        font-weight: 600;
        color: #1a1a2e;
    }
    .report-time {
        font-size: 11px;
        color: #6b7280;
        margin-top: 1px;
    }
    .badge {
        display: inline-block; 
        font-size: 10px; 
        font-weight: 600; 
        padding: 4px 10px; 
        border-radius: 20px; 
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .badge-dicari    { background: #fcebeb; color: #a32d2d; }
    .badge-ditemukan { background: #eaf3de; color: #3b6d11; }
    .badge-selesai   { background: #e0f2fe; color: #0369a1; }

    .card-img-wrap {
        position: relative;
        height: 180px;
        background: #f9fafb;
    }
    .card-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .card-body {
        padding: 16px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .card-title {
        font-size: 14px;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 4px;
    }
    .card-location {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 8px;
    }
    .card-desc {
        font-size: 12px;
        color: #4b5563;
        line-height: 1.5;
        margin-bottom: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .card-actions {
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        gap: 8px;
    }
    .card-actions form {
        flex-grow: 1;
    }
    .btn-action {
        width: 100%;
        display: block;
        font-size: 11px; 
        font-weight: 500; 
        padding: 8px 12px;
        border-radius: 6px; 
        border: 1px solid #e5e7eb;
        background: #fff; 
        color: #374151; 
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        transition: all 0.15s;
        box-sizing: border-box;
    }
    .btn-action:hover { background: #f3f4f6; }
    .btn-green { color: #3b6d11; border-color: #c0dd97; }
    .btn-green:hover { background: #eaf3de; }
    .btn-blue  { color: #3D4BA0; border-color: #b5d4f4; }
    .btn-blue:hover  { background: #eef0fa; }
    .btn-red   { color: #a32d2d; border-color: #fecaca; }
    .btn-red:hover   { background: #fcebeb; }

    .pagination-wrap {
        display: flex; justify-content: flex-end; padding: 16px 20px;
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        width: 100%;
        box-sizing: border-box;
    }
    .empty-state { text-align: center; padding: 80px; color: #6b7280; font-size: 14px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; width: 100%; }
</style>
@endpush

@section('content')

<div class="page-header">
    <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
        <!-- Form Filter Status -->
        <div class="filter-row">
            <a href="{{ route('admin.laporan.index', ['status' => '', 'search' => $search]) }}"
               class="filter-pill {{ !$status ? 'active' : '' }}">Semua</a>
            <a href="{{ route('admin.laporan.index', ['status' => 'dicari', 'search' => $search]) }}"
               class="filter-pill {{ $status === 'dicari' ? 'active' : '' }}">Dicari</a>
            <a href="{{ route('admin.laporan.index', ['status' => 'ditemukan', 'search' => $search]) }}"
               class="filter-pill {{ $status === 'ditemukan' ? 'active' : '' }}">Ditemukan</a>
        </div>

        <!-- Form Pencarian -->
        <form class="search-form" method="GET" action="{{ route('admin.laporan.index') }}">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <input type="text" name="search" value="{{ $search }}"
                   class="search-input" placeholder="Cari barang atau lokasi...">
            <button type="submit" class="btn-search">Cari</button>
            @if($search)
                <a href="{{ route('admin.laporan.index', ['status' => $status]) }}"
                   style="font-size:12px; color:#6b7280; text-decoration:none; margin-left:4px;">✕ Reset</a>
            @endif
        </form>
    </div>
    
    <div style="font-size:13px; color:#6b7280;">
        Total: <strong>{{ $laporan->total() }}</strong> laporan
    </div>
</div>

<div class="reports-grid">
    @forelse($laporan as $lap)
        <div class="report-card">
            <!-- Header Card (Pelapor & Waktu) -->
            <div class="card-header">
                <div class="reporter-info">
                    <span class="reporter-name">{{ $lap->user->name ?? 'Anonim' }}</span>
                    <span class="report-time">{{ $lap->created_at ? $lap->created_at->diffForHumans() : '-' }}</span>
                </div>
                
                @if($lap->status === 'dicari')
                    <span class="badge badge-dicari">Dicari</span>
                @elseif($lap->status === 'ditemukan')
                    <span class="badge badge-ditemukan">Ditemukan</span>
                @else
                    <span class="badge badge-selesai">Selesai</span>
                @endif
            </div>

            <!-- Gambar postingan -->
            <div class="card-img-wrap">
                @if(!empty($lap->images))
                    <img src="{{ $lap->images[0] }}" class="card-img" onerror="this.src='https://via.placeholder.com/400x300'">
                @else
                    <img src="https://via.placeholder.com/400x300" class="card-img">
                @endif
            </div>

            <!-- Deskripsi & Info -->
            <div class="card-body">
                <h3 class="card-title">{{ $lap->title ?? '-' }}</h3>
                <div class="card-location">📍 {{ $lap->location ?? '-' }}</div>
                <p class="card-desc">{{ $lap->description ?? 'Tidak ada deskripsi.' }}</p>
                
                <!-- Aksi Admin -->
                <div class="card-actions">
                    <!-- Ubah Status -->
                    <form action="{{ route('admin.laporan.updateStatus', $lap->id) }}" method="POST">
                        @csrf @method('PATCH')
                        @if($lap->status === 'dicari')
                            <input type="hidden" name="status" value="ditemukan">
                            <button type="submit" class="btn-action btn-green">✓ Ditemukan</button>
                        @else
                            <input type="hidden" name="status" value="dicari">
                            <button type="submit" class="btn-action btn-blue">↩ Dicari</button>
                        @endif
                    </form>

                    <!-- Hapus Postingan (Jika tidak pantas) -->
                    <form action="{{ route('admin.laporan.destroy', $lap->id) }}" method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus postingan ini karena melanggar aturan / tidak pantas?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-action btn-red">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state" style="grid-column: 1 / -1;">
            Tidak ada laporan ditemukan.
        </div>
    @endforelse
</div>

@if($laporan->hasPages())
<div class="pagination-wrap">
    {{ $laporan->appends(request()->query())->links() }}
</div>
@endif

@endsection