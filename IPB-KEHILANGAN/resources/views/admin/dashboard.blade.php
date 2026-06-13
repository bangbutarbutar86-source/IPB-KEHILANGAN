{{-- ============================================================
     FRONTEND — Halaman Dashboard Admin
     Lokasi di project: resources/views/admin/dashboard.blade.php
     ============================================================
     Data yang dikirim dari AdminController:
       - $totalReport
       - $totalDicari
       - $totalDitemukan
       - $totalUser
       - $ReportTerbaru (collection)
--}}
@extends('admin.layouts.admin')

@section('page-title', 'Dashboard')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px 20px;
        text-decoration: none;
        color: inherit;
        display: block;
        transition: transform 0.15s, box-shadow 0.15s;
        cursor: pointer;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
        border-color: #3D4BA0;
    }
    .stat-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 14px;
    }
    .stat-icon svg { width: 20px; height: 20px; }
    .stat-num {
        font-size: 26px;
        font-weight: 600;
        color: #1a1a2e;
    }
    .stat-label {
        font-size: 12px;
        color: #6b7280;
        margin-top: 3px;
    }

    /* Tabel Report terbaru */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .search-box-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .search-box-input {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 12px;
        color: #1a1a2e;
        outline: none;
        width: 220px;
    }
    .search-box-input:focus {
        border-color: #3D4BA0;
    }
    .btn-search-box {
        background: #3D4BA0;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 12px;
        cursor: pointer;
    }
    .btn-search-box:hover {
        background: #2d3880;
    }
    .section-title { font-size: 15px; font-weight: 600; color: #1a1a2e; }
    .filter-row { display: flex; gap: 8px; }
    .filter-pill {
        font-size: 12px;
        padding: 5px 14px;
        border-radius: 20px;
        border: 1px solid #e5e7eb;
        color: #6b7280;
        background: #fff;
        text-decoration: none;
        transition: all 0.15s;
    }
    .filter-pill:hover { border-color: #3D4BA0; color: #3D4BA0; }
    .filter-pill.active { background: #3D4BA0; color: #fff; border-color: #3D4BA0; }

    .table-wrap {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    thead tr { background: #f9fafb; }
    th {
        font-size: 11px; font-weight: 600;
        color: #6b7280; padding: 11px 16px;
        text-align: left; border-bottom: 1px solid #e5e7eb;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    td { font-size: 13px; color: #1a1a2e; padding: 12px 16px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f9fafb; }

    .item-name { font-weight: 500; }
    .item-loc { font-size: 11px; color: #6b7280; margin-top: 2px; }
    .badge {
        display: inline-block;
        font-size: 11px; font-weight: 500;
        padding: 4px 10px; border-radius: 20px;
    }
    .badge-dicari    { background: #fcebeb; color: #a32d2d; }
    .badge-ditemukan { background: #eaf3de; color: #3b6d11; }

    .btn-act {
        display: inline-flex; align-items: center;
        padding: 5px 12px; border-radius: 6px;
        font-size: 11px; font-weight: 500;
        border: 1px solid #e5e7eb;
        text-decoration: none; cursor: pointer;
        background: #fff; color: #374151;
        transition: all 0.15s;
    }
    .btn-act:hover { background: #f3f4f6; }
    .btn-del { color: #a32d2d; border-color: #fecaca; }
    .btn-del:hover { background: #fcebeb; }

    .empty-state {
        text-align: center; padding: 48px; color: #6b7280; font-size: 14px;
    }
</style>
@endpush

@section('content')

{{-- STAT CARDS --}}
<div class="stats-grid">
    <a href="{{ route('admin.laporan.index') }}" class="stat-card">
        <div class="stat-icon" style="background:#eef0fa; color:#3D4BA0;">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M9 12h6M9 16h6M9 8h3M5 4h14a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z"/>
            </svg>
        </div>
        <div class="stat-num">{{ $totalReport }}</div>
        <div class="stat-label">Total Report</div>
    </a>

    <a href="{{ route('admin.laporan.index', ['status' => 'dicari']) }}" class="stat-card">
        <div class="stat-icon" style="background:#fcebeb; color:#a32d2d;">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
        </div>
        <div class="stat-num">{{ $totalDicari }}</div>
        <div class="stat-label">Masih Dicari</div>
    </a>

    <a href="{{ route('admin.laporan.index', ['status' => 'ditemukan']) }}" class="stat-card">
        <div class="stat-icon" style="background:#eaf3de; color:#3b6d11;">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/>
            </svg>
        </div>
        <div class="stat-num">{{ $totalDitemukan }}</div>
        <div class="stat-label">Ditemukan</div>
    </a>

    <a href="{{ route('admin.users.index') }}" class="stat-card">
        <div class="stat-icon" style="background:#faeeda; color:#854f0b;">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
            </svg>
        </div>
        <div class="stat-num">{{ $totalUser }}</div>
        <div class="stat-label">Total User</div>
    </a>
</div>

{{-- TABEL Report TERBARU --}}
<div class="section-header">
    <div class="section-title">
        @if(isset($search) && $search)
            Hasil Pencarian: "{{ $search }}"
        @else
            Report Terbaru
        @endif
    </div>
    
    <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
        <!-- Form Pencarian -->
        <form class="search-box-wrap" method="GET" action="{{ route('admin.dashboard') }}">
            <input type="text" name="search" value="{{ $search ?? '' }}"
                   class="search-box-input" placeholder="Cari barang atau lokasi...">
            <button type="submit" class="btn-search-box">Cari</button>
            @if(isset($search) && $search)
                <a href="{{ route('admin.dashboard') }}"
                   style="font-size:12px; color:#6b7280; text-decoration:none; margin-left:4px;">✕ Reset</a>
            @endif
        </form>

        <div class="filter-row">
            <a href="{{ route('admin.laporan.index') }}"
               class="filter-pill">Semua Laporan</a>
        </div>
    </div>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Barang</th>
                <th>Pelapor</th>
                <th>Lokasi</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ReportTerbaru as $lap)
            <tr>
                <td>
                    <div class="item-name">{{ $lap->title ?? '-' }}</div>
                </td>
                <td>{{ $lap->user->name ?? 'Anonim' }}</td>
                <td>
                    <div class="item-loc">{{ $lap->location ?? '-' }}</div>
                </td>
                <td style="color:#6b7280; font-size:12px;">
                    {{ \Carbon\Carbon::parse($lap->created_at)->format('d M Y') }}
                </td>
                <td>
                    @if($lap->status === 'dicari')
                        <span class="badge badge-dicari">Dicari</span>
                    @elseif($lap->status === 'ditemukan')
                        <span class="badge badge-ditemukan">Ditemukan</span>
                    @else
                        <span class="badge badge-ditemukan" style="background:#e0f2fe; color:#0369a1;">Selesai</span>
                    @endif
                </td>
                <td>
                    {{-- Ubah status --}}
                    <form action="{{ route('admin.laporan.updateStatus', $lap->id) }}"
                          method="POST" style="display:inline;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status"
                               value="{{ $lap->status === 'dicari' ? 'ditemukan' : 'dicari' }}">
                        <button type="submit" class="btn-act">
                            {{ $lap->status === 'dicari' ? '✓ Tandai Ditemukan' : '↩ Ubah ke Dicari' }}
                        </button>
                    </form>
                    {{-- Hapus --}}
                    <form action="{{ route('admin.laporan.destroy', $lap->id) }}"
                          method="POST" style="display:inline;"
                          onsubmit="return confirm('Hapus Report ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-act btn-del">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="empty-state">Tidak ada report ditemukan.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection