@extends('layouts.waitress')

@section('content')
<div class="container-fluid px-0 py-5 d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg border-0 rounded-4" style="max-width: 520px; width: 100%; background-color: #141820; border: 1px solid rgba(255, 255, 255, 0.08) !important;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex p-3 mb-3">
                    <i class="bi bi-box-arrow-right fs-1"></i>
                </div>
                <h3 class="fw-bold text-white">Tutup Shift Kasir</h3>
                <p class="text-white-50 small mb-0">Masukkan modal awal dan hitung seluruh fisik uang tunai yang ada di dalam laci kas sekarang.</p>
            </div>

            <div class="alert alert-info py-2 small d-flex align-items-center gap-2 mb-4" style="background-color: rgba(56, 189, 248, 0.1); border-color: rgba(56, 189, 248, 0.25); color: #38bdf8;">
                <i class="bi bi-clock-history fs-6"></i>
                <span>Waktu mulai shift: {{ $shift->waktu_buka->format('d M Y, H:i') }} WIB</span>
            </div>

            <form action="{{ route('kasir.shift.storeTutup') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin jumlah fisik uang tunai sudah dihitung dengan benar? Shift akan ditutup dan Anda akan keluar dari sistem.')">
                @csrf
                
                <!-- 1. Modal Awal Kas (Kembalian) -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-white small">Modal Awal / Uang Kembalian (Rp)</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text text-white border-end-0" style="background-color: #0e1217; border-color: #21262d;">Rp</span>
                        <input type="number" class="form-control text-white border-start-0 ps-0" style="background-color: #0e1217; border-color: #21262d;" 
                               name="modal_awal" value="{{ old('modal_awal', ($shift->modal_awal > 0 ? (int)$shift->modal_awal : '100000')) }}" 
                               placeholder="Contoh: 100000" min="0" onkeydown="if(['e','E','+','-'].includes(event.key)) event.preventDefault();" required>
                    </div>
                    <small class="text-white-50 mt-1 d-block" style="font-size: 0.75rem;">Uang kembalian yang disiapkan di laci kas saat awal buka toko (default: Rp 100.000).</small>
                    @error('modal_awal')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 2. Uang Fisik Aktual di Laci -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-white small">Total Uang Fisik Aktual di Laci Saat Ini (Rp)</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text text-white border-end-0" style="background-color: #0e1217; border-color: #21262d;">Rp</span>
                        <input type="number" class="form-control text-white border-start-0 ps-0" style="background-color: #0e1217; border-color: #21262d;" 
                               name="uang_fisik_aktual" placeholder="Contoh: 650000" min="0" onkeydown="if(['e','E','+','-'].includes(event.key)) event.preventDefault();" required autofocus>
                    </div>
                    <small class="text-white-50 mt-1 d-block" style="font-size: 0.75rem;">Sistem akan otomatis menghitung: Modal Awal + Total Penjualan Tunai - Pengeluaran Kasir.</small>
                    @error('uang_fisik_aktual')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('kasir.pos') }}" class="btn btn-outline-secondary flex-grow-1 fw-bold rounded-pill btn-touch" style="color: #cbd5e1; border-color: #30363d;">Batal</a>
                    <button type="submit" class="btn btn-danger flex-grow-1 fw-bold rounded-pill shadow-sm btn-touch">
                        Akhiri Shift & Logout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
