@extends('layouts.admin')

@section('content')
@php 
    $users = $users ?? collect(); 
    $stokMenipis = $stokMenipis ?? collect(); 
    $topMenus = $topMenus ?? collect();
    $chartDailyLabels = $chartDailyLabels ?? [];
    $chartDailyData = $chartDailyData ?? [];
    $chartDailyLaba = $chartDailyLaba ?? [];
    $chartMonthlyLabels = $chartMonthlyLabels ?? [];
    $chartMonthlyData = $chartMonthlyData ?? [];
    $chartMonthlyLaba = $chartMonthlyLaba ?? [];
@endphp
<div class="container">
    <!-- Header Dashboard -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">Dashboard Executive Owner</h2>
            <p class="text-secondary small mb-0">Pantau omzet real-time, stok bahan baku, dan performa operasional Master Cafe.</p>
        </div>
        <div class="text-md-end text-secondary small d-flex align-items-center gap-2">
            <i class="bi bi-calendar-event text-secondary"></i>
            <span>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Row 1: Summary Cards (Elegan, Monokrom Bersih, Aksen Bronze & Soft Emerald) -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Omzet Penjualan -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm text-white h-100 rounded-3" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary small fw-medium text-truncate" style="max-width: 80%;">Omzet Penjualan</span>
                        <div class="d-flex align-items-center justify-content-center rounded-2" style="width: 36px; height: 36px; background-color: rgba(192, 142, 92, 0.12); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25);">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-1 text-truncate text-white">Rp {{ number_format($totalPenjualanBulan ?? 0, 0, ',', '.') }}</h4>
                    <small class="text-secondary" style="font-size: 0.72rem;">Bulan Ini (Paid)</small>
                </div>
            </div>
        </div>

        <!-- Card 2: Pengeluaran Kasir (Kas Laci) -->
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('admin.pengeluaran.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm text-white h-100 rounded-3" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-secondary small fw-medium text-truncate" style="max-width: 80%;">Pengeluaran Kasir</span>
                            <div class="d-flex align-items-center justify-content-center rounded-2" style="width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.04); color: #9ca3af; border: 1px solid rgba(255, 255, 255, 0.08);">
                                <i class="bi bi-receipt"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-1 text-truncate text-white">- Rp {{ number_format($totalPengeluaranKasirBulan ?? 0, 0, ',', '.') }}</h4>
                        <small class="text-secondary" style="font-size: 0.72rem;">Kas kecil laci kasir</small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 3: Pengeluaran Bisnis (Modal Owner) -->
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('admin.pengeluaran_bisnis.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm text-white h-100 rounded-3" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-secondary small fw-medium text-truncate" style="max-width: 80%;">Pengeluaran Bisnis</span>
                            <div class="d-flex align-items-center justify-content-center rounded-2" style="width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.04); color: #9ca3af; border: 1px solid rgba(255, 255, 255, 0.08);">
                                <i class="bi bi-wallet2"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-1 text-truncate text-white">- Rp {{ number_format($totalPengeluaranBisnisBulan ?? 0, 0, ',', '.') }}</h4>
                        <small class="text-secondary" style="font-size: 0.72rem;">Gaji, stok bahan, utilitas</small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 4: Pendapatan Bersih Owner -->
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm text-white h-100 rounded-3" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary small fw-medium text-truncate" style="max-width: 80%;">Pendapatan Bersih Owner</span>
                        <div class="d-flex align-items-center justify-content-center rounded-2" style="width: 36px; height: 36px; background-color: rgba(52, 211, 153, 0.12); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.25);">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                    @php $isProfit = ($labaBersihBulan ?? 0) >= 0; @endphp
                    <h4 class="fw-bold mb-1 text-truncate" style="color: {{ $isProfit ? '#34d399' : '#f87171' }};">
                        Rp {{ number_format($labaBersihBulan ?? 0, 0, ',', '.') }}
                    </h4>
                    <small class="text-secondary" style="font-size: 0.72rem;">Omzet - Total Pengeluaran</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 1.5: Secondary Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm text-white h-100 rounded-3" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small">Penjualan Hari Ini</span>
                        <div class="d-flex align-items-center justify-content-center rounded-2" style="width: 32px; height: 32px; background-color: rgba(192, 142, 92, 0.12); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25);">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0 text-white">Rp {{ number_format($totalPenjualanHariIni ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm text-white h-100 rounded-3" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small">Pendapatan Cash</span>
                        <div class="d-flex align-items-center justify-content-center rounded-2" style="width: 32px; height: 32px; background-color: rgba(255, 255, 255, 0.04); color: #9ca3af; border: 1px solid rgba(255, 255, 255, 0.08);">
                            <i class="bi bi-cash"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0 text-white">Rp {{ number_format($totalCash ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm text-white h-100 rounded-3" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small">Pendapatan QRIS</span>
                        <div class="d-flex align-items-center justify-content-center rounded-2" style="width: 32px; height: 32px; background-color: rgba(192, 142, 92, 0.12); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25);">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0 text-white">Rp {{ number_format($totalQris ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Charts -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-header text-white pt-3 pb-3 px-4 d-flex justify-content-between align-items-center" style="background-color: #161b22; border-bottom: 1px solid #21262d !important;">
                    <h6 class="fw-semibold mb-0">Grafik Penjualan Harian (Bulan Ini)</h6>
                </div>
                <div class="card-body p-3 p-md-4" style="background-color: #161b22;">
                    <canvas id="dailySalesChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-header text-white pt-3 pb-3 px-4 d-flex justify-content-between align-items-center" style="background-color: #161b22; border-bottom: 1px solid #21262d !important;">
                    <h6 class="fw-semibold mb-0">Grafik Penjualan Bulanan (Tahun Ini)</h6>
                </div>
                <div class="card-body p-3 p-md-4" style="background-color: #161b22;">
                    <canvas id="monthlySalesChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Alerts & Reviews side-by-side -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-header text-white pt-3 pb-3 px-4" style="background-color: #161b22; border-bottom: 1px solid #21262d !important;">
                    <h6 class="fw-semibold text-white mb-0"><i class="bi bi-exclamation-circle me-2 text-warning"></i> Menu Sedang Habis (Status Waitress)</h6>
                </div>
                <div class="card-body p-0">
                    @if($stokMenipis->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
                                <thead style="border-bottom: 1px solid #21262d;">
                                    <tr class="text-secondary small">
                                        <th class="ps-4 py-3 fw-medium">Nama Produk</th>
                                        <th class="text-center py-3 fw-medium">Kategori</th>
                                        <th class="text-end pe-4 py-3 fw-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stokMenipis as $menu)
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                        <td class="ps-4 fw-medium text-white">{{ $menu->nama_menu }}</td>
                                        <td class="text-center">
                                            <span class="badge rounded-2 px-2.5 py-1 text-secondary" style="background-color: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); font-size: 0.72rem;">
                                                {{ ucfirst($menu->kategori) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="badge rounded-2 px-2.5 py-1" style="background-color: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25); font-size: 0.72rem;">Habis</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-5 text-center">
                            <div class="mb-2" style="color: #34d399;"><i class="bi bi-check-circle fs-1"></i></div>
                            <h6 class="text-secondary mb-0 small">Semua menu saat ini tersedia untuk dipesan.</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-header text-white pt-3 pb-3 px-4 d-flex justify-content-between align-items-center" style="background-color: #161b22; border-bottom: 1px solid #21262d !important;">
                    <h6 class="fw-semibold text-white mb-0"><i class="bi bi-chat-left-text me-2" style="color: #c08e5c;"></i> Ulasan Terbaru</h6>
                    <a href="{{ route('admin.reviews.index') }}" class="text-decoration-none small fw-semibold" style="color: #c08e5c;">Lihat Semua &rarr;</a>
                </div>
                <div class="card-body p-4">
                    @if(($latestReviews ?? collect())->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($latestReviews as $r)
                                <div class="list-group-item bg-transparent text-white px-0 py-3 border-secondary border-opacity-25">
                                    <div class="d-flex justify-content-between w-100 mb-1">
                                        <h6 class="mb-0 fw-semibold text-white">{{ $r->konsumen->name ?? 'Konsumen' }}</h6>
                                        <small class="text-secondary" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($r->tanggal)->diffForHumans() }}</small>
                                    </div>
                                    <div class="mb-1" style="color: #f59e0b; font-size: 0.85rem;">
                                        @for($i=1; $i<=5; $i++)
                                            <i class="bi bi-star{{ $i <= $r->rating ? '-fill' : '' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="mb-0 text-white-50 small lh-base">{{ \Illuminate\Support\Str::limit($r->komentar, 120) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-5 text-center">
                            <div class="text-secondary mb-2"><i class="bi bi-chat-square opacity-50 fs-1"></i></div>
                            <h6 class="text-secondary mb-0 small">Belum ada ulasan baru.</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Row 4: Best Seller -->
    <div class="row g-4 mt-1 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-header text-white pt-3 pb-3 px-4" style="background-color: #161b22; border-bottom: 1px solid #21262d !important;">
                    <h6 class="fw-semibold text-white mb-0"><i class="bi bi-award me-2" style="color: #c08e5c;"></i> Top 5 Menu Terlaris (Bulan Ini)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
                            <thead style="border-bottom: 1px solid #21262d;">
                                <tr class="text-secondary small">
                                    <th class="ps-4 py-3 fw-medium" style="width: 80px;">Peringkat</th>
                                    <th class="py-3 fw-medium">Menu</th>
                                    <th class="text-end pe-4 py-3 fw-medium">Total Terjual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topMenus as $index => $menu)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="ps-4">
                                        <div class="rounded-circle d-inline-flex justify-content-center align-items-center fw-semibold" style="width: 30px; height: 30px; background-color: rgba(192, 142, 92, 0.12); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25); font-size: 0.8rem;">
                                            #{{ $index + 1 }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @php
                                                $imgSrc = asset('images/logo.png');
                                                if (!empty($menu->image_url)) {
                                                    $imgSrc = $menu->image_url;
                                                } elseif (!empty($menu->image)) {
                                                    $path = str_contains($menu->image, '/') ? $menu->image : 'menus/' . $menu->image;
                                                    if (str_starts_with($path, 'storage/')) { $path = substr($path, 8); }
                                                    $imgSrc = asset('storage/' . ltrim($path, '/'));
                                                }
                                            @endphp
                                            <img src="{{ $imgSrc }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="{{ $menu->nama_menu }}" class="rounded-2 object-fit-cover shadow-sm" width="40" height="40" style="border: 1px solid #21262d;">
                                            <span class="fw-medium text-white">{{ $menu->nama_menu }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="badge rounded-2 px-3 py-1.5 fw-medium" style="background-color: rgba(52, 211, 153, 0.12); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.25); font-size: 0.78rem;">
                                            {{ $menu->total_terjual }} porsi
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-secondary small">Belum ada data penjualan bulan ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include("components.admin.dashboard-scripts")
@endsection
