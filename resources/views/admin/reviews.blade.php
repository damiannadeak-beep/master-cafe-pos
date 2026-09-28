@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="text-white fw-bold mb-1">Evaluasi & Ulasan Pelanggan</h2>
            <p class="text-white-50 mb-0">Umpan balik langsung dari tamu sebagai bahan evaluasi layanan & kualitas rasa kafe.</p>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-4">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Kartu KPI Ringkasan Kepuasan Pelanggan -->
    <div class="row g-3 mb-4">
        <!-- Rata-rata Rating -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 h-100" style="background-color: #161b22; border: 1px solid #30363d !important; border-radius: 12px;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Skor Kepuasan</span>
                    <i class="bi bi-star-fill text-warning fs-5"></i>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-2 fw-bold text-white">{{ $stats['average'] }}</span>
                    <span class="text-secondary small">/ 5.0</span>
                </div>
                <div class="mt-2 text-warning small">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($stats['average']))
                            <i class="bi bi-star-fill"></i>
                        @elseif($i - $stats['average'] < 1 && $stats['average'] - floor($stats['average']) >= 0.3)
                            <i class="bi bi-star-half"></i>
                        @else
                            <i class="bi bi-star text-secondary"></i>
                        @endif
                    @endfor
                    <span class="text-white-50 ms-1 small">Rata-rata</span>
                </div>
            </div>
        </div>

        <!-- Total Ulasan -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 h-100" style="background-color: #161b22; border: 1px solid #30363d !important; border-radius: 12px;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Masukan</span>
                    <i class="bi bi-chat-heart text-info fs-5"></i>
                </div>
                <div class="fs-2 fw-bold text-white">{{ $stats['total'] }}</div>
                <span class="text-white-50 small mt-auto">Ulasan masuk dari tamu</span>
            </div>
        </div>

        <!-- Ulasan Bintang 5 (Sangat Puas) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 h-100" style="background-color: #161b22; border: 1px solid #30363d !important; border-radius: 12px;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Sangat Puas (<i class="bi bi-star-fill text-warning" style="font-size: 0.7rem;"></i> 5)</span>
                    <i class="bi bi-emoji-smile text-success fs-5"></i>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-2 fw-bold text-success">{{ $stats['star5'] }}</span>
                    <span class="text-secondary small">ulasan</span>
                </div>
                <span class="text-success small opacity-75 mt-auto">Kepuasan maksimal</span>
            </div>
        </div>

        <!-- Perlu Evaluasi (<= 3 Bintang) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 h-100" style="background-color: #161b22; border: 1px solid #30363d !important; border-radius: 12px;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Perlu Evaluasi (&le; <i class="bi bi-star-fill text-warning" style="font-size: 0.7rem;"></i> 3)</span>
                    <i class="bi bi-exclamation-octagon text-danger fs-5"></i>
                </div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-2 fw-bold text-danger">{{ $stats['star3'] + $stats['starLow'] }}</span>
                    <span class="text-secondary small">catatan</span>
                </div>
                <span class="text-danger small opacity-75 mt-auto">Peluang peningkatan rasa/layanan</span>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Ulasan & Masukan Konsumen -->
    <div class="card shadow-sm border-0" style="background-color: #161b22; border: 1px solid #30363d !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header d-flex justify-content-between align-items-center py-3 px-4" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #30363d;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-journal-text text-warning"></i>
                <span class="fw-bold text-white fs-6">Daftar Masukan & Evaluasi Pelanggan</span>
            </div>
            <span class="text-secondary small fw-medium">
                Total: {{ $reviews->total() }} Data
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255, 255, 255, 0.04);">
                    <thead>
                        <tr style="border-bottom: 1px solid #30363d; background-color: rgba(255, 255, 255, 0.03);">
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3 text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 60px;">No</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 180px;">Pelanggan / Meja</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 140px;">Rating</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Kesan & Saran Tamu</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3 text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 160px;">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $index => $r)
                        @php
                            $namaTamu = $r->pesanan->customer_name ?? $r->konsumen->name ?? 'Tamu Kafe';
                            $infoMeja = $r->pesanan ? ($r->pesanan->tipe_pesanan === 'takeaway' ? 'Takeaway' : ($r->pesanan->meja ? 'Meja ' . $r->pesanan->meja->nama_meja_atau_nomor : 'Dine In')) : '-';
                            $orderId = $r->pesanan_id ?? $r->id_pesanan ?? '-';
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <td class="text-center text-white-50 px-2 fw-semibold" style="font-size: 0.9rem;">
                                {{ $reviews->firstItem() + $index }}
                            </td>
                            <td class="px-3 py-3">
                                <div class="fw-semibold text-white d-block" style="font-size: 0.95rem;">
                                    {{ $namaTamu }}
                                </div>
                                <div class="small text-secondary mt-1" style="font-size: 0.78rem;">
                                    <span class="text-light opacity-75">{{ $infoMeja }}</span> &bull; <span>Order #{{ $orderId }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="d-flex align-items-center gap-1 text-warning mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $r->rating)
                                            <i class="bi bi-star-fill" style="font-size: 0.85rem;"></i>
                                        @else
                                            <i class="bi bi-star text-secondary opacity-50" style="font-size: 0.85rem;"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span class="small fw-medium {{ $r->rating >= 4 ? 'text-success' : ($r->rating == 3 ? 'text-warning' : 'text-danger') }}" style="font-size: 0.78rem;">
                                    {{ $r->rating == 5 ? 'Sangat Puas' : ($r->rating == 4 ? 'Puas' : ($r->rating == 3 ? 'Cukup' : 'Kurang Puas')) }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                @if(!empty($r->komentar))
                                    <div class="text-white p-2.5 rounded-3" style="background-color: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); font-size: 0.9rem; line-height: 1.5;">
                                        "{{ $r->komentar }}"
                                    </div>
                                @else
                                    <span class="text-white-50 fst-italic small">Hanya memberikan bintang rating (tanpa pesan teks).</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-end text-white-50 small">
                                <div class="fw-medium text-white-50">
                                    {{ \Carbon\Carbon::parse($r->created_at ?? $r->tanggal)->translatedFormat('d M Y') }}
                                </div>
                                <div style="font-size: 0.75rem;">
                                    {{ \Carbon\Carbon::parse($r->created_at ?? $r->tanggal)->format('H:i') }} WIB
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-white-50 py-5">
                                <i class="bi bi-chat-square-heart fs-1 text-secondary d-block mb-2 opacity-50"></i>
                                <span class="d-block fw-semibold text-white">Belum Ada Ulasan Pelanggan</span>
                                <small class="text-white-50">Setiap ulasan bintang dan kritik/saran dari tamu akan muncul di sini sebagai bahan evaluasi.</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($reviews->hasPages())
        <div class="card-footer border-top border-secondary border-opacity-25 py-3 px-4" style="background-color: rgba(255, 255, 255, 0.02);">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-secondary small">
                    Menampilkan {{ $reviews->firstItem() }} - {{ $reviews->lastItem() }} dari {{ $reviews->total() }} ulasan
                </span>
                <div>
                    {{ $reviews->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

