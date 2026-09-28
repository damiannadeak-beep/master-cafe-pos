@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h4 fw-bold text-white mb-0">Manajemen User</h1>
                <span class="text-secondary small fw-normal ms-2 fs-6">(Database Akun)</span>
            </div>
            <p class="text-secondary small mb-0">Daftar seluruh akun terdaftar pada sistem Master Cafe POS.</p>
        </div>
        <a href="{{ route('admin.kasir.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 rounded-2" style="border-color: #30363d; color: #d0d7de;">
            <i class="bi bi-arrow-left"></i> Ke Manajemen Staf Waitress
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="background-color: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.25); color: #34d399;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-people" style="color: #c08e5c;"></i>
                <h5 class="mb-0 fw-bold text-white fs-6">Daftar Akun Pengguna</h5>
            </div>
            <span class="text-secondary small fw-medium">
                {{ $users->total() }} Akun Terdaftar
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: #c9d1d9;">
                    <thead style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                        <tr>
                            <th class="ps-4 py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Pengguna</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Status Email</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Role / Hak Akses</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">No. HP</th>
                            <th class="pe-4 py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr style="border-bottom: 1px solid #21262d;">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; background: rgba(192, 142, 92, 0.12); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25); font-size: 0.82rem;">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-white">{{ $user->name }}</div>
                                            <div class="text-secondary small" style="font-size: 0.75rem;">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    @if($user->email_verified_at)
                                        <span class="badge rounded-2 fw-medium px-2 py-0.5" style="background-color: rgba(52, 211, 153, 0.12); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.25); font-size: 0.72rem;">
                                            <i class="bi bi-check-circle-fill me-1"></i> Terverifikasi
                                        </span>
                                    @else
                                        <span class="badge rounded-2 fw-medium px-2 py-0.5" style="background-color: rgba(239, 68, 68, 0.08); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.72rem;">
                                            <i class="bi bi-x-circle me-1"></i> Belum Verifikasi
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @forelse($user->getRoleNames() as $role)
                                        @if(strtolower($role) == 'pemilik')
                                            <span class="badge rounded-2 fw-medium px-2.5 py-1" style="background-color: rgba(192, 142, 92, 0.15); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.3); font-size: 0.72rem;">
                                                <i class="bi bi-shield-lock-fill me-1"></i> PEMILIK
                                            </span>
                                        @elseif(strtolower($role) == 'kasir')
                                            <span class="badge rounded-2 fw-medium px-2.5 py-1" style="background-color: rgba(129, 140, 248, 0.12); color: #a5b4fc; border: 1px solid rgba(129, 140, 248, 0.25); font-size: 0.72rem;">
                                                <i class="bi bi-person-badge me-1"></i> KASIR / WAITRESS
                                            </span>
                                        @else
                                            <span class="badge fw-medium px-2 py-0.5 rounded-2" style="background-color: rgba(255, 255, 255, 0.05); color: #8b949e; border: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.72rem;">
                                                KONSUMEN
                                            </span>
                                        @endif
                                    @empty
                                        <span class="badge fw-medium px-2 py-0.5 rounded-2" style="background-color: rgba(255, 255, 255, 0.05); color: #8b949e; border: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.72rem;">User</span>
                                    @endforelse
                                </td>
                                <td class="py-3 text-secondary small">
                                    {{ $user->no_hp ?: '-' }}
                                </td>
                                <td class="pe-4 py-3 text-secondary small">
                                    {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-secondary">Belum ada data user terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($users->hasPages())
            <div class="card-footer py-3 px-4" style="background-color: rgba(255, 255, 255, 0.02); border-top: 1px solid #21262d;">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
