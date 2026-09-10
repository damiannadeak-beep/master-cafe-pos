@extends('layouts.waitress')

@section('content')
<div class="container-fluid px-0 py-0">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-accent"><i class="bi bi-box-seam me-2"></i>Update Stok Produk</h4>
            <p class="text-white-50">Perbarui jumlah stok produk jadi dan ketersediaan menu secara langsung.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close btn-touch" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('kasir.stok.update') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- Kolom Menu (Produk Jadi) -->
            <div class="col-12 col-xl-10 mx-auto">
                <div class="card kasir-card hover-lift">
                    <div class="card-header bg-transparent border-bottom pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-cup-straw me-2 text-info"></i>Daftar Stok Produk (Menu)</h5>
                        <span class="badge bg-secondary">{{ $menus->count() }} Item</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-dark table-hover align-middle mb-0">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th class="ps-4">Nama Produk</th>
                                        <th class="text-center">Kategori</th>
                                        <th class="text-center" style="width: 160px;">Jumlah Stok</th>
                                        <th class="pe-4 text-center" style="width: 140px;">Ketersediaan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($menus as $menu)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-light">{{ $menu->nama_menu }}</div>
                                                <div class="small text-white-50">Rp {{ number_format($menu->harga, 0, ',', '.') }}</div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $menu->kategori == 'makanan' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                                    {{ ucfirst($menu->kategori) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <input type="number" id="stok-{{ $menu->id }}" name="menu[{{ $menu->id }}]" class="form-control text-white border-secondary text-center mx-auto" value="{{ $menu->stok }}" min="0" style="width: 100px;" onchange="checkAvailability({{ $menu->id }})" onkeyup="checkAvailability({{ $menu->id }})">
                                            </td>
                                            <td class="pe-4 text-center">
                                                <select id="avail-{{ $menu->id }}" name="menu_available[{{ $menu->id }}]" class="form-select text-white border-secondary form-select-sm text-center mx-auto" style="width: 100px; font-weight: bold; color: {{ $menu->is_available ? '#198754' : '#dc3545' }}" onchange="updateColor(this)">
                                                    <option value="1" {{ $menu->is_available ? 'selected' : '' }} class="text-success">Tersedia</option>
                                                    <option value="0" {{ !$menu->is_available ? 'selected' : '' }} class="text-danger">Habis</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if($menus->isEmpty())
                                        <tr><td colspan="4" class="text-center py-4 text-white-50">Belum ada menu produk.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm btn-touch">
                        <i class="bi bi-save me-2"></i> Simpan Pembaruan Stok
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Validasi otomatis saat input stok diubah
    function checkAvailability(id) {
        const stokInput = document.getElementById('stok-' + id);
        const availSelect = document.getElementById('avail-' + id);
        
        let stok = parseInt(stokInput.value) || 0;
        
        if (stok <= 0) {
            availSelect.value = "0";
            availSelect.style.color = '#dc3545';
        } else {
            if(availSelect.value === "0" && availSelect.dataset.wasZero) {
                availSelect.value = "1";
                availSelect.style.color = '#198754';
                availSelect.dataset.wasZero = '';
            }
        }
        
        if(stok <= 0) availSelect.dataset.wasZero = 'true';
    }

    function updateColor(select) {
        select.style.color = select.value === '1' ? '#198754' : '#dc3545';
        
        const id = select.id.split('-')[1];
        const stokInput = document.getElementById('stok-' + id);
        if (select.value === '1' && (parseInt(stokInput.value) || 0) <= 0) {
            alert('Stok masih 0! Tidak dapat diatur menjadi Tersedia.');
            select.value = '0';
            select.style.color = '#dc3545';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input[id^="stok-"]').forEach(input => {
            const id = input.id.split('-')[1];
            checkAvailability(id);
        });
    });
</script>
@endsection
