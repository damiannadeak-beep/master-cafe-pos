@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h4 fw-bold text-white mb-0">Manajemen Staf Waitress</h1>
                <span class="text-secondary small fw-normal ms-2 fs-6">(Akses POS & Kasir)</span>
            </div>
            <p class="text-secondary small mb-0">Kelola akun staf waitress, pengaturan shift kerja, dan hak akses operasional kasir.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.absensi.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 rounded-2" style="border-color: #30363d; color: #d0d7de;">
                <i class="bi bi-calendar-check" style="color: #c08e5c;"></i> Rekap Absensi
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 rounded-2" style="border-color: #30363d; color: #d0d7de;">
                <i class="bi bi-people"></i> Semua User
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="background-color: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.25); color: #34d399;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="background-color: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.25); color: #f87171;">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metric Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Total Staf Waitress</div>
                        <div class="fs-4 fw-bold text-white">{{ $kasirs->total() }}</div>
                        <div class="text-secondary small mt-0.5" style="font-size: 0.72rem;">Akun aktif dengan hak akses POS</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.25); color: #c08e5c;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Shift Pagi</div>
                        <div class="fs-4 fw-bold text-white">{{ $kasirs->where('shift', 'pagi')->count() }}</div>
                        <div class="text-secondary small mt-0.5" style="font-size: 0.72rem;">Jam operasional 08:00 - 17:00</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.25); color: #fbbf24;">
                        <i class="bi bi-sun-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Shift Malam</div>
                        <div class="fs-4 fw-bold text-white">{{ $kasirs->where('shift', 'malam')->count() }}</div>
                        <div class="text-secondary small mt-0.5" style="font-size: 0.72rem;">Jam operasional 17:00 - 00:00</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(129, 140, 248, 0.12); border: 1px solid rgba(129, 140, 248, 0.25); color: #a5b4fc;">
                        <i class="bi bi-moon-stars-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Kolom Kiri: Tabel Data Staf Waitress -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge" style="color: #c08e5c;"></i>
                        <h5 class="mb-0 fw-bold text-white fs-6">Daftar Akun Waitress</h5>
                    </div>
                    <span class="text-secondary small fw-medium">
                        {{ $kasirs->total() }} Staf Terdaftar
                    </span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="color: #c9d1d9;">
                            <thead style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                                <tr>
                                    <th class="ps-4 py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Staf Waitress</th>
                                    <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">No. WhatsApp</th>
                                    <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Shift Kerja</th>
                                    <th class="pe-4 py-3 text-end text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kasirs as $k)
                                    <tr style="border-bottom: 1px solid #21262d;">
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; background: rgba(192, 142, 92, 0.12); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25); font-size: 0.85rem;">
                                                    {{ strtoupper(substr($k->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-white">{{ $k->name }}</div>
                                                    <div class="text-secondary small" style="font-size: 0.75rem;">{{ $k->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 text-secondary small">
                                            @if($k->no_hp)
                                                <span class="text-white fw-medium"><i class="bi bi-telephone text-secondary me-1"></i>{{ $k->no_hp }}</span>
                                            @else
                                                <span class="text-secondary opacity-50">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @if($k->shift == 'pagi')
                                                <span class="badge rounded-2 fw-medium px-2.5 py-1" style="background-color: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.25); font-size: 0.72rem;">
                                                    <i class="bi bi-sun-fill me-1"></i> Pagi
                                                </span>
                                            @elseif($k->shift == 'malam')
                                                <span class="badge rounded-2 fw-medium px-2.5 py-1" style="background-color: rgba(129, 140, 248, 0.12); color: #a5b4fc; border: 1px solid rgba(129, 140, 248, 0.25); font-size: 0.72rem;">
                                                    <i class="bi bi-moon-stars-fill me-1"></i> Malam
                                                </span>
                                            @else
                                                <span class="text-secondary small">-</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 py-3 text-end">
                                            <div class="action-btn-group d-inline-flex align-items-center">
                                                <a href="{{ route('admin.kasir.edit', $k->id) }}" class="btn btn-icon d-inline-flex align-items-center justify-content-center p-0 rounded-2" style="width: 36px; height: 36px; background: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.25); color: #c08e5c;" title="Edit Data Staf">
                                                    <i class="bi bi-pencil" style="font-size: 0.85rem;"></i>
                                                </a>
                                                <form class="d-inline-flex m-0 p-0" action="{{ route('admin.kasir.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun waitress ini? Akses kasir akan langsung dicabut.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-icon d-inline-flex align-items-center justify-content-center p-0 rounded-2" style="width: 36px; height: 36px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); color: #f87171;" title="Hapus Akun">
                                                        <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.03); border: 1px solid #21262d; color: #8b949e;">
                                                    <i class="bi bi-person-x fs-3"></i>
                                                </div>
                                                <h6 class="text-white fw-bold mb-1">Belum Ada Akun Waitress</h6>
                                                <p class="text-secondary small mb-0">Tambahkan akun staf kasir/waitress baru melalui formulir di samping.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($kasirs->hasPages())
                    <div class="card-footer py-3 px-4" style="background-color: rgba(255, 255, 255, 0.02); border-top: 1px solid #21262d;">
                        {{ $kasirs->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Form Tambah Staf Waitress -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-plus" style="color: #c08e5c;"></i>
                        <h5 class="mb-0 fw-bold text-white fs-6">Tambah Akun Staf</h5>
                    </div>
                    <span class="text-secondary small fw-medium">Baru</span>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.kasir.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label text-secondary small fw-medium mb-1">Nama Staf Waitress</label>
                            <input type="text" class="form-control text-white @error('name') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Safik Hidayat" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label text-secondary small fw-medium mb-1">Alamat Email Staf</label>
                            <input type="email" class="form-control text-white @error('email') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" id="email" name="email" value="{{ old('email') }}" placeholder="waitress@mastercafe.com" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="shift" class="form-label text-secondary small fw-medium mb-1">Penugasan Shift</label>
                            <select class="form-select text-white @error('shift') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" id="shift" name="shift" required>
                                <option value="pagi" {{ old('shift') == 'pagi' ? 'selected' : '' }}>🌅 Shift Pagi (08:00 - 17:00)</option>
                                <option value="malam" {{ old('shift') == 'malam' ? 'selected' : '' }}>🌙 Shift Malam (17:00 - 00:00)</option>
                            </select>
                            @error('shift') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label text-secondary small fw-medium mb-1">Password</label>
                            <input type="password" class="form-control text-white @error('password') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" id="password" name="password" placeholder="Minimal 6 karakter" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label text-secondary small fw-medium mb-1">Konfirmasi Password</label>
                            <input type="password" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" id="password_confirmation" name="password_confirmation" placeholder="Ketik ulang password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-medium py-2 rounded-3 d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-check2"></i> Buat Akun Waitress
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
