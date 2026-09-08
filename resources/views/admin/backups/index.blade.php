@extends('layouts.admin')

@section('title', 'Backup Database')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-accent"><i class="bi bi-shield-check me-2"></i>Backup Database</h4>
                <p class="text-white-50 mb-0">Kelola cadangan data Anda. Backup otomatis berjalan setiap jam 03:00 pagi.</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('admin.settings') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Ke Pengaturan
                </a>
                <form action="{{ route('admin.backups.run') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span>Memproses...'; this.form.submit();">
                        <i class="bi bi-play-circle me-2"></i>Backup Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card kasir-card hover-lift">
        <div class="card-header bg-transparent border-bottom pt-4 pb-3 px-4">
            <h5 class="mb-0 fw-bold"><i class="bi bi-archive me-2 text-info"></i>Daftar File Backup ({{ count($files) }} file)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Nama File</th>
                            <th>Ukuran</th>
                            <th>Tanggal Dibuat</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($files as $index => $file)
                            <tr>
                                <td class="ps-4 text-white-50">{{ $index + 1 }}</td>
                                <td>
                                    <i class="bi bi-file-earmark-zip me-2 text-warning"></i>
                                    <span class="fw-medium">{{ $file['name'] }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-25 text-info rounded-pill px-3 py-2">
                                        {{ $file['size'] }} KB
                                    </span>
                                </td>
                                <td class="text-white-50">{{ $file['date'] }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.backups.download', $file['name']) }}" class="btn btn-sm btn-outline-success rounded-pill me-1" title="Download">
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                    <form action="{{ route('admin.backups.delete', $file['name']) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus file backup ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-white-50">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                    Belum ada file backup. Klik tombol <strong>"Backup Sekarang"</strong> untuk membuat backup pertama.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card kasir-card hover-lift mt-4">
        <div class="card-body px-4 py-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle text-info fs-5 me-3"></i>
                <div class="text-white-50 small">
                    <strong class="text-white">Informasi:</strong> Backup otomatis berjalan setiap hari jam 03:00 pagi. File backup yang berusia lebih dari 7 hari akan dihapus secara otomatis. Disarankan untuk rutin mengunduh file backup ke perangkat pribadi Anda.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection