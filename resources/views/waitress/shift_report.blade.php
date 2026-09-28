@extends('layouts.waitress')

@section('content')
<div class="container-fluid px-0 py-0 d-print-none">
    <!-- Feedback Alerts -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 d-flex align-items-center gap-2" role="alert" style="background-color: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.3); color: #34d399;">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div class="flex-grow-1">{{ session('success') }}</div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 d-flex align-items-center gap-2" role="alert" style="background-color: rgba(248, 113, 113, 0.15); border: 1px solid rgba(248, 113, 113, 0.3); color: #f87171;">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div class="flex-grow-1">{{ session('error') }}</div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold text-white mb-0">
                    <i class="bi bi-calendar-check me-2" style="color: #c08e5c;"></i>Laporan Tutup Shift
                </h4>
                @if(($shift->status ?? '') === 'closed')
                    <span class="badge rounded-pill px-2.5 py-1" style="background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); font-size: 0.75rem;">
                        <i class="bi bi-lock-fill me-1"></i>Shift Ditutup
                    </span>
                @else
                    <span class="badge rounded-pill px-2.5 py-1" style="background-color: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3); font-size: 0.75rem;">
                        <i class="bi bi-clock-history me-1"></i>Shift Aktif
                    </span>
                @endif
            </div>
            <p class="text-secondary small mb-0">
                Rekapitulasi penjualan & kas kasir • Shift dibuka {{ $shift->waktu_buka ? $shift->waktu_buka->format('d M Y, H:i') . ' WIB' : '-' }} (Kasir: {{ auth()->user()->name }})
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('kasir.shift_report.excel') }}" class="btn btn-sm px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1.5 fw-medium text-decoration-none shadow-sm" style="background-color: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.25); color: #34d399; transition: all 0.2s ease;">
                <i class="bi bi-file-earmark-excel fs-6"></i>
                <span>Export Excel</span>
            </a>
            <a href="{{ route('kasir.shift_report.pdf') }}" class="btn btn-sm px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1.5 fw-medium text-decoration-none shadow-sm" style="background-color: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.25); color: #f87171; transition: all 0.2s ease;">
                <i class="bi bi-file-earmark-pdf fs-6"></i>
                <span>Export PDF</span>
            </a>
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1.5 fw-medium shadow-sm">
                <i class="bi bi-printer fs-6"></i>
                <span>Cetak Browser</span>
            </button>
        </div>
    </div>

    <!-- Metric Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Tunai -->
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Pemasukan Tunai</div>
                        <div class="fs-5 fw-bold text-white">Rp {{ number_format($totalCash, 0, ',', '.') }}</div>
                        <div class="text-secondary small mt-0.5" style="font-size: 0.72rem;">
                            <i class="bi bi-cash-stack me-1" style="color: #34d399;"></i>Tunai pesanan
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(52, 211, 153, 0.12); border: 1px solid rgba(52, 211, 153, 0.25); color: #34d399;">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total QRIS -->
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Non-Tunai (QRIS)</div>
                        <div class="fs-5 fw-bold text-white">Rp {{ number_format($totalQris, 0, ',', '.') }}</div>
                        <div class="text-secondary small mt-0.5" style="font-size: 0.72rem;">
                            <i class="bi bi-qr-code me-1" style="color: #38bdf8;"></i>Pemasukan digital
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.25); color: #38bdf8;">
                        <i class="bi bi-qr-code-scan fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Pengeluaran -->
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Pengeluaran Kasir</div>
                        <div class="fs-5 fw-bold text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
                        <div class="text-secondary small mt-0.5" style="font-size: 0.72rem;">
                            <i class="bi bi-arrow-down-right-circle me-1" style="color: #f87171;"></i>Biaya operasional
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #f87171;">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Total Omzet Shift Ini -->
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid rgba(192, 142, 92, 0.35) !important; border-radius: 12px; position: relative;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Total Omzet Shift</div>
                        <div class="fs-5 fw-bold text-white">Rp {{ number_format($totalSemua, 0, ',', '.') }}</div>
                        <div class="small mt-0.5" style="color: #c08e5c; font-size: 0.72rem;">
                            <i class="bi bi-check-circle-fill me-1"></i>{{ $pembayarans->count() }} Transaksi
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(192, 142, 92, 0.15); border: 1px solid rgba(192, 142, 92, 0.3); color: #c08e5c;">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-money-box {
            background-color: #0b0e14;
            border: 1px solid #30363d;
            border-radius: 8px;
            height: 38px;
            width: 190px;
            padding: 0 12px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        .custom-money-box:focus-within {
            border-color: #c08e5c !important;
            box-shadow: 0 0 0 2px rgba(192, 142, 92, 0.16) !important;
            background-color: #0f131a;
        }
        .custom-money-prefix {
            color: #c08e5c;
            font-weight: 700;
            font-size: 0.92rem;
            margin-right: 8px;
            user-select: none;
            letter-spacing: 0.5px;
        }
        .custom-money-input {
            background: transparent !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            letter-spacing: 0.5px;
            padding: 0;
            margin: 0;
        }
        .custom-money-input::-webkit-outer-spin-button,
        .custom-money-input::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            appearance: none !important;
            margin: 0 !important;
            display: none !important;
        }
        .custom-money-input[type=number] {
            -moz-appearance: textfield !important;
            appearance: textfield !important;
        }
    </style>

    <!-- Card Modal Kas & Tutup Shift (Kompak, Proporsional & Elegan) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4" style="background-color: #141820; border: 1px solid rgba(255, 255, 255, 0.08) !important;">
        <div class="card-body px-3.5 py-3">
            <form action="{{ route('kasir.shift_report.update_cash') }}" method="POST" class="d-flex flex-column flex-xl-row align-items-start align-items-xl-end justify-content-between gap-3">
                @csrf
                
                <!-- Left: Input Uang Cas / Modal Awal & Total Kas Laci Otomatis -->
                <div class="d-flex flex-wrap align-items-end gap-3.5">
                    <!-- Input Modal Awal Kas -->
                    <div>
                        <label for="input_modal_awal" class="small fw-semibold text-secondary text-uppercase d-block" style="font-size: 0.72rem; letter-spacing: 0.5px; margin-bottom: 7px !important;">
                            Uang Cas / Modal Awal
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width: 38px; height: 38px; background: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.25); color: #c08e5c;">
                                <i class="bi bi-wallet2 fs-5"></i>
                            </div>
                            <div class="custom-money-box">
                                <span class="custom-money-prefix">Rp</span>
                                <input type="number" id="input_modal_awal" name="modal_awal" class="custom-money-input" 
                                    value="{{ old('modal_awal', ($shift->modal_awal > 0 ? (int)$shift->modal_awal : '100000')) }}" 
                                    placeholder="100000" min="0" oninput="updateTotalKasLive()">
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="d-none d-md-block vr opacity-25 mx-1" style="height: 38px; background-color: #484f58;"></div>

                    <!-- Total Uang Kas di Laci (Otomatis dari Pesanan) -->
                    <div>
                        <span class="small fw-semibold text-secondary text-uppercase d-block" style="font-size: 0.72rem; letter-spacing: 0.5px; margin-bottom: 7px !important;">
                            Total Uang Kas di Laci (Otomatis)
                        </span>
                        <div class="d-flex align-items-center gap-1.5" style="height: 38px;">
                            <span class="fs-5 fw-bold text-success" id="disp_total_kas_laci">
                                Rp {{ number_format(($shift->modal_awal > 0 ? (float)$shift->modal_awal : 100000) + (float)$totalCash - (float)$totalPengeluaran, 0, ',', '.') }}
                            </span>
                            <span class="text-secondary small ms-1" style="font-size: 0.72rem;">(Modal + Tunai Masuk - Pengeluaran)</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Action Buttons (Proporsional, Rapi, Jarak Pas) -->
                <div class="d-flex align-items-center flex-wrap" style="gap: 10px !important;">
                    <button type="submit" name="action" value="save" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-2 text-white fw-medium d-inline-flex align-items-center gap-1.5" style="border-color: #30363d; background-color: rgba(255, 255, 255, 0.03); font-size: 0.82rem; height: 38px;">
                        <i class="bi bi-save" style="font-size: 0.85rem;"></i>
                        <span>Simpan Uang Cas</span>
                    </button>

                    @if(($shift->status ?? '') === 'open')
                        <button type="submit" name="action" value="close" onclick="return confirm('Apakah Anda yakin ingin menutup shift ini?')" class="btn btn-danger btn-sm px-3.5 py-1.5 rounded-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" style="font-size: 0.82rem; height: 38px; background-color: #dc2626; border-color: #b91c1c;">
                            <i class="bi bi-lock-fill" style="font-size: 0.85rem;"></i>
                            <span>Tutup Shift Sekarang</span>
                        </button>
                    @else
                        <button type="button" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-outline-danger btn-sm px-3.5 py-1.5 rounded-2 fw-medium d-inline-flex align-items-center gap-1.5" style="font-size: 0.82rem; height: 38px;">
                            <i class="bi bi-box-arrow-right" style="font-size: 0.85rem;"></i>
                            <span>Logout Kasir</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Main Content Card with View Switcher -->
    <div class="card border-0 shadow-sm rounded-4" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-receipt" style="color: #c08e5c;"></i>
                <h5 class="mb-0 fw-bold text-white fs-6">Rincian Operasional Shift</h5>
            </div>
            <!-- Segmented Switcher -->
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="btn-view-transaksi" onclick="switchShiftTab('transaksi')" class="btn btn-sm py-1.5 px-3 rounded-pill fw-medium" style="background-color: rgba(192, 142, 92, 0.2); border: 1px solid #c08e5c; color: #fff; font-size: 0.78rem; transition: all 0.2s ease;">
                    <i class="bi bi-receipt me-1"></i>Daftar Transaksi ({{ $pembayarans->count() }})
                </button>
                <button type="button" id="btn-view-menu" onclick="switchShiftTab('menu')" class="btn btn-sm py-1.5 px-3 rounded-pill fw-medium" style="background-color: transparent; border: 1px solid #30363d; color: #8b949e; font-size: 0.78rem; transition: all 0.2s ease;">
                    <i class="bi bi-cup-hot me-1"></i>Rekap Menu ({{ $totalItemTerjual }})
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <!-- TAB 1: Daftar Transaksi -->
            <div id="view-transaksi">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="color: #e6edf3;">
                        <thead>
                            <tr style="background-color: #12161c; border-bottom: 1px solid #21262d;">
                                <th class="py-3 px-4 text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; width: 15%;">Waktu</th>
                                <th class="py-3 px-3 text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; width: 18%;">No. Pesanan</th>
                                <th class="py-3 px-3 text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; width: 22%;">Tipe</th>
                                <th class="py-3 px-3 text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; width: 20%;">Metode</th>
                                <th class="py-3 px-4 text-secondary small fw-semibold text-uppercase text-end" style="letter-spacing: 0.5px; font-size: 0.72rem; width: 25%;">Total Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pembayarans as $p)
                            <tr style="border-bottom: 1px solid #1c2128; transition: background 0.15s ease;">
                                <td class="py-3 px-4">
                                    <span class="text-white-50 small d-inline-flex align-items-center">
                                        <i class="bi bi-clock me-2 text-secondary opacity-75" style="margin-right: 7px !important;"></i> {{ $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('H:i') : '-' }} WIB
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="badge px-2.5 py-1" style="background-color: rgba(255, 255, 255, 0.05); color: #e6edf3; border: 1px solid #30363d; font-size: 0.8rem; border-radius: 6px;">
                                        #{{ str_pad($p->id_pesanan, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    @php
                                        $tipe = $p->pesanan->tipe_pesanan ?? '';
                                    @endphp
                                    @if($tipe === 'takeaway')
                                        <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.25); font-size: 0.75rem;">
                                            <i class="bi bi-bag me-1"></i>Takeaway
                                        </span>
                                    @elseif($tipe === 'dine_in')
                                        <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(56, 189, 248, 0.12); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.25); font-size: 0.75rem;">
                                            <i class="bi bi-cup-hot me-1"></i>Dine In
                                        </span>
                                    @else
                                        <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(255, 255, 255, 0.06); color: #9ca3af; border: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.75rem;">
                                            {{ ucfirst(str_replace('_', ' ', $tipe ?: 'Pesanan')) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    @if($p->metode == 'cash')
                                        <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(52, 211, 153, 0.12); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.25); font-size: 0.75rem;">
                                            <i class="bi bi-cash me-1"></i>Tunai
                                        </span>
                                    @elseif($p->metode == 'qris')
                                        <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(56, 189, 248, 0.12); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.25); font-size: 0.75rem;">
                                            <i class="bi bi-qr-code me-1"></i>QRIS
                                        </span>
                                    @else
                                        <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(255, 255, 255, 0.06); color: #9ca3af; border: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.75rem;">
                                            {{ strtoupper($p->metode) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <span class="fw-bold text-white" style="font-size: 0.95rem;">
                                        Rp {{ number_format($p->total_bayar, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background-color: rgba(255, 255, 255, 0.03); border: 1px solid #21262d; color: #6e7681;">
                                            <i class="bi bi-receipt-cutoff fs-3"></i>
                                        </div>
                                        <h6 class="text-white fw-semibold mb-1">Belum Ada Transaksi Selesai</h6>
                                        <p class="text-secondary small mb-0">Transaksi yang diselesaikan pada shift ini akan otomatis muncul di sini.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: Rekapitulasi Menu Terjual -->
            <div id="view-menu" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="color: #e6edf3;">
                        <thead>
                            <tr style="background-color: #12161c; border-bottom: 1px solid #21262d;">
                                <th class="py-3 px-4 text-secondary small fw-semibold text-uppercase text-center" style="letter-spacing: 0.5px; font-size: 0.72rem; width: 8%;">No</th>
                                <th class="py-3 px-3 text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; width: 52%;">Menu / Produk</th>
                                <th class="py-3 px-3 text-secondary small fw-semibold text-uppercase text-center" style="letter-spacing: 0.5px; font-size: 0.72rem; width: 18%;">Qty Terjual</th>
                                <th class="py-3 px-4 text-secondary small fw-semibold text-uppercase text-end" style="letter-spacing: 0.5px; font-size: 0.72rem; width: 22%;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $itemIndex = 1; @endphp
                            @forelse($rekapMenu as $nama => $data)
                            <tr style="border-bottom: 1px solid #1c2128; transition: background 0.15s ease;">
                                <td class="py-3 px-4 text-center">
                                    <span class="text-secondary small">{{ $itemIndex++ }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="text-white fw-medium">{{ $nama }}</span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="badge rounded-2 px-2.5 py-1 fw-bold" style="background-color: rgba(192, 142, 92, 0.15); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.3); font-size: 0.8rem;">
                                        {{ $data['jumlah'] }} porsi
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <span class="fw-bold text-white" style="font-size: 0.95rem;">
                                        Rp {{ number_format($data['subtotal'], 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background-color: rgba(255, 255, 255, 0.03); border: 1px solid #21262d; color: #6e7681;">
                                            <i class="bi bi-cup-hot fs-3"></i>
                                        </div>
                                        <h6 class="text-white fw-semibold mb-1">Belum Ada Menu Terjual</h6>
                                        <p class="text-secondary small mb-0">Rincian item menu yang terjual pada shift ini akan dikelompokkan di sini.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                            @if($totalItemTerjual > 0)
                            <tr style="background-color: rgba(255, 255, 255, 0.02); border-top: 2px solid #21262d;">
                                <td colspan="2" class="py-3 px-4 text-end text-secondary fw-semibold small text-uppercase" style="letter-spacing: 0.5px;">
                                    TOTAL ITEM TERJUAL:
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="badge rounded-2 px-3 py-1.5 fw-bold" style="background-color: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3); font-size: 0.85rem;">
                                        {{ $totalItemTerjual }} Item
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <span class="fw-bold text-white fs-6">
                                        Rp {{ number_format($totalSemua, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchShiftTab(tab) {
    const tabTrx = document.getElementById('view-transaksi');
    const tabMenu = document.getElementById('view-menu');
    const btnTrx = document.getElementById('btn-view-transaksi');
    const btnMenu = document.getElementById('btn-view-menu');

    if (tab === 'transaksi') {
        if (tabTrx) tabTrx.style.display = 'block';
        if (tabMenu) tabMenu.style.display = 'none';
        if (btnTrx) {
            btnTrx.style.backgroundColor = 'rgba(192, 142, 92, 0.2)';
            btnTrx.style.borderColor = '#c08e5c';
            btnTrx.style.color = '#fff';
        }
        if (btnMenu) {
            btnMenu.style.backgroundColor = 'transparent';
            btnMenu.style.borderColor = '#30363d';
            btnMenu.style.color = '#8b949e';
        }
    } else {
        if (tabTrx) tabTrx.style.display = 'none';
        if (tabMenu) tabMenu.style.display = 'block';
        if (btnMenu) {
            btnMenu.style.backgroundColor = 'rgba(192, 142, 92, 0.2)';
            btnMenu.style.borderColor = '#c08e5c';
            btnMenu.style.color = '#fff';
        }
        if (btnTrx) {
            btnTrx.style.backgroundColor = 'transparent';
            btnTrx.style.borderColor = '#30363d';
            btnTrx.style.color = '#8b949e';
        }
    }
}

const totalCash = {{ (float) $totalCash }};
const totalPengeluaran = {{ (float) $totalPengeluaran }};

function formatRupiah(num) {
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function updateTotalKasLive() {
    const inputModal = document.getElementById('input_modal_awal');
    const dispTotalKas = document.getElementById('disp_total_kas_laci');
    const modalVal = parseFloat(inputModal ? inputModal.value : 0) || 0;
    const totalKas = modalVal + totalCash - totalPengeluaran;
    if (dispTotalKas) {
        dispTotalKas.textContent = formatRupiah(totalKas);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateTotalKasLive();
});
</script>

<!-- Layout Print Browser -->
<div class="d-none d-print-block print-area" style="background: #fff; color: #111;">
    <!-- KOP Header Cetak -->
    <div class="d-flex justify-content-between align-items-start border-bottom border-2 border-dark pb-2 mb-3">
        <div>
            <h3 class="fw-bold mb-0 text-dark" style="letter-spacing: 0.5px;">MASTER CAFE POS</h3>
            <div class="small text-muted">Laporan Rekonsiliasi & Penutupan Shift Kasir</div>
        </div>
        <div class="text-end small text-dark">
            <div><strong>Kasir:</strong> {{ auth()->user()->name }}</div>
            <div><strong>Tanggal Shift:</strong> {{ $shift->waktu_buka ? $shift->waktu_buka->translatedFormat('d F Y') : date('d/m/Y') }}</div>
            <div><strong>Dicetak:</strong> {{ now()->translatedFormat('d F Y H:i') }} WIB</div>
        </div>
    </div>

    <!-- 1. Ringkasan Kas & Rekonsiliasi Shift -->
    <h6 class="fw-bold text-dark text-uppercase border-bottom border-dark pb-1 mb-2">1. Ringkasan Kas & Rekonsiliasi Shift</h6>
    <table class="table table-bordered border-secondary table-sm mb-3 table-print" style="font-size: 8.5pt;">
        <tbody>
            <tr>
                <td style="width: 25%; background-color: #f8f9fa; font-weight: bold;">Waktu Buka Shift:</td>
                <td style="width: 25%;">{{ $shift->waktu_buka ? $shift->waktu_buka->format('d/m/Y H:i') : '-' }} WIB</td>
                <td style="width: 25%; background-color: #f8f9fa; font-weight: bold;">Modal Awal Kas:</td>
                <td style="width: 25%; text-align: right; font-weight: bold;">Rp {{ number_format($shift->modal_awal ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; font-weight: bold;">Waktu Tutup Shift:</td>
                <td>{{ $shift->waktu_tutup ? $shift->waktu_tutup->format('d/m/Y H:i') . ' WIB' : 'Shift Masih Berjalan (Open)' }}</td>
                <td style="background-color: #f8f9fa; font-weight: bold;">Pemasukan Tunai:</td>
                <td style="text-align: right; font-weight: bold; color: #0d6832;">Rp {{ number_format($totalCash, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; font-weight: bold;">Status Shift:</td>
                <td><span class="badge bg-secondary">{{ strtoupper($shift->status) }}</span></td>
                <td style="background-color: #f8f9fa; font-weight: bold;">Pemasukan QRIS:</td>
                <td style="text-align: right; font-weight: bold; color: #0b5ed7;">Rp {{ number_format($totalQris, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; font-weight: bold;">Total Transaksi:</td>
                <td>{{ $pembayarans->count() }} Transaksi</td>
                <td style="background-color: #e9ecef; font-weight: bold;">TOTAL PENJUALAN:</td>
                <td style="text-align: right; font-weight: bold; background-color: #e9ecef;">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; font-weight: bold;">Pengeluaran Kasir:</td>
                <td style="color: #dc3545; font-weight: bold;">Rp {{ number_format($totalPengeluaran ?? ($shift->total_pengeluaran ?? 0), 0, ',', '.') }}</td>
                <td style="background-color: #f8f9fa; font-weight: bold;">Uang Fisik Aktual:</td>
                <td style="text-align: right; font-weight: bold;">Rp {{ number_format($shift->uang_fisik_aktual ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; font-weight: bold;">Total Item Terjual:</td>
                <td>{{ $totalItemTerjual }} porsi / minuman</td>
                <td style="background-color: #f8f9fa; font-weight: bold;">Selisih Fisik Kas:</td>
                <td style="text-align: right; font-weight: bold; color: {{ ($shift->selisih ?? 0) < 0 ? '#dc3545' : (($shift->selisih ?? 0) > 0 ? '#0d6832' : '#000') }};">
                    Rp {{ number_format($shift->selisih ?? 0, 0, ',', '.') }}
                    @if(($shift->selisih ?? 0) == 0) (Pas) @elseif(($shift->selisih ?? 0) < 0) (Kurang) @else (Lebih) @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 2. Rekapitulasi Menu Terjual -->
    <h6 class="fw-bold text-dark text-uppercase border-bottom border-dark pb-1 mb-2">2. Rekapitulasi Menu Terjual</h6>
    <table class="table table-bordered border-secondary table-sm mb-3 table-print" style="font-size: 8.5pt;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="width: 35px;" class="text-center">No</th>
                <th>Menu / Produk</th>
                <th style="width: 90px;" class="text-center">Qty Terjual</th>
                <th style="width: 130px;" class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($rekapMenu as $nama => $data)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ $nama }}</td>
                <td class="text-center fw-bold">{{ $data['jumlah'] }}</td>
                <td class="text-end">Rp {{ number_format($data['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center py-2 text-muted">Belum ada item terjual</td>
            </tr>
            @endforelse
            @if($totalItemTerjual > 0)
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="2" class="text-end">TOTAL ITEM TERJUAL:</td>
                <td class="text-center">{{ $totalItemTerjual }}</td>
                <td class="text-end">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="row pt-3 text-center" style="page-break-inside: avoid;">
        <div class="col-6">
            <div class="small">Kasir yang bertugas,</div>
            <div style="height: 50px;"></div>
            <div class="fw-bold"><u>{{ auth()->user()->name }}</u></div>
            <div class="small text-muted">Staf Kasir Master Cafe</div>
        </div>
        <div class="col-6">
            <div class="small">Mengetahui / Verifikasi,</div>
            <div style="height: 50px;"></div>
            <div class="fw-bold"><u>( Supervisor / Pemilik )</u></div>
            <div class="small text-muted">Manajemen Master Cafe</div>
        </div>
    </div>
</div>

<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 10mm 12mm;
    }
    html, body {
        background: #ffffff !important;
        color: #000000 !important;
        height: auto !important;
        min-height: auto !important;
        overflow: visible !important;
        font-family: Arial, sans-serif !important;
    }
    .kasir-layout, .kasir-main, .kasir-container {
        height: auto !important;
        min-height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        display: block !important;
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    .kasir-navbar, .kasir-topbar, .offcanvas, .d-print-none, nav, header {
        display: none !important;
    }
    .print-area {
        display: block !important;
        background: #ffffff !important;
        color: #000000 !important;
        padding: 0 !important;
    }
    .table-print {
        width: 100% !important;
        border-collapse: collapse !important;
        color: #000000 !important;
        background: transparent !important;
    }
    .table-print th, .table-print td {
        border: 1px solid #333333 !important;
        color: #000000 !important;
        padding: 4px 8px !important;
        background: transparent !important;
    }
    .table-print th {
        background-color: #f2f2f2 !important;
        font-weight: bold !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
@endsection


