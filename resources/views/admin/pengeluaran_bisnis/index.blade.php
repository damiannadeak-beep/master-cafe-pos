@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h4 fw-bold text-white mb-0">Pengeluaran Bisnis</h1>
                <span class="text-secondary small fw-normal ms-2 fs-6">(Akses Owner)</span>
            </div>
            <p class="text-secondary small mb-0">Kelola biaya modal, stok bahan baku, upah tim, dan utilitas operasional kafe.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-medium rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createPengeluaranModal">
                <i class="bi bi-plus-lg"></i>
                <span>Catat Pengeluaran</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3" role="alert" style="background-color: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.25); color: #34d399;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" style="background-color: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.25); color: #f87171;">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 4 Stat Cards Ringkasan Kategori (Refined Executive Dark) -->
    <div class="row g-3 mb-4">
        <!-- Total Pengeluaran -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-medium">Total Pengeluaran</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background-color: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171;">
                            <i class="bi bi-wallet2" style="font-size: 1rem;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-1" style="font-size: 1.45rem;">Rp {{ number_format($totalSemua, 0, ',', '.') }}</h3>
                    <div class="text-secondary small" style="font-size: 0.75rem;">
                        Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Stok Bahan Baku -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-medium">Stok Bahan Baku</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background-color: rgba(192, 142, 92, 0.08); border: 1px solid rgba(192, 142, 92, 0.2); color: #c08e5c;">
                            <i class="bi bi-box-seam" style="font-size: 1rem;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-1" style="font-size: 1.45rem;">Rp {{ number_format($totalStokBahan, 0, ',', '.') }}</h3>
                    <div class="text-secondary small text-truncate" style="font-size: 0.75rem;" title="Dapur, kopi, bumbu & supply">
                        Dapur, kopi, bumbu & supply
                    </div>
                </div>
            </div>
        </div>

        <!-- Gaji & Insentif -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-medium">Gaji & Insentif</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); color: #d0d7de;">
                            <i class="bi bi-people" style="font-size: 1rem;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-1" style="font-size: 1.45rem;">Rp {{ number_format($totalGaji, 0, ',', '.') }}</h3>
                    <div class="text-secondary small text-truncate" style="font-size: 0.75rem;" title="Upah koki, barista & kasir">
                        Upah koki, barista & kasir
                    </div>
                </div>
            </div>
        </div>

        <!-- Operasional & Sewa -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-medium">Operasional & Sewa</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); color: #d0d7de;">
                            <i class="bi bi-lightning-charge" style="font-size: 1rem;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-1" style="font-size: 1.45rem;">Rp {{ number_format($totalOperasional + $totalSewa + $totalLainnya, 0, ',', '.') }}</h3>
                    <div class="text-secondary small text-truncate" style="font-size: 0.75rem;" title="PLN, air, gas, sewa gedung">
                        PLN, air, gas, sewa gedung
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
        <div class="card-body p-3">
            <form action="{{ route('admin.pengeluaran_bisnis.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Periode Bulan</label>
                    <input type="month" name="bulan" class="form-control form-control-sm text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px; height: 38px;" value="{{ $bulan }}" required>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Kategori Pengeluaran</label>
                    <select name="kategori" class="form-select form-select-sm text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px; height: 38px;">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriLabels as $key => $label)
                            <option value="{{ $key }}" {{ $kategori == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-1.5 fw-medium flex-grow-1" style="border-radius: 8px; height: 38px;">
                        <i class="bi bi-funnel"></i>
                        <span>Filter Data</span>
                    </button>
                    <a href="{{ route('admin.pengeluaran_bisnis.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; width: 38px; height: 38px; flex-shrink: 0;" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data Pengeluaran Bisnis -->
    <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d !important;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-receipt" style="color: #c08e5c;"></i>
                <span class="fw-semibold text-white fs-6">Rincian Pengeluaran Usaha</span>
            </div>
            <span class="text-secondary small fw-medium">
                Total: {{ $pengeluarans->total() }} Data
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255, 255, 255, 0.02);">
                    <thead>
                        <tr style="border-bottom: 1px solid #21262d; background-color: rgba(255, 255, 255, 0.02);">
                            <th class="text-secondary text-uppercase fw-semibold py-3 ps-4" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 130px;">Tanggal</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 170px;">Kategori</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Deskripsi / Keperluan</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Keterangan</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3 text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 100px;">Nota / Bon</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3 text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 160px;">Nominal (Rp)</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 pe-4 text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengeluarans as $p)
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <td class="ps-4 py-3 text-white-50 small text-nowrap">
                                {{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-3 py-3">
                                <span class="badge rounded-2 px-2.5 py-1 text-secondary" style="background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.75rem; font-weight: 500;">
                                    {{ $p->kategori_label }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="fw-medium text-white d-block">{{ $p->deskripsi }}</span>
                            </td>
                            <td class="px-3 py-3 text-secondary small">
                                {{ $p->keterangan ?: '-' }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if($p->bukti_nota)
                                    <button type="button" class="btn btn-sm d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-2" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.12); color: #d0d7de; font-size: 0.75rem;" onclick="previewImage('{{ asset($p->bukti_nota) }}', '{{ addslashes($p->deskripsi) }}')" title="Lihat Foto Nota">
                                        <i class="bi bi-image" style="font-size: 0.8rem; color: #c08e5c;"></i>
                                        <span>Nota</span>
                                    </button>
                                @else
                                    <span class="text-secondary small opacity-50">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-end text-nowrap">
                                <span class="fw-semibold text-white">Rp {{ number_format($p->nominal, 0, ',', '.') }}</span>
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="d-inline-flex justify-content-end align-items-center gap-2 flex-nowrap" style="gap: 8px !important;">
                                    <button type="button" class="btn btn-sm btn-icon d-inline-flex align-items-center justify-content-center p-0 rounded-2" style="width: 36px; height: 36px; background: rgba(192, 142, 92, 0.08); border: 1px solid rgba(192, 142, 92, 0.25); color: #c08e5c;" onclick="editPengeluaran({{ json_encode($p) }})" title="Edit Pengeluaran">
                                        <i class="bi bi-pencil" style="font-size: 0.85rem;"></i>
                                    </button>
                                    <form action="{{ route('admin.pengeluaran_bisnis.destroy', $p->id) }}" method="POST" class="d-inline-flex m-0 p-0" onsubmit="return confirm('Hapus data pengeluaran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon d-inline-flex align-items-center justify-content-center p-0 rounded-2" style="width: 36px; height: 36px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); color: #f87171;" title="Hapus Pengeluaran">
                                            <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary small">
                                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                Belum ada data pengeluaran bisnis yang dicatat pada bulan ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pengeluarans->hasPages())
        <div class="card-footer px-4 py-3" style="background-color: #161b22; border-top: 1px solid #21262d;">
            {{ $pengeluarans->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Pengeluaran Bisnis -->
<div class="modal fade" id="createPengeluaranModal" tabindex="-1" aria-labelledby="createPengeluaranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 14px;">
            <div class="modal-header py-3 px-4" style="border-bottom: 1px solid #21262d;">
                <h5 class="modal-title fs-6 fw-bold text-white d-flex align-items-center gap-2" id="createPengeluaranModalLabel">
                    <i class="bi bi-plus-circle" style="color: #c08e5c;"></i> Catat Pengeluaran Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.pengeluaran_bisnis.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-medium">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-medium">Kategori Pengeluaran <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" required>
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            @foreach($kategoriLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-medium">Deskripsi / Keperluan <span class="text-danger">*</span></label>
                        <input type="text" name="deskripsi" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" placeholder="Misal: Belanja Daging & Sayur Pasar, Gaji Koki Budi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-medium">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="nominal" class="form-control text-white fs-5 fw-bold" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" placeholder="0" min="1" onkeydown="if(['e','E','+','-'].includes(event.key)) event.preventDefault();" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-medium">Keterangan Tambahan (Opsional)</label>
                        <textarea name="keterangan" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" rows="2" placeholder="Catatan toko, supplier, atau nomor rekening transfer"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-secondary fw-medium">Foto Struk / Kwitansi (Opsional, Max 3MB)</label>
                        <input type="file" name="bukti_nota" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer py-3 px-4" style="border-top: 1px solid #21262d;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-medium px-4">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pengeluaran Bisnis -->
<div class="modal fade" id="editPengeluaranModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 14px;">
            <div class="modal-header py-3 px-4" style="border-bottom: 1px solid #21262d;">
                <h5 class="modal-title fs-6 fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square" style="color: #c08e5c;"></i> Edit Pengeluaran Usaha
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-medium">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" id="edit_tanggal" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-medium">Kategori Pengeluaran <span class="text-danger">*</span></label>
                        <select name="kategori" id="edit_kategori" class="form-select text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" required>
                            @foreach($kategoriLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-medium">Deskripsi / Keperluan <span class="text-danger">*</span></label>
                        <input type="text" name="deskripsi" id="edit_deskripsi" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-medium">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="nominal" id="edit_nominal" class="form-control text-white fs-5 fw-bold" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" min="1" onkeydown="if(['e','E','+','-'].includes(event.key)) event.preventDefault();" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-medium">Keterangan Tambahan</label>
                        <textarea name="keterangan" id="edit_keterangan" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-secondary fw-medium">Ganti Foto Struk / Kwitansi (Opsional)</label>
                        <input type="file" name="bukti_nota" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer py-3 px-4" style="border-top: 1px solid #21262d;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-medium px-4">Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Preview Foto Nota -->
<div class="modal fade" id="previewImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content text-white" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 14px;">
            <div class="modal-header py-3 px-4" style="border-bottom: 1px solid #21262d;">
                <h6 class="modal-title fw-bold text-white" id="previewImageTitle">Bukti Nota</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="previewImageSrc" src="" alt="Bukti Nota" class="img-fluid rounded border" style="max-height: 75vh; object-fit: contain; border-color: #21262d !important;">
            </div>
        </div>
    </div>
</div>

<script>
function editPengeluaran(item) {
    document.getElementById('editForm').action = '/admin/pengeluaran-bisnis/' + item.id;
    document.getElementById('edit_tanggal').value = item.tanggal.substring(0, 10);
    document.getElementById('edit_kategori').value = item.kategori;
    document.getElementById('edit_deskripsi').value = item.deskripsi;
    document.getElementById('edit_nominal').value = parseInt(item.nominal);
    document.getElementById('edit_keterangan').value = item.keterangan || '';
    
    var modal = new bootstrap.Modal(document.getElementById('editPengeluaranModal'));
    modal.show();
}

function previewImage(src, title) {
    document.getElementById('previewImageSrc').src = src;
    document.getElementById('previewImageTitle').innerText = 'Bukti Nota: ' + title;
    var modal = new bootstrap.Modal(document.getElementById('previewImageModal'));
    modal.show();
}
</script>
@endsection
