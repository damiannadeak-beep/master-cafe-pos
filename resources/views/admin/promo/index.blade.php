@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #c08e5c;">
                <i class="bi bi-boxes me-2"></i>Paket Hemat / Bundling Menu (Combo)
            </h2>
            <p class="text-white-50 mb-0">
                Strategi penjualan bundling menu combo untuk meningkatkan omset dan nilai rata-rata pesanan pelanggan Master Cafe.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.promo.create') }}" class="btn fw-semibold px-3 py-2 shadow-sm text-white" style="background-color: #c08e5c; border-color: #c08e5c;">
                <i class="bi bi-plus-circle me-1"></i> Buat Paket Hemat Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="background-color: #1a3a2a; border: 1px solid #235c3b; color: #75b798;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
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

    <!-- 4 Stat Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm text-white h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-white-50 small">Total Paket Terdaftar</span>
                        <div class="p-2 rounded" style="background-color: rgba(192, 142, 92, 0.15); color: #c08e5c;">
                            <i class="bi bi-boxes fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-white">{{ $totalPaket }}</h3>
                    <small class="text-white-50">Semua paket combo dibuat</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm text-white h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-white-50 small">Paket Aktif (Live)</span>
                        <div class="p-2 rounded bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check2-circle fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-success">{{ $paketAktif }}</h3>
                    <small class="text-white-50">Siap dipesan kasir & konsumen</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm text-white h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-white-50 small">Rata-rata Hemat Pelanggan</span>
                        <div class="p-2 rounded bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-stars fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-warning">Rp {{ number_format($avgSavings, 0, ',', '.') }}</h3>
                    <small class="text-white-50">Daya tarik potongan combo</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm text-white h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-white-50 small">Paket Kedaluwarsa</span>
                        <div class="p-2 rounded bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-danger">{{ $paketExpired }}</h3>
                    <small class="text-white-50">Lewat batas waktu berlaku</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm text-white" style="background-color: #161b22; border: 1px solid #21262d !important;">
        <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background-color: #0d1117; border-bottom: 1px solid #21262d;">
            <h5 class="fw-bold mb-0" style="color: #c08e5c;">
                <i class="bi bi-list-stars me-2"></i>Daftar Paket Bundling Menu
            </h5>
            <span class="badge rounded-pill" style="background-color: #21262d; color: #8b949e;">
                {{ $promos->count() }} dari {{ $promos->total() }} paket
            </span>
        </div>
        <div class="card-body p-0">
            @if($promos->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3" style="color: #c08e5c; opacity: 0.5;">
                        <i class="bi bi-boxes display-4"></i>
                    </div>
                    <h5 class="text-white-50">Belum ada paket bundling yang dibuat</h5>
                    <p class="text-white-50 small mb-3">Buat paket hemat pertama Anda (contoh: Kopi Susu + Kentang Goreng Rp 25.000)!</p>
                    <a href="{{ route('admin.promo.create') }}" class="btn btn-sm px-3 py-2 text-white" style="background-color: #c08e5c;">
                        <i class="bi bi-plus-circle me-1"></i> Buat Paket Hemat Sekarang
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: #21262d;">
                        <thead>
                            <tr class="text-white-50 small text-uppercase" style="background-color: #11141a; letter-spacing: 0.5px;">
                                <th class="py-3 px-3">Nama Paket</th>
                                <th class="py-3 px-3">Menu dalam Paket</th>
                                <th class="py-3 px-3">Skema Harga & Hemat</th>
                                <th class="py-3 px-3">Jadwal & Hari</th>
                                <th class="py-3 px-3 text-center">Status</th>
                                <th class="py-3 px-3 text-end">Aksi</th>
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
                                <tr>
                                    <!-- Nama Paket -->
                                    <td class="px-3 py-3">
                                        <div class="fw-bold text-white fs-6 mb-1">{{ $p->title }}</div>
                                        @if($p->description)
                                            <div class="text-white-50 small text-truncate" style="max-width: 260px;" title="{{ $p->description }}">
                                                {{ $p->description }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Menu dalam Paket -->
                                    <td class="px-3 py-3">
                                        <div class="d-flex flex-wrap gap-1" style="max-width: 320px;">
                                            @forelse($p->menus as $menu)
                                                <span class="badge bg-secondary bg-opacity-25 text-white border border-secondary border-opacity-50 py-1 px-2 small">
                                                    <i class="bi bi-check2 me-1 text-success"></i>{{ $menu->nama_menu }}
                                                    <strong class="text-warning ms-1">({{ $menu->pivot->jumlah ?? 1 }}x)</strong>
                                                </span>
                                            @empty
                                                <span class="text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Belum ada menu</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    <!-- Skema Harga & Hemat -->
                                    <td class="px-3 py-3">
                                        @if($packageNormalTotal > 0)
                                            <div class="text-white-50 small text-decoration-line-through">
                                                Rp {{ number_format($packageNormalTotal, 0, ',', '.') }}
                                            </div>
                                        @endif
                                        <div class="fw-bold fs-5" style="color: #e5b98a;">
                                            Rp {{ number_format($p->value, 0, ',', '.') }}
                                        </div>
                                        @if($savings > 0)
                                            <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 mt-1">
                                                Hemat Rp {{ number_format($savings, 0, ',', '.') }} ({{ $savingsPercent }}%)
                                            </span>
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
                                            @if(!empty($promoDays) && count($promoDays) > 0)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($promoDays as $dayKey)
                                                        <span class="badge py-0 px-1 bg-dark text-white-50 border border-secondary" style="font-size: 0.7rem;">
                                                            {{ $daysMap[$dayKey] ?? $dayKey }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="badge py-1 px-2 bg-secondary bg-opacity-20 text-white-50" style="font-size: 0.75rem;">
                                                    <i class="bi bi-calendar-check me-1"></i>Setiap Hari
                                                </span>
                                            @endif
                                        </div>

                                        <div class="text-white-50 small" style="font-size: 0.75rem;">
                                            @if($p->starts_at || $p->ends_at)
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $p->starts_at ? $p->starts_at->format('d/m/y') : 'Sekarang' }} - 
                                                {{ $p->ends_at ? $p->ends_at->format('d/m/y') : 'Seterusnya' }}
                                            @else
                                                <span class="text-white-50">Tanpa batas tanggal</span>
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
                                                   style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                        </div>
                                        <div>
                                            @if(!$p->is_active)
                                                <span class="badge bg-secondary" style="font-size: 0.7rem;">Nonaktif</span>
                                            @elseif($isExpired)
                                                <span class="badge bg-danger" style="font-size: 0.7rem;">Kadaluarsa</span>
                                            @elseif($isUpcoming)
                                                <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">Belum Mulai</span>
                                            @else
                                                <span class="badge bg-success" style="font-size: 0.7rem;">Aktif Live</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-3 py-3 text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('admin.promo.edit', $p->id) }}" class="btn btn-sm btn-outline-warning border-secondary text-warning" title="Edit Paket">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('admin.promo.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket hemat \'{{ $p->title }}\'?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-secondary" title="Hapus Paket">
                                                    <i class="bi bi-trash"></i>
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
            <div class="card-footer py-3" style="background-color: #0d1117; border-top: 1px solid #21262d;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-white-50">
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
