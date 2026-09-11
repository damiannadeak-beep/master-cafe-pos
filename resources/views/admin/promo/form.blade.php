@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #c08e5c;">
                <i class="bi bi-tag-fill me-2"></i>{{ $promo->exists ? 'Edit Promo / Paket Hemat' : 'Buat Promo / Paket Hemat Baru' }}
            </h2>
            <p class="text-white-50 mb-0">
                Atur penawaran bundling menu combo untuk meningkatkan rata-rata belanja (average order value) pelanggan Master Cafe.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.promo.index') }}" class="btn btn-outline-secondary text-white-50 border-secondary px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Promo
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="background-color: #3b181a; border: 1px solid #6b262b; color: #f8d7da;">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Perhatian! Terjadi kesalahan input:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- FORMULIR INPUT (SISI KIRI) -->
        <div class="col-lg-7 col-xl-8">
            <div class="card border-0 shadow-sm text-white" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-header py-3" style="background-color: #0d1117; border-bottom: 1px solid #21262d;">
                    <h5 class="fw-bold mb-0 text-white">
                        <i class="bi bi-sliders me-2 text-warning"></i>Formulir Pengaturan Promo
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ $promo->exists ? route('admin.promo.update', $promo->id) : route('admin.promo.store') }}" method="POST" id="promoForm">
                        @csrf
                        @if($promo->exists) @method('PUT') @endif

                        <!-- Judul Promo -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-white">
                                Nama Promo / Paket <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" id="inputTitle" class="form-control text-white border-secondary bg-dark"
                                   placeholder="Contoh: Paket Santai Kopi + Kentang Goreng"
                                   value="{{ old('title', $promo->title) }}" required>
                            <div class="form-text text-white-50">Nama yang akan dilihat oleh kasir dan konsumen di menu.</div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-white">Deskripsi Singkat (Opsional)</label>
                            <textarea name="description" id="inputDesc" rows="2" class="form-control text-white border-secondary bg-dark"
                                      placeholder="Contoh: Nikmati perpaduan Kopi Susu Aren dengan cemilan kentang gurih lebih hemat Rp 5.000!">{{ old('description', $promo->description) }}</textarea>
                        </div>

                        <!-- Tipe Promo (Paket Bundling vs Diskon) -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-white d-block">Pilih Model Promo <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="card h-100 p-3 border cursor-pointer promo-type-card {{ old('type', $promo->type ?? 'package') === 'package' ? 'active-type' : '' }}"
                                           style="background-color: #1a1e27; border-color: #2b3240; cursor: pointer;">
                                        <div class="d-flex align-items-center">
                                            <input type="radio" name="type" value="package" class="form-check-input me-3" id="typePackage"
                                                   {{ old('type', $promo->type ?? 'package') === 'package' ? 'checked' : '' }}>
                                            <div>
                                                <div class="fw-bold text-white fs-6">
                                                    <i class="bi bi-boxes me-1 text-warning"></i> Paket Hemat (Bundling)
                                                </div>
                                                <div class="text-white-50 small mt-1">Gabungan beberapa menu dengan harga spesial combo</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="card h-100 p-3 border cursor-pointer promo-type-card {{ old('type', $promo->type ?? 'package') === 'discount' ? 'active-type' : '' }}"
                                           style="background-color: #1a1e27; border-color: #2b3240; cursor: pointer;">
                                        <div class="d-flex align-items-center">
                                            <input type="radio" name="type" value="discount" class="form-check-input me-3" id="typeDiscount"
                                                   {{ old('type', $promo->type ?? 'package') === 'discount' ? 'checked' : '' }}>
                                            <div>
                                                <div class="fw-bold text-white fs-6">
                                                    <i class="bi bi-percent me-1 text-info"></i> Diskon Reguler
                                                </div>
                                                <div class="text-white-50 small mt-1">Potongan harga dalam persen (%) atau nominal Rupiah</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- BLOK 1: PENGATURAN PAKET BUNDLING (COMBO) -->
                        <div id="sectionPackage" class="p-3 mb-4 rounded border" style="background-color: #0f131a; border-color: #21262d !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0" style="color: #e5b98a;">
                                    <i class="bi bi-bag-check-fill me-2"></i>Komposisi Menu dalam Paket Hemat
                                </h6>
                                <button type="button" id="btnAddMenuRow" class="btn btn-sm text-white" style="background-color: #238636; border-color: #2ea043;">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Menu
                                </button>
                            </div>
                            <p class="text-white-50 small mb-3">
                                Pilih menu apa saja yang harus dipesan pelanggan untuk mendapatkan harga promo paket ini.
                            </p>

                            <!-- Daftar Baris Menu Dinamis -->
                            <div id="packageMenuContainer" class="d-flex flex-column gap-2 mb-3">
                                @php
                                    $existingPackageMenus = $promo->exists && $promo->type === 'package' ? $promo->menus : collect([]);
                                @endphp

                                @if($existingPackageMenus->isNotEmpty())
                                    @foreach($existingPackageMenus as $index => $em)
                                        <div class="row g-2 align-items-center package-menu-row p-2 rounded" style="background-color: #161b22; border: 1px solid #282e39;">
                                            <div class="col-7 col-sm-8">
                                                <select name="package_menus[]" class="form-select text-white bg-dark border-secondary select-menu-item" required>
                                                    <option value="">-- Pilih Menu Kafe --</option>
                                                    @foreach($allMenus as $m)
                                                        <option value="{{ $m->id }}" data-price="{{ $m->harga }}" data-name="{{ $m->nama_menu }}" {{ $m->id == $em->id ? 'selected' : '' }}>
                                                            {{ $m->nama_menu }} (Rp {{ number_format($m->harga, 0, ',', '.') }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-3 col-sm-3">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-dark text-white-50 border-secondary px-2" style="font-size: 0.8rem;">Porsi</span>
                                                    <input type="number" name="package_qty[]" class="form-control text-white bg-dark border-secondary text-center input-menu-qty"
                                                           value="{{ $em->pivot->jumlah ?? 1 }}" min="1" max="99" required>
                                                </div>
                                            </div>
                                            <div class="col-2 col-sm-1 text-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm border-secondary btn-remove-row" title="Hapus menu">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Baris Menu Default 1 -->
                                    <div class="row g-2 align-items-center package-menu-row p-2 rounded" style="background-color: #161b22; border: 1px solid #282e39;">
                                        <div class="col-7 col-sm-8">
                                            <select name="package_menus[]" class="form-select text-white bg-dark border-secondary select-menu-item" required>
                                                <option value="">-- Pilih Menu Kafe (1) --</option>
                                                @foreach($allMenus as $m)
                                                    <option value="{{ $m->id }}" data-price="{{ $m->harga }}" data-name="{{ $m->nama_menu }}">
                                                        {{ $m->nama_menu }} (Rp {{ number_format($m->harga, 0, ',', '.') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-3 col-sm-3">
                                            <div class="input-group">
                                                <span class="input-group-text bg-dark text-white-50 border-secondary px-2" style="font-size: 0.8rem;">Porsi</span>
                                                <input type="number" name="package_qty[]" class="form-control text-white bg-dark border-secondary text-center input-menu-qty"
                                                       value="1" min="1" max="99" required>
                                            </div>
                                        </div>
                                        <div class="col-2 col-sm-1 text-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm border-secondary btn-remove-row" title="Hapus menu">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Baris Menu Default 2 (Bundling butuh teman combo) -->
                                    <div class="row g-2 align-items-center package-menu-row p-2 rounded" style="background-color: #161b22; border: 1px solid #282e39;">
                                        <div class="col-7 col-sm-8">
                                            <select name="package_menus[]" class="form-select text-white bg-dark border-secondary select-menu-item">
                                                <option value="">-- Pilih Menu Kafe (2) --</option>
                                                @foreach($allMenus as $m)
                                                    <option value="{{ $m->id }}" data-price="{{ $m->harga }}" data-name="{{ $m->nama_menu }}">
                                                        {{ $m->nama_menu }} (Rp {{ number_format($m->harga, 0, ',', '.') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-3 col-sm-3">
                                            <div class="input-group">
                                                <span class="input-group-text bg-dark text-white-50 border-secondary px-2" style="font-size: 0.8rem;">Porsi</span>
                                                <input type="number" name="package_qty[]" class="form-control text-white bg-dark border-secondary text-center input-menu-qty"
                                                       value="1" min="1" max="99">
                                            </div>
                                        </div>
                                        <div class="col-2 col-sm-1 text-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm border-secondary btn-remove-row" title="Hapus menu">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Realtime Price Calculation Card -->
                            <div class="p-3 rounded mt-3" style="background-color: #161b22; border: 1px dashed rgba(192, 142, 92, 0.4);">
                                <div class="row align-items-center g-3">
                                    <div class="col-sm-6">
                                        <div class="text-white-50 small">Total Harga Normal Menu:</div>
                                        <div class="fs-4 fw-bold text-white text-decoration-line-through" id="calcNormalPriceDisplay">
                                            Rp 0
                                        </div>
                                        <small class="text-white-50" style="font-size: 0.75rem;">(Harga jika beli terpisah)</small>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label text-white fw-bold mb-1" style="color: #e5b98a !important;">
                                            Harga Spesial Paket Bundling <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark border-secondary text-warning fw-bold">Rp</span>
                                            <input type="number" name="value" id="inputPackageValue"
                                                   class="form-control text-white bg-dark border-secondary fs-5 fw-bold"
                                                   placeholder="Contoh: 25000"
                                                   value="{{ old('value', $promo->exists && $promo->type === 'package' ? ($promo->value + 0) : '') }}"
                                                   min="0" step="500">
                                        </div>
                                    </div>
                                </div>

                                <!-- Feedback Penghematan -->
                                <div class="mt-3 pt-2 border-top border-secondary border-opacity-25" id="savingsFeedbackBox">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-stars text-warning fs-5"></i>
                                        <div>
                                            <span class="fw-bold text-success" id="savingsText">Konsumen Hemat: Rp 0 (0%)</span>
                                            <div class="text-white-50 small" id="savingsNote">Pilih menu dan tentukan harga paket di atas.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BLOK 2: PENGATURAN DISKON REGULER (JIKA DIPILIH) -->
                        <div id="sectionDiscount" class="p-3 mb-4 rounded border d-none" style="background-color: #0f131a; border-color: #21262d !important;">
                            <h6 class="fw-bold mb-3 text-info">
                                <i class="bi bi-percent me-2"></i>Pengaturan Diskon Reguler
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-white small fw-semibold">Tipe Diskon</label>
                                    <select name="discount_type" id="inputDiscountType" class="form-select text-white bg-dark border-secondary">
                                        <option value="percentage" {{ old('discount_type', $promo->discount_type) === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                        <option value="nominal" {{ old('discount_type', $promo->discount_type) === 'nominal' ? 'selected' : '' }}>Nominal Tunai (Rp)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-white small fw-semibold">Besar Diskon <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" id="inputDiscountValue" class="form-control text-white bg-dark border-secondary"
                                               placeholder="Contoh: 10 atau 5000"
                                               value="{{ old('value', $promo->exists && $promo->type === 'discount' ? ($promo->value + 0) : '') }}" min="0">
                                        <span class="input-group-text bg-dark border-secondary text-info fw-bold" id="discountUnitLabel">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BLOK 3: HARI BERLAKU -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold text-white mb-0">
                                    <i class="bi bi-calendar3 me-1 text-warning"></i> Hari Berlaku Promo
                                </label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="btnSelectAllDays">Pilih Semua</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="btnClearDays">Reset</button>
                                </div>
                            </div>
                            <p class="text-white-50 small mb-2">Kosongkan jika promo berlaku setiap hari tanpa batasan hari.</p>
                            <div class="d-flex flex-wrap gap-2">
                                @php
                                    $selectedDays = old('days', $promo->days ?? []);
                                    $daysList = [
                                        'Monday' => 'Senin',
                                        'Tuesday' => 'Selasa',
                                        'Wednesday' => 'Rabu',
                                        'Thursday' => 'Kamis',
                                        'Friday' => 'Jumat',
                                        'Saturday' => 'Sabtu',
                                        'Sunday' => 'Minggu'
                                    ];
                                @endphp
                                @foreach($daysList as $enDay => $idDay)
                                    @php
                                        $checked = is_array($selectedDays) && in_array($enDay, $selectedDays);
                                    @endphp
                                    <input type="checkbox" class="btn-check day-checkbox" name="days[]" value="{{ $enDay }}" id="day_{{ $enDay }}" {{ $checked ? 'checked' : '' }}>
                                    <label class="btn btn-outline-secondary btn-sm px-3 py-1" for="day_{{ $enDay }}">{{ $idDay }}</label>
                                @endforeach
                            </div>
                        </div>

                        <!-- BLOK 4: PERIODE TANGGAL & STATUS AKTIF -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-white small fw-semibold">Tanggal Mulai (Opsional)</label>
                                <input type="datetime-local" name="starts_at" id="inputStartsAt" class="form-control text-white bg-dark border-secondary"
                                       value="{{ old('starts_at', $promo->starts_at ? $promo->starts_at->format('Y-m-d\TH:i') : '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white small fw-semibold">Tanggal Berakhir (Opsional)</label>
                                <input type="datetime-local" name="ends_at" id="inputEndsAt" class="form-control text-white bg-dark border-secondary"
                                       value="{{ old('ends_at', $promo->ends_at ? $promo->ends_at->format('Y-m-d\TH:i') : '') }}">
                            </div>
                        </div>

                        <!-- Status Aktif -->
                        <div class="form-check form-switch mb-4 p-3 rounded" style="background-color: #0f131a; border: 1px solid #21262d;">
                            <input class="form-check-input ms-0 me-3" type="checkbox" name="is_active" id="isActiveSwitch" role="switch"
                                   {{ old('is_active', $promo->exists ? $promo->is_active : true) ? 'checked' : '' }}
                                   style="width: 2.5em; height: 1.25em;">
                            <label class="form-check-label text-white fw-bold" for="isActiveSwitch">
                                Aktifkan Promo Ini Sekarang
                            </label>
                            <div class="text-white-50 small ms-5">
                                Jika diaktifkan, kasir dan konsumen dapat langsung memilih paket promo ini saat bertransaksi.
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top border-secondary border-opacity-25">
                            <a href="{{ route('admin.promo.index') }}" class="btn btn-outline-secondary px-4 py-2">Batal</a>
                            <button type="submit" class="btn text-white fw-bold px-4 py-2 shadow-sm" style="background-color: #c08e5c; border-color: #c08e5c;">
                                <i class="bi bi-check2-circle me-2"></i>{{ $promo->exists ? 'Perbarui Promo' : 'Simpan Promo Baru' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- PRATINJAU VISUAL KARTU PROMO (SISI KANAN) -->
        <div class="col-lg-5 col-xl-4">
            <div class="position-sticky" style="top: 20px;">
                <div class="card border-0 shadow-sm text-white mb-3" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <div class="card-header py-3" style="background-color: #0d1117; border-bottom: 1px solid #21262d;">
                        <h6 class="fw-bold mb-0 text-white">
                            <i class="bi bi-eye-fill me-2 text-info"></i>Pratinjau Kartu Promo (Live)
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <p class="text-white-50 small mb-3">Tampilan penawaran promo yang dilihat staf dan pelanggan:</p>

                        <!-- Visual Card Promo -->
                        <div class="card border-0 text-white overflow-hidden shadow"
                             style="background: linear-gradient(145deg, #1c222c, #0e1217); border: 1px solid rgba(192, 142, 92, 0.4) !important; border-radius: 12px;">
                            <div class="p-3">
                                <!-- Badge Tag Header -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge px-2 py-1" id="previewBadgeType"
                                          style="background-color: rgba(192, 142, 92, 0.2); color: #e5b98a; border: 1px solid rgba(192, 142, 92, 0.4); font-size: 0.75rem;">
                                        <i class="bi bi-boxes me-1"></i> PAKET HEMAT COMBO
                                    </span>
                                    <span class="badge bg-success" id="previewStatusBadge" style="font-size: 0.7rem;">
                                        Aktif
                                    </span>
                                </div>

                                <!-- Judul & Deskripsi Preview -->
                                <h5 class="fw-bold text-white mb-1" id="previewTitle">Paket Santai Kopi + Kentang</h5>
                                <p class="text-white-50 small mb-3" id="previewDesc">Nikmati perpaduan Kopi Susu Aren dengan cemilan kentang gurih lebih hemat!</p>

                                <!-- Komposisi Menu Preview -->
                                <div id="previewItemsBox" class="p-2 rounded mb-3" style="background-color: rgba(0,0,0,0.25); border: 1px solid #252c38;">
                                    <div class="text-white-50 small fw-semibold mb-2" style="font-size: 0.75rem;">ISI DALAM PAKET:</div>
                                    <div id="previewItemList" class="d-flex flex-column gap-1">
                                        <div class="small text-white"><i class="bi bi-check-circle-fill text-success me-1"></i> Menu belum dipilih</div>
                                    </div>
                                </div>

                                <!-- Skema Harga Preview -->
                                <div class="d-flex justify-content-between align-items-end pt-2 border-top border-secondary border-opacity-25">
                                    <div>
                                        <div class="text-white-50 text-decoration-line-through small" id="previewOldPrice" style="font-size: 0.8rem;">
                                            Rp 0
                                        </div>
                                        <div class="fw-bold fs-4" id="previewFinalPrice" style="color: #e5b98a;">
                                            Rp 0
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 py-1 px-2 fw-semibold" id="previewSavingsBadge" style="font-size: 0.8rem;">
                                            Hemat Rp 0
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Card Preview: Jadwal -->
                            <div class="px-3 py-2 text-white-50 small" style="background-color: rgba(0,0,0,0.3); font-size: 0.72rem; border-top: 1px solid #21262d;">
                                <i class="bi bi-clock-history me-1"></i>
                                <span id="previewScheduleText">Berlaku: Setiap Hari</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips Praktis Owner -->
                <div class="card border-0 text-white" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <div class="card-body p-3">
                        <div class="d-flex gap-2">
                            <i class="bi bi-lightbulb-fill text-warning fs-5"></i>
                            <div>
                                <h6 class="fw-bold mb-1 text-white" style="font-size: 0.85rem;">Tips Bundling Menu Kafe:</h6>
                                <p class="text-white-50 small mb-0" style="font-size: 0.75rem;">
                                    Gabungkan menu minuman <em>high-margin</em> (Kopi/Teh) dengan makanan ringan (Snack/Kentang) dengan potongan Rp 3.000 - Rp 5.000. Pelanggan merasa untung, dan omset kafe meningkat!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .promo-type-card {
        transition: all 0.2s ease;
    }
    .promo-type-card:hover {
        border-color: #c08e5c !important;
    }
    .promo-type-card.active-type {
        border-color: #c08e5c !important;
        background-color: rgba(192, 142, 92, 0.1) !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allMenusData = @json($allMenus);
        const menuMap = {};
        allMenusData.forEach(m => { menuMap[m.id] = m; });

        const form = document.getElementById('promoForm');
        const inputTitle = document.getElementById('inputTitle');
        const inputDesc = document.getElementById('inputDesc');
        const typeRadios = document.querySelectorAll('input[name="type"]');
        const sectionPackage = document.getElementById('sectionPackage');
        const sectionDiscount = document.getElementById('sectionDiscount');
        const packageMenuContainer = document.getElementById('packageMenuContainer');
        const btnAddMenuRow = document.getElementById('btnAddMenuRow');
        const calcNormalPriceDisplay = document.getElementById('calcNormalPriceDisplay');
        const inputPackageValue = document.getElementById('inputPackageValue');
        const inputDiscountValue = document.getElementById('inputDiscountValue');
        const inputDiscountType = document.getElementById('inputDiscountType');
        const discountUnitLabel = document.getElementById('discountUnitLabel');
        const savingsFeedbackBox = document.getElementById('savingsFeedbackBox');
        const savingsText = document.getElementById('savingsText');
        const savingsNote = document.getElementById('savingsNote');
        const isActiveSwitch = document.getElementById('isActiveSwitch');

        // Preview Elements
        const previewTitle = document.getElementById('previewTitle');
        const previewDesc = document.getElementById('previewDesc');
        const previewBadgeType = document.getElementById('previewBadgeType');
        const previewStatusBadge = document.getElementById('previewStatusBadge');
        const previewItemList = document.getElementById('previewItemList');
        const previewItemsBox = document.getElementById('previewItemsBox');
        const previewOldPrice = document.getElementById('previewOldPrice');
        const previewFinalPrice = document.getElementById('previewFinalPrice');
        const previewSavingsBadge = document.getElementById('previewSavingsBadge');
        const previewScheduleText = document.getElementById('previewScheduleText');

        function formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }

        // 1. Tipe Promo Toggle
        typeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.promo-type-card').forEach(card => card.classList.remove('active-type'));
                this.closest('.promo-type-card').classList.add('active-type');

                if (this.value === 'package') {
                    sectionPackage.classList.remove('d-none');
                    sectionDiscount.classList.add('d-none');
                    inputPackageValue.setAttribute('name', 'value');
                    inputDiscountValue.removeAttribute('name');
                    previewBadgeType.innerHTML = '<i class="bi bi-boxes me-1"></i> PAKET HEMAT COMBO';
                    previewItemsBox.classList.remove('d-none');
                } else {
                    sectionPackage.classList.add('d-none');
                    sectionDiscount.classList.remove('d-none');
                    inputDiscountValue.setAttribute('name', 'value');
                    inputPackageValue.removeAttribute('name');
                    previewBadgeType.innerHTML = '<i class="bi bi-percent me-1"></i> DISKON REGULER';
                    previewItemsBox.classList.add('d-none');
                }
                updateCalculationsAndPreview();
            });
        });

        // 2. Tambah Baris Menu Baru
        btnAddMenuRow.addEventListener('click', function() {
            let rowCount = packageMenuContainer.querySelectorAll('.package-menu-row').length + 1;
            let div = document.createElement('div');
            div.className = 'row g-2 align-items-center package-menu-row p-2 rounded';
            div.style = 'background-color: #161b22; border: 1px solid #282e39;';

            let optionsHtml = '<option value="">-- Pilih Menu Kafe (' + rowCount + ') --</option>';
            allMenusData.forEach(m => {
                optionsHtml += `<option value="${m.id}" data-price="${m.harga}" data-name="${m.nama_menu}">${m.nama_menu} (${formatRupiah(m.harga)})</option>`;
            });

            div.innerHTML = `
                <div class="col-7 col-sm-8">
                    <select name="package_menus[]" class="form-select text-white bg-dark border-secondary select-menu-item" required>
                        ${optionsHtml}
                    </select>
                </div>
                <div class="col-3 col-sm-3">
                    <div class="input-group">
                        <span class="input-group-text bg-dark text-white-50 border-secondary px-2" style="font-size: 0.8rem;">Porsi</span>
                        <input type="number" name="package_qty[]" class="form-control text-white bg-dark border-secondary text-center input-menu-qty"
                               value="1" min="1" max="99" required>
                    </div>
                </div>
                <div class="col-2 col-sm-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm border-secondary btn-remove-row" title="Hapus menu">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            packageMenuContainer.appendChild(div);
            bindRowEvents(div);
            updateCalculationsAndPreview();
        });

        function bindRowEvents(row) {
            row.querySelector('.select-menu-item').addEventListener('change', updateCalculationsAndPreview);
            row.querySelector('.input-menu-qty').addEventListener('input', updateCalculationsAndPreview);
            row.querySelector('.btn-remove-row').addEventListener('click', function() {
                if (packageMenuContainer.querySelectorAll('.package-menu-row').length > 1) {
                    row.remove();
                    updateCalculationsAndPreview();
                } else {
                    alert('Paket hemat membutuhkan minimal 1 menu.');
                }
            });
        }

        packageMenuContainer.querySelectorAll('.package-menu-row').forEach(row => bindRowEvents(row));

        // 3. Kalkulator & Update Live Preview
        function updateCalculationsAndPreview() {
            const isPackage = document.querySelector('input[name="type"]:checked')?.value === 'package';

            // Update Title & Desc
            previewTitle.textContent = inputTitle.value.trim() || 'Judul Promo Paket';
            previewDesc.textContent = inputDesc.value.trim() || 'Deskripsi paket combo hemat Master Cafe.';

            // Status Badge
            previewStatusBadge.textContent = isActiveSwitch.checked ? 'Aktif' : 'Nonaktif';
            previewStatusBadge.className = 'badge ' + (isActiveSwitch.checked ? 'bg-success' : 'bg-secondary');

            if (isPackage) {
                let normalTotal = 0;
                let itemsListHtml = '';
                let validItemCount = 0;

                packageMenuContainer.querySelectorAll('.package-menu-row').forEach(row => {
                    const select = row.querySelector('.select-menu-item');
                    const qtyInput = row.querySelector('.input-menu-qty');
                    const menuId = select.value;
                    const qty = parseInt(qtyInput.value) || 1;

                    if (menuId && menuMap[menuId]) {
                        const menu = menuMap[menuId];
                        normalTotal += (menu.harga * qty);
                        validItemCount++;
                        itemsListHtml += `
                            <div class="d-flex justify-content-between align-items-center text-white small py-1 border-bottom border-secondary border-opacity-10">
                                <div><i class="bi bi-check2-circle text-success me-1"></i> ${menu.nama_menu} <strong class="text-warning">(${qty}x)</strong></div>
                                <span class="text-white-50">${formatRupiah(menu.harga * qty)}</span>
                            </div>
                        `;
                    }
                });

                previewItemList.innerHTML = itemsListHtml || '<div class="small text-white-50"><i class="bi bi-info-circle me-1"></i> Belum ada menu yang dipilih</div>';

                calcNormalPriceDisplay.textContent = formatRupiah(normalTotal);
                previewOldPrice.textContent = normalTotal > 0 ? formatRupiah(normalTotal) : '';
                previewOldPrice.style.display = normalTotal > 0 ? 'block' : 'none';

                const packagePrice = parseFloat(inputPackageValue.value) || 0;
                previewFinalPrice.textContent = formatRupiah(packagePrice);

                const savings = normalTotal - packagePrice;
                if (packagePrice > 0 && normalTotal > 0) {
                    if (savings > 0) {
                        const pct = ((savings / normalTotal) * 100).toFixed(1);
                        savingsText.textContent = `Konsumen Hemat: ${formatRupiah(savings)} (${pct}%)`;
                        savingsText.className = 'fw-bold text-success';
                        savingsNote.textContent = 'Strategi bundling menarik! Pelanggan hemat Rp ' + savings.toLocaleString('id-ID') + '.';
                        previewSavingsBadge.textContent = `Hemat ${formatRupiah(savings)}`;
                        previewSavingsBadge.className = 'badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 py-1 px-2 fw-semibold';
                    } else if (savings === 0) {
                        savingsText.textContent = 'Harga paket sama dengan harga normal (Diskon Rp 0)';
                        savingsText.className = 'fw-bold text-warning';
                        savingsNote.textContent = 'Sebaiknya berikan sedikit potongan harga agar pelanggan tertarik membeli combo.';
                        previewSavingsBadge.textContent = 'Harga Normal';
                        previewSavingsBadge.className = 'badge bg-secondary py-1 px-2';
                    } else {
                        savingsText.textContent = 'Perhatian: Harga paket lebih mahal dari harga asli total!';
                        savingsText.className = 'fw-bold text-danger';
                        savingsNote.textContent = 'Pastikan harga paket lebih murah dari ' + formatRupiah(normalTotal) + '.';
                        previewSavingsBadge.textContent = 'Harga Lebih Tinggi';
                        previewSavingsBadge.className = 'badge bg-danger py-1 px-2';
                    }
                } else {
                    savingsText.textContent = 'Konsumen Hemat: Rp 0 (0%)';
                    savingsText.className = 'fw-bold text-white-50';
                    savingsNote.textContent = 'Masukkan harga paket bundling di atas untuk melihat perhitungan penghematan.';
                    previewSavingsBadge.textContent = 'Belum Ada Harga';
                }
            } else {
                // Diskon Reguler
                const discountType = inputDiscountType.value;
                const discountVal = parseFloat(inputDiscountValue.value) || 0;

                if (discountType === 'percentage') {
                    discountUnitLabel.textContent = '%';
                    previewFinalPrice.textContent = discountVal + '% OFF';
                    previewOldPrice.style.display = 'none';
                    previewSavingsBadge.textContent = 'Diskon ' + discountVal + '%';
                } else {
                    discountUnitLabel.textContent = 'Rp';
                    previewFinalPrice.textContent = 'Potongan ' + formatRupiah(discountVal);
                    previewOldPrice.style.display = 'none';
                    previewSavingsBadge.textContent = 'Potongan ' + formatRupiah(discountVal);
                }
            }

            // Update Jadwal Hari Text
            const selectedDays = [];
            document.querySelectorAll('.day-checkbox:checked').forEach(cb => {
                const label = document.querySelector(`label[for="${cb.id}"]`);
                if (label) selectedDays.push(label.textContent.trim());
            });
            if (selectedDays.length === 0 || selectedDays.length === 7) {
                previewScheduleText.textContent = 'Berlaku: Setiap Hari';
            } else {
                previewScheduleText.textContent = 'Berlaku: ' + selectedDays.join(', ');
            }
        }

        // Bind Inputs to Preview
        inputTitle.addEventListener('input', updateCalculationsAndPreview);
        inputDesc.addEventListener('input', updateCalculationsAndPreview);
        inputPackageValue.addEventListener('input', updateCalculationsAndPreview);
        inputDiscountValue.addEventListener('input', updateCalculationsAndPreview);
        inputDiscountType.addEventListener('change', updateCalculationsAndPreview);
        isActiveSwitch.addEventListener('change', updateCalculationsAndPreview);
        document.querySelectorAll('.day-checkbox').forEach(cb => cb.addEventListener('change', updateCalculationsAndPreview));

        // Quick Day Selectors
        document.getElementById('btnSelectAllDays').addEventListener('click', function() {
            document.querySelectorAll('.day-checkbox').forEach(cb => cb.checked = true);
            updateCalculationsAndPreview();
        });
        document.getElementById('btnClearDays').addEventListener('click', function() {
            document.querySelectorAll('.day-checkbox').forEach(cb => cb.checked = false);
            updateCalculationsAndPreview();
        });

        // Initialize on load
        const initialType = document.querySelector('input[name="type"]:checked')?.value || 'package';
        if (initialType === 'package') {
            sectionPackage.classList.remove('d-none');
            sectionDiscount.classList.add('d-none');
            inputPackageValue.setAttribute('name', 'value');
            inputDiscountValue.removeAttribute('name');
        } else {
            sectionPackage.classList.add('d-none');
            sectionDiscount.classList.remove('d-none');
            inputDiscountValue.setAttribute('name', 'value');
            inputPackageValue.removeAttribute('name');
        }
        updateCalculationsAndPreview();
    });
</script>
@endsection
