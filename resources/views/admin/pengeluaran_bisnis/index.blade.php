@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #c08e5c;"><i class="bi bi-wallet-fill me-2"></i>Pengeluaran Bisnis / Usaha (Owner)</h2>
            <p class="text-white-50 mb-0">Catat seluruh pengeluaran modal usaha, belanja stok bahan, gaji karyawan, dan utilitas untuk menghitung Pendapatan Bersih Owner.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary fw-semibold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#createPengeluaranModal">
                <i class="bi bi-plus-circle me-1"></i> Catat Pengeluaran Baru
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 4 Stat Cards Ringkasan Kategori -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm text-white h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-white-50 small">Total Pengeluaran Usaha</span>
                        <div class="text-danger bg-danger bg-opacity-10 p-2 rounded">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalSemua, 0, ',', '.') }}</h4>
                    <small class="text-white-50" style="font-size: 0.75rem;">Bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm text-white h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-white-50 small">Belanja Stok Bahan Baku</span>
                        <div class="p-2 rounded" style="background-color: rgba(192, 142, 92, 0.15); color: #c08e5c;">
                            <i class="bi bi-box-seam fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0" style="color: #c08e5c;">Rp {{ number_format($totalStokBahan, 0, ',', '.') }}</h4>
                    <small class="text-white-50" style="font-size: 0.75rem;">Dapur, kopi, bumbu, seafood dll</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm text-white h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-white-50 small">Gaji & Insentif Karyawan</span>
                        <div class="text-info bg-info bg-opacity-10 p-2 rounded">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0 text-info">Rp {{ number_format($totalGaji, 0, ',', '.') }}</h4>
                    <small class="text-white-50" style="font-size: 0.75rem;">Upah koki, barista, kasir</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm text-white h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-white-50 small">Operasional, Listrik & Sewa</span>
                        <div class="text-warning bg-warning bg-opacity-10 p-2 rounded">
                            <i class="bi bi-lightning-charge-fill fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0 text-warning">Rp {{ number_format($totalOperasional + $totalSewa + $totalLainnya, 0, ',', '.') }}</h4>
                    <small class="text-white-50" style="font-size: 0.75rem;">PLN, air, gas LPG, sewa gedung</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card shadow-sm mb-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
        <div class="card-body py-3">
            <form action="{{ route('admin.pengeluaran_bisnis.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small text-white-50 mb-1">Periode Bulan</label>
                    <input type="month" name="bulan" class="form-control form-control-sm text-white" style="background-color: #0e1217; border-color: #21262d;" value="{{ $bulan }}" required>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small text-white-50 mb-1">Kategori Pengeluaran</label>
                    <select name="kategori" class="form-select form-select-sm text-white" style="background-color: #0e1217; border-color: #21262d;">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriLabels as $key => $label)
                            <option value="{{ $key }}" {{ $kategori == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter Data</button>
                    <a href="{{ route('admin.pengeluaran_bisnis.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data Pengeluaran Bisnis -->
    <div class="card shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important;">
        <div class="card-header fw-bold text-white d-flex justify-content-between align-items-center" style="background-color: transparent; border-bottom: 1px solid #21262d !important;">
            <span><i class="bi bi-table me-2" style="color: #c08e5c;"></i>Daftar Pengeluaran Usaha (Owner)</span>
            <span class="badge" style="background-color: rgba(192, 142, 92, 0.2); color: #c08e5c;">Khusus Akses Owner</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead class="table-dark" style="border-bottom: 1px solid #21262d;">
                        <tr>
                            <th class="ps-3" style="width: 130px;">Tanggal</th>
                            <th style="width: 180px;">Kategori</th>
                            <th>Deskripsi / Keperluan</th>
                            <th>Keterangan</th>
                            <th class="text-center" style="width: 100px;">Nota / Bon</th>
                            <th class="text-end" style="width: 170px;">Nominal (Rp)</th>
                            <th class="text-center pe-3" style="width: 110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengeluarans as $p)
                        @php
                            $badgeClass = match($p->kategori) {
                                'stok_bahan' => 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25',
                                'gaji' => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
                                'operasional' => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
                                'sewa' => 'bg-secondary bg-opacity-10 text-white-50 border border-secondary',
                                'pemeliharaan' => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                default => 'bg-dark text-white-50 border border-secondary',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3 text-white fw-medium">
                                {{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }} px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                                    {{ $p->kategori_label }}
                                </span>
                            </td>
                            <td class="fw-semibold text-white">
                                {{ $p->deskripsi }}
                            </td>
                            <td class="text-white-50 small">
                                {{ $p->keterangan ?: '-' }}
                            </td>
                            <td class="text-center">
                                @if($p->bukti_nota)
                                    <button type="button" class="btn btn-sm btn-outline-info p-1 px-2 rounded-pill" onclick="previewImage('{{ asset($p->bukti_nota) }}', '{{ addslashes($p->deskripsi) }}')" title="Lihat Foto Nota">
                                        <i class="bi bi-image me-1"></i> Foto
                                    </button>
                                @else
                                    <span class="text-white-50 small">-</span>
                                @endif
                            </td>
                            <td class="text-end text-danger fw-bold text-nowrap">
                                - Rp {{ number_format($p->nominal, 0, ',', '.') }}
                            </td>
                            <td class="text-center pe-3">
                                <div class="d-inline-flex align-items-center justify-content-center gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center justify-content-center rounded-2" style="width: 32px; height: 32px; padding: 0;" onclick="editPengeluaran({{ json_encode($p) }})" title="Edit">
                                        <i class="bi bi-pencil" style="font-size: 0.85rem; margin: 0 !important;"></i>
                                    </button>
                                    <form action="{{ route('admin.pengeluaran_bisnis.destroy', $p->id) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('Hapus data pengeluaran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center rounded-2" style="width: 32px; height: 32px; padding: 0;" title="Hapus">
                                            <i class="bi bi-trash" style="font-size: 0.85rem; margin: 0 !important;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-white-50 py-5">
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
        <div class="card-footer border-top py-3" style="background-color: transparent; border-color: #21262d !important;">
            {{ $pengeluarans->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Pengeluaran Bisnis -->
<div class="modal fade" id="createPengeluaranModal" tabindex="-1" aria-labelledby="createPengeluaranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="createPengeluaranModalLabel" style="color: #c08e5c;">
                    <i class="bi bi-wallet2 me-2"></i>Catat Pengeluaran Usaha Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.pengeluaran_bisnis.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small text-white-50 fw-semibold">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white-50 fw-semibold">Kategori Pengeluaran <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select text-white" style="background-color: #0e1217; border-color: #21262d;" required>
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            @foreach($kategoriLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white-50 fw-semibold">Deskripsi / Keperluan <span class="text-danger">*</span></label>
                        <input type="text" name="deskripsi" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" placeholder="Misal: Belanja Daging & Sayur Pasar, Gaji Koki Budi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white-50 fw-semibold">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="nominal" class="form-control text-white fs-5 fw-bold" style="background-color: #0e1217; border-color: #21262d; color: #f56565 !important;" placeholder="0" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white-50 fw-semibold">Keterangan / Rincian Tambahan (Opsional)</label>
                        <textarea name="keterangan" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" rows="2" placeholder="Catatan toko, supplier, atau nomor rekening transfer"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-white-50 fw-semibold">Foto Struk / Kwitansi (Opsional, Max 3MB)</label>
                        <input type="file" name="bukti_nota" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pengeluaran Bisnis -->
<div class="modal fade" id="editPengeluaranModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: #c08e5c;">
                    <i class="bi bi-pencil-square me-2"></i>Edit Pengeluaran Usaha
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small text-white-50 fw-semibold">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" id="edit_tanggal" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white-50 fw-semibold">Kategori Pengeluaran <span class="text-danger">*</span></label>
                        <select name="kategori" id="edit_kategori" class="form-select text-white" style="background-color: #0e1217; border-color: #21262d;" required>
                            @foreach($kategoriLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white-50 fw-semibold">Deskripsi / Keperluan <span class="text-danger">*</span></label>
                        <input type="text" name="deskripsi" id="edit_deskripsi" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white-50 fw-semibold">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="nominal" id="edit_nominal" class="form-control text-white fs-5 fw-bold" style="background-color: #0e1217; border-color: #21262d; color: #f56565 !important;" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white-50 fw-semibold">Keterangan Tambahan</label>
                        <textarea name="keterangan" id="edit_keterangan" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-white-50 fw-semibold">Ganti Foto Struk / Kwitansi (Opsional)</label>
                        <input type="file" name="bukti_nota" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Preview Foto Nota -->
<div class="modal fade" id="previewImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content text-white" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="previewImageTitle">Bukti Nota</h6>
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
