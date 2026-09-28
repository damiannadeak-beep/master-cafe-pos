@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h4 fw-bold text-white mb-0">Paket Hemat & Bundling</h1>
                <span class="text-secondary small fw-normal ms-2 fs-6">(Menu Combo)</span>
            </div>
            <p class="text-secondary small mb-0">
                Strategi bundling menu combo untuk meningkatkan nilai rata-rata pesanan pelanggan.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.promo.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-medium rounded-3 shadow-sm">
                <i class="bi bi-plus-lg"></i>
                <span>Buat Paket Baru</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3" role="alert" style="background-color: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.25); color: #34d399;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $totalPaket = $promos->total();
        $paketAktif = $promos->where('is_active', true)->filter(fn($p) => (!$p->ends_at || $p->ends_at >= now()) && (!$p->starts_at || $p->starts_at <= now()))->count();
        $paketExpired = $promos->filter(fn($p) => $p->ends_at && $p->ends_at < now())->count();
        
        // Rata-rata hemat
        $totalSavings = 0;
        $countSavings = 0;
        foreach($promos as $p) {
            $normal = 0;
            foreach($p->menus as $m) {
                $normal += ($m->harga * ($m->pivot->jumlah ?? 1));
            }
            if ($normal > $p->value) {
                $totalSavings += ($normal - $p->value);
                $countSavings++;
            }
        }
        $avgSavings = $countSavings > 0 ? round($totalSavings / $countSavings) : 0;
    @endphp

    <!-- 4 Stat Metric Cards (Executive Dark) -->
    <div class="row g-3 mb-4">
        <!-- Total Paket Terdaftar -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-medium">Total Paket</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background-color: rgba(192, 142, 92, 0.08); border: 1px solid rgba(192, 142, 92, 0.2); color: #c08e5c;">
                            <i class="bi bi-boxes" style="font-size: 1rem;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-1" style="font-size: 1.45rem;">{{ $totalPaket }}</h3>
                    <div class="text-secondary small" style="font-size: 0.75rem;">Semua paket terdaftar</div>
                </div>
            </div>
        </div>

        <!-- Paket Aktif (Live) -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-medium">Paket Aktif (Live)</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background-color: rgba(52, 211, 153, 0.08); border: 1px solid rgba(52, 211, 153, 0.2); color: #34d399;">
                            <i class="bi bi-check2-circle" style="font-size: 1rem;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-1" style="font-size: 1.45rem;">{{ $paketAktif }}</h3>
                    <div class="text-secondary small" style="font-size: 0.75rem;">Siap dipesan kasir & konsumen</div>
                </div>
            </div>
        </div>

        <!-- Rata-rata Potongan -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-medium">Rata-rata Potongan</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); color: #d0d7de;">
                            <i class="bi bi-tag" style="font-size: 1rem;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-1" style="font-size: 1.45rem;">Rp {{ number_format($avgSavings, 0, ',', '.') }}</h3>
                    <div class="text-secondary small" style="font-size: 0.75rem;">Daya tarik diskon combo</div>
                </div>
            </div>
        </div>

        <!-- Kedaluwarsa -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-medium">Kedaluwarsa</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background-color: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171;">
                            <i class="bi bi-clock-history" style="font-size: 1rem;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-1" style="font-size: 1.45rem;">{{ $paketExpired }}</h3>
                    <div class="text-secondary small" style="font-size: 0.75rem;">Lewat batas waktu berlaku</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-collection" style="color: #c08e5c;"></i>
                <span class="fw-semibold text-white fs-6">Daftar Paket Bundling Menu</span>
            </div>
            <span class="text-secondary small fw-medium">
                Total: {{ $promos->total() }} Paket
            </span>
        </div>
        <div class="card-body p-0">
            @if($promos->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3" style="color: #c08e5c; opacity: 0.5;">
                        <i class="bi bi-boxes display-4"></i>
                    </div>
                    <h5 class="text-white-50">Belum ada paket bundling yang dibuat</h5>
                    <p class="text-secondary small mb-3">Buat paket hemat pertama Anda (contoh: Kopi Susu + Kentang Goreng Rp 25.000)!</p>
                    <a href="{{ route('admin.promo.create') }}" class="btn btn-primary btn-sm px-3 py-2 fw-medium rounded-3">
                        <i class="bi bi-plus-lg me-1"></i> Buat Paket Hemat Sekarang
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255, 255, 255, 0.02);">
                        <thead>
                            <tr style="border-bottom: 1px solid #21262d; background-color: rgba(255, 255, 255, 0.02);">
                                <th class="text-secondary text-uppercase fw-semibold py-3 ps-4" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 220px;">Nama Paket</th>
                                <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Menu dalam Paket</th>
                                <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 190px;">Skema Harga & Hemat</th>
                                <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 170px;">Jadwal & Hari</th>
                                <th class="text-secondary text-uppercase fw-semibold py-3 px-3 text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 120px;">Status</th>
                                <th class="text-secondary text-uppercase fw-semibold py-3 pe-4 text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($promos as $p)
                                @php
                                    $isExpired = $p->ends_at && $p->ends_at < now();
                                    $isUpcoming = $p->starts_at && $p->starts_at > now();
                                    $isLive = $p->is_active && !$isExpired && !$isUpcoming;

                                    $packageNormalTotal = 0;
                                    foreach ($p->menus as $menuItem) {
                                        $packageNormalTotal += ($menuItem->harga * ($menuItem->pivot->jumlah ?? 1));
                                    }
                                    $savings = max(0, $packageNormalTotal - $p->value);
                                    $savingsPercent = $packageNormalTotal > 0 ? round(($savings / $packageNormalTotal) * 100, 1) : 0;
                                @endphp
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <!-- Nama Paket -->
                                    <td class="ps-4 py-3">
                                        <div class="fw-medium text-white fs-6 mb-0.5">{{ $p->title }}</div>
                                        @if($p->description)
                                            <div class="text-secondary small text-truncate" style="max-width: 240px;" title="{{ $p->description }}">
                                                {{ $p->description }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Menu dalam Paket -->
                                    <td class="px-3 py-3">
                                        <div class="d-flex flex-column gap-1" style="max-width: 320px;">
                                            @forelse($p->menus as $menu)
                                                <div class="d-flex align-items-center small text-light">
                                                    <span class="text-secondary opacity-50 me-2" style="font-size: 0.7rem;">&bull;</span>
                                                    <span class="fw-medium text-white">{{ $menu->nama_menu }}</span>
                                                    <span class="ms-1.5 fw-semibold" style="color: #c08e5c; font-size: 0.8rem;">({{ $menu->pivot->jumlah ?? 1 }}x)</span>
                                                </div>
                                            @empty
                                                <span class="text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Belum ada menu</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    <!-- Skema Harga & Hemat -->
                                    <td class="px-3 py-3 text-nowrap">
                                        @if($packageNormalTotal > 0)
                                            <div class="text-secondary small text-decoration-line-through mb-0.5" style="font-size: 0.78rem;">
                                                Rp {{ number_format($packageNormalTotal, 0, ',', '.') }}
                                            </div>
                                        @endif
                                        <div class="fw-bold text-white fs-6 mb-1">
                                            Rp {{ number_format($p->value, 0, ',', '.') }}
                                        </div>
                                        @if($savings > 0)
                                            <div class="small fw-semibold text-success mt-1" style="font-size: 0.76rem;">
                                                <i class="bi bi-tag-fill me-1"></i>Hemat Rp {{ number_format($savings, 0, ',', '.') }} ({{ $savingsPercent }}%)
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Jadwal & Hari -->
                                    <td class="px-3 py-3">
                                        @php
                                            $daysMap = [
                                                'Monday' => 'Sen',
                                                'Tuesday' => 'Sel',
                                                'Wednesday' => 'Rab',
                                                'Thursday' => 'Kam',
                                                'Friday' => 'Jum',
                                                'Saturday' => 'Sab',
                                                'Sunday' => 'Min'
                                            ];
                                            $promoDays = is_array($p->days) ? $p->days : (is_string($p->days) ? json_decode($p->days, true) : null);
                                        @endphp
                                        <div class="mb-1">
                                            @if(!empty($promoDays) && count($promoDays) > 0 && count($promoDays) < 7)
                                                <div class="small fw-medium text-white" style="font-size: 0.8rem;">
                                                    {{ implode(', ', array_map(fn($dayKey) => $daysMap[$dayKey] ?? $dayKey, $promoDays)) }}
                                                </div>
                                            @else
                                                <div class="small fw-medium text-white" style="font-size: 0.8rem;">
                                                    Setiap Hari
                                                </div>
                                            @endif
                                        </div>

                                        <div class="text-secondary small" style="font-size: 0.75rem;">
                                            @if($p->starts_at || $p->ends_at)
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $p->starts_at ? $p->starts_at->format('d/m/y') : 'Sekarang' }} - 
                                                {{ $p->ends_at ? $p->ends_at->format('d/m/y') : 'Seterusnya' }}
                                            @else
                                                <span class="text-secondary">Tanpa batas tanggal</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status Toggle -->
                                    <td class="px-3 py-3 text-center">
                                        <div class="form-check form-switch d-inline-block mb-1">
                                            <input class="form-check-input promo-toggle-switch" type="checkbox" role="switch"
                                                   data-id="{{ $p->id }}"
                                                   data-url="{{ route('admin.promo.toggle_status', $p->id) }}"
                                                   {{ $p->is_active ? 'checked' : '' }}
                                                   style="cursor: pointer; width: 2.3em; height: 1.2em;">
                                        </div>
                                        <div>
                                            @if(!$p->is_active)
                                                <span class="small fw-medium text-secondary">Nonaktif</span>
                                            @elseif($isExpired)
                                                <span class="small fw-medium text-danger">Kedaluwarsa</span>
                                            @elseif($isUpcoming)
                                                <span class="small fw-medium text-warning">Belum Mulai</span>
                                            @else
                                                <span class="small fw-medium text-success">Aktif Live</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="pe-4 py-3 text-end">
                                        <div class="d-inline-flex justify-content-end align-items-center gap-2 flex-nowrap" style="gap: 8px !important;">
                                            <a href="{{ route('admin.promo.edit', $p->id) }}" class="btn btn-sm btn-icon d-inline-flex align-items-center justify-content-center p-0 rounded-2" style="width: 36px; height: 36px; background: rgba(192, 142, 92, 0.08); border: 1px solid rgba(192, 142, 92, 0.25); color: #c08e5c;" title="Edit Paket">
                                                <i class="bi bi-pencil" style="font-size: 0.85rem;"></i>
                                            </a>
                                            <form action="{{ route('admin.promo.destroy', $p->id) }}" method="POST" class="d-inline-flex m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket hemat \'{{ $p->title }}\'?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon d-inline-flex align-items-center justify-content-center p-0 rounded-2" style="width: 36px; height: 36px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); color: #f87171;" title="Hapus Paket">
                                                    <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($promos->hasPages())
            <div class="card-footer px-4 py-3" style="background-color: #161b22; border-top: 1px solid #21262d;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-secondary">
                        Menampilkan halaman {{ $promos->currentPage() }} dari {{ $promos->lastPage() }}
                    </div>
                    <div>
                        {{ $promos->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.promo-toggle-switch').forEach(switchEl => {
            switchEl.addEventListener('change', function() {
                const promoId = this.dataset.id;
                const toggleUrl = this.dataset.url;
                const isChecked = this.checked;

                fetch(toggleUrl, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        switchEl.checked = !isChecked;
                        alert('Gagal mengubah status paket.');
                    }
                })
                .catch(err => {
                    console.error('Error toggling package status:', err);
                    switchEl.checked = !isChecked;
                    alert('Terjadi kesalahan jaringan.');
                });
            });
        });
    });
</script>
@endsection
