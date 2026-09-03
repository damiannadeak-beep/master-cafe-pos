@if(isset($promos) && count($promos) > 0)
    <div class="alert border-0 shadow-sm rounded-4 mb-4" style="background: var(--gradient-bronze); color: #ffffff;">
        <h6 class="fw-bold mb-2"><i class="bi bi-tags-fill text-white me-1"></i> Promo Spesial Hari Ini!</h6>
        <ul class="mb-0 ps-3">
            @foreach($promos as $promo)
                <li class="mb-1">
                    <strong>{{ $promo->title }}</strong> 
                    @if($promo->type == 'discount')
                        <span class="badge bg-mastercafe rounded-pill ms-1">
                        Diskon {{ $promo->discount_type == 'percentage' ? $promo->value.'%' : 'Rp '.number_format($promo->value,0,',','.') }}
                        </span>
                    @elseif($promo->type == 'package')
                        <span class="badge bg-mastercafe rounded-pill ms-1">Paket Khusus</span>
                        <span class="badge bg-warning text-white rounded-pill ms-1 shadow-sm"><i class="bi bi-tag-fill"></i> Cukup Bayar Rp {{ number_format($promo->value,0,',','.') }}</span>
                        <div class="mt-1 small">
                            <strong>Termasuk:</strong> 
                            @foreach($promo->menus as $pm)
                                <span class="badge  text-white border">{{ $pm->nama_menu }}</span>
                            @endforeach
                        </div>
                    @endif