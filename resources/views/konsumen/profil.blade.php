@extends('layouts.app')

@section('content')
<div class="container py-5">
    
    <div class="row mb-4">
        <div class="col-md-8 mx-auto text-center">
            @if($user->foto)
                <img src="{{ asset('uploads/profil/' . $user->foto) }}" alt="Foto Profil" class="rounded-circle mb-3 border border-4 border-white shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
            @else
                <div class="d-inline-block text-white p-3 rounded-circle mb-3" style="width: 100px; height: 100px; line-height: 70px;">
                    <i class="bi bi-person-fill" style="font-size: 3rem;"></i>
                </div>
            @endif
            <h2 class="fw-bold text-white" style="font-family: 'Rye', serif;">Halo, {{ $user->name }}!</h2>
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
                <div class="card-header  border-bottom-0 p-0">
                    <ul class="nav nav-pills nav-justified" id="pills-tab" role="tablist" style="border-bottom: 2px solid #21262d;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-0 fw-bold py-3" id="pills-aktif-tab" data-bs-toggle="pill" data-bs-target="#pills-aktif" type="button" role="tab" style="border-bottom: 3px solid transparent;">
                                <i class="bi bi-basket me-2"></i>Pesanan Aktif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 fw-bold py-3 text-secondary" id="pills-riwayat-tab" data-bs-toggle="pill" data-bs-target="#pills-riwayat" type="button" role="tab" style="border-bottom: 3px solid transparent;" onclick="this.classList.remove('text-secondary');">
                                <i class="bi bi-clock-history me-2"></i>Riwayat
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 fw-bold py-3 text-secondary" id="pills-profil-tab" data-bs-toggle="pill" data-bs-target="#pills-profil" type="button" role="tab" style="border-bottom: 3px solid transparent;" onclick="this.classList.remove('text-secondary');">
                                <i class="bi bi-gear me-2"></i>Profil
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4 p-md-5 ">
                    <div class="tab-content" id="pills-tabContent">
                        
                        <!-- TAB PESANAN AKTIF -->
                        <div class="tab-pane fade show active" id="pills-aktif" role="tabpanel">
                            <h5 class="fw-bold mb-4">Pesanan Sedang Berlangsung</h5>
                            @include("components.konsumen.active-order-card")
                        </div>

                        <!-- TAB RIWAYAT -->
                        <div class="tab-pane fade" id="pills-riwayat" role="tabpanel">
                            <h5 class="fw-bold mb-4">Riwayat Pesanan Saya</h5>
                            @include("components.konsumen.order-history-card")
                        </div>

                        <!-- TAB PROFIL -->
                        <div class="tab-pane fade" id="pills-profil" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8 mx-auto">
                                    <div class="card border-0 shadow-sm rounded-4" style="background-color: #0e1217; border: 1px solid #21262d !important;">
                                        <div class="card-body p-4 p-md-5">
                                            <h5 class="fw-bold mb-4 text-center">Informasi Akun</h5>
                                            
                                            @include("components.konsumen.profile-edit-form")
                                            
                                            <hr class="mb-4 border-light">
                                            
                                            <div class="text-center">
                                                <p class="text-secondary small mb-3">Ingin keluar dari akun ini?</p>
                                                <form action="{{ route('logout') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger px-4 rounded-pill fw-bold btn-touch">
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
        color: #6c757d;
        background: transparent;
        transition: 0.3s;
    }
    .nav-pills .nav-link:hover {
        color: #c08e5c;
    }
    .nav-pills .nav-link.active {
        color: #c08e5c !important;
        background: transparent;
        border-bottom: 3px solid #c08e5c !important;
    }
    .rounded-4 {
        border-radius: 1rem !important;
    }
    .form-control:focus {
        box-shadow: none;
        border-color: #c08e5c;
        background-color: #0e1217 !important;
        color: white !important;
    }
    . { background-color: #0e1217 !important; }
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
                    alert(data.message);
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
