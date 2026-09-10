@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Manajemen User</h2>
            <p class="text-white-50 mb-0">Daftar seluruh akun terdaftar. *(Sistem baru menggunakan Guest Self-Ordering tanpa butuh pendaftaran akun untuk konsumen)*.</p>
        </div>
        <a href="{{ route('admin.kasir.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Ke Manajemen Staf Waitress
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header fw-bold">Daftar Akun User</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Status Email</th>
                            <th>Role / Akses</th>
                            <th>No. HP</th>
                            <th>Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Terverifikasi</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Palsu/Belum Verifikasi</span>
                                    @endif
                                </td>
                                <td>
                                    @forelse($user->getRoleNames() as $role)
                                        @if(strtolower($role) == 'konsumen')
                                            <span class="badge bg-secondary">Akun Konsumen Lama (Legacy)</span>
                                        @else
                                            <span class="badge bg-warning text-dark text-uppercase">{{ $role }}</span>
                                        @endif
                                    @empty
                                        <span class="badge bg-secondary">User Biasa</span>
                                    @endforelse
                                </td>
                                <td>{{ $user->no_hp ?: '-' }}</td>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Belum ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
