@extends('layouts.app')

@section('content')
<div class="container py-5">
    
    <div class="row mb-4">
        <div class="col-md-8 mx-auto text-center">
            <div class="mb-3 d-inline-block position-relative">
                <img src="{{ $user->foto ? asset('uploads/profil/' . $user->foto) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=c08e5c&color=fff&size=120' }}" 
                     alt="Foto Profil" 
                     class="rounded-circle border border-4 shadow-sm" 
                     style="width: 100px; height: 100px; object-fit: cover; border-color: #c08e5c !important;"
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=c08e5c&color=fff&size=120';">
            </div>
            <h2 class="fw-bold text-white" style="font-family: 'Outfit', sans-serif !important;">Halo, {{ $user->name }}!</h2>
            <p class="text-secondary">Kelola profil dan pantau status pesanan Anda di sini.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card border-0 shadow-sm overflow-hidden rounded-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-header border-bottom-0 p-0" style="background-color: #11151a;">
                    <ul class="nav nav-pills nav-justified" id="pills-tab" role="tablist" style="border-bottom: 1px solid #21262d;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex justify-content-center align-items-center active rounded-0 fw-semibold py-3 px-2 px-md-3" id="pills-aktif-tab" data-bs-toggle="pill" data-bs-target="#pills-aktif" type="button" role="tab" style="font-family: 'Outfit', sans-serif !important;">
                                <i class="bi bi-basket me-2 fs-6"></i>
                                <span class="text-nowrap">Pesanan Aktif</span>
                                @if(isset($pesananAktif) && $pesananAktif->count() > 0)
                                    <span class="badge rounded-pill bg-warning text-dark ms-2" style="font-size: 0.7rem; padding: 0.25em 0.55em;">{{ $pesananAktif->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex justify-content-center align-items-center rounded-0 fw-semibold py-3 px-2 px-md-3 text-secondary" id="pills-riwayat-tab" data-bs-toggle="pill" data-bs-target="#pills-riwayat" type="button" role="tab" style="font-family: 'Outfit', sans-serif !important;">
                                <i class="bi bi-clock-history me-2 fs-6"></i>
                                <span class="text-nowrap">Riwayat</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-flex justify-content-center align-items-center rounded-0 fw-semibold py-3 px-2 px-md-3 text-secondary" id="pills-profil-tab" data-bs-toggle="pill" data-bs-target="#pills-profil" type="button" role="tab" style="font-family: 'Outfit', sans-serif !important;">
                                <i class="bi bi-gear me-2 fs-6"></i>
                                <span class="text-nowrap">Profil</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="tab-content" id="pills-tabContent">
                        
                        <!-- TAB PESANAN AKTIF -->
                        <div class="tab-pane fade show active" id="pills-aktif" role="tabpanel">
                            <div class="text-center mb-4">
                                <h5 class="fw-bold text-white mb-1" style="font-family: 'Outfit', sans-serif !important; font-size: 1.25rem;">Pesanan Sedang Berlangsung</h5>
                                <p class="text-secondary small mb-0" style="font-family: 'Outfit', sans-serif !important;">Pantau progres pesanan makanan dan minuman Anda secara langsung</p>
                            </div>
                            @include("components.konsumen.active-order-card")
                        </div>

                        <!-- TAB RIWAYAT -->
                        <div class="tab-pane fade" id="pills-riwayat" role="tabpanel">
                            <div class="text-center mb-4">
                                <h5 class="fw-bold text-white mb-1" style="font-family: 'Outfit', sans-serif !important; font-size: 1.25rem;">Riwayat Pesanan Saya</h5>
                                <p class="text-secondary small mb-0" style="font-family: 'Outfit', sans-serif !important;">Daftar seluruh transaksi dan pesanan yang pernah Anda buat</p>
                            </div>
                            @include("components.konsumen.order-history-card")
                        </div>

                        <!-- TAB PROFIL -->
                        <div class="tab-pane fade" id="pills-profil" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8 mx-auto">
                                    <div class="card border-0 shadow-sm rounded-4" style="background-color: #0e1217; border: 1px solid #21262d !important;">
                                        <div class="card-body p-4 p-md-5">
                                            <div class="text-center mb-4">
                                                <h5 class="fw-bold text-white mb-1" style="font-family: 'Outfit', sans-serif !important; font-size: 1.25rem;">Informasi Akun</h5>
                                                <p class="text-secondary small mb-0" style="font-family: 'Outfit', sans-serif !important;">Perbarui profil dan nomor kontak akun Anda</p>
                                            </div>
                                            
                                            @include("components.konsumen.profile-edit-form")
                                            
                                            <hr class="mb-4" style="border-color: #21262d !important;">
                                            
                                            <div class="text-center">
                                                <p class="text-secondary small mb-3">Ingin keluar dari akun ini?</p>
                                                <form action="{{ route('logout') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger px-4 rounded-pill fw-bold btn-touch" style="font-family: 'Outfit', sans-serif !important;">
                                                        <i class="bi bi-box-arrow-right me-1"></i> Keluar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling Tabs */
    .nav-pills .nav-link {
        color: #8b949e;
        background: transparent;
        transition: all 0.2s ease-in-out;
        border-bottom: 2px solid transparent !important;
        font-family: 'Outfit', sans-serif !important;
        font-size: 0.95rem;
    }
    .nav-pills .nav-link:hover {
        color: #e6edf3;
        background: rgba(255, 255, 255, 0.02);
    }
    .nav-pills .nav-link.active {
        color: #c08e5c !important;
        background: rgba(192, 142, 92, 0.06) !important;
        border-bottom: 2px solid #c08e5c !important;
    }
    .rounded-4 {
        border-radius: 1rem !important;
    }
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(192, 142, 92, 0.15) !important;
        border-color: #c08e5c !important;
        background-color: #0e1217 !important;
        color: white !important;
    }
</style>

<script>
    function previewFoto(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('foto-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function callBell(id_meja) {
        if (!id_meja) return;
        const expireKey = 'call_bell_expire_' + id_meja;
        try {
            const expire = localStorage.getItem(expireKey);
            if (expire) {
                const remaining = Math.ceil((parseInt(expire) - Date.now()) / 1000);
                if (remaining > 0) {
                    alert('Pelayan sudah dipanggil. Mohon tunggu ' + remaining + ' detik lagi.');
                    return;
                }
            }
        } catch(e) {}

        if(confirm('Panggil pelayan ke meja Anda?')) {
            fetch('/konsumen/call-bell', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id_meja: id_meja })
            })
            .then(res => res.json())
            .then(data => {
                if(data.error) {
                    alert(data.error);
                } else {
                    alert('🔔 ' + (data.message || 'Pelayan segera datang ke meja Anda.'));
                    try {
                        localStorage.setItem(expireKey, Date.now() + (120 * 1000));
                    } catch(e) {}
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan koneksi.');
            });
        }
    }

    // Live Order Tracking: Reload active tab automatically if there are active orders
    @if(count($pesananAktif) > 0)
    setInterval(() => {
        // Only refresh if 'Pesanan Aktif' tab is currently visible
        let activeTab = document.querySelector('#pills-aktif');
        if (activeTab.classList.contains('active')) {
            // Optional: Fetch only the html of this tab or just reload
            location.reload();
        }
    }, 30000); // 30 seconds
    @endif
</script>
@endsection
