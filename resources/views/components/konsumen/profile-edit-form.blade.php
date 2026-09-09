<form action="/konsumen/profil/update" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-4 text-center">
        <div class="position-relative d-inline-block shadow-sm rounded-circle" style="width: 110px; height: 110px;">
            <img id="foto-preview" 
                 src="{{ $user->foto ? asset('uploads/profil/' . $user->foto) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=c08e5c&color=fff&size=120' }}" 
                 alt="Foto Profil" 
                 class="rounded-circle border border-3" 
                 style="width: 110px; height: 110px; object-fit: cover; cursor: pointer; border-color: #c08e5c !important;" 
                 onclick="document.getElementById('foto-input').click();"
                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=c08e5c&color=fff&size=120';">
            <div class="position-absolute bottom-0 end-0 text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                 style="background: var(--gradient-bronze); width: 34px; height: 34px; cursor: pointer; border: 2px solid #0e1217; transform: translate(5%, 5%);" 
                 onclick="document.getElementById('foto-input').click();" title="Ganti Foto">
                <i class="bi bi-camera-fill small"></i>
            </div>
        </div>
        <input type="file" id="foto-input" name="foto" class="d-none" accept="image/*" onchange="previewFoto(event)">
        <small class="text-secondary d-block mt-2" style="font-family: 'Outfit', sans-serif !important; font-size: 0.8rem;">Klik kamera untuk mengganti foto profil (Maks. 2MB)</small>
    </div>
    <div class="mb-3">
        <label class="form-label text-secondary small fw-semibold text-uppercase" style="font-family: 'Outfit', sans-serif !important; font-size: 0.78rem; letter-spacing: 0.04em;">Nama Lengkap</label>
        <input type="text" name="name" class="form-control text-white" style="background-color: #161b22; border: 1px solid #21262d; font-family: 'Outfit', sans-serif !important; padding: 0.75rem 1rem;" value="{{ $user->name }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label text-secondary small fw-semibold text-uppercase" style="font-family: 'Outfit', sans-serif !important; font-size: 0.78rem; letter-spacing: 0.04em;">Email</label>
        <input type="email" name="email" class="form-control text-white" style="background-color: #161b22; border: 1px solid #21262d; font-family: 'Outfit', sans-serif !important; padding: 0.75rem 1rem;" value="{{ $user->email }}" required>
    </div>
    <div class="mb-4">
        <label class="form-label text-secondary small fw-semibold text-uppercase" style="font-family: 'Outfit', sans-serif !important; font-size: 0.78rem; letter-spacing: 0.04em;">Nomor WhatsApp / HP</label>
        <div class="input-group">
            <span class="input-group-text border-end-0 text-secondary" style="background-color: #161b22; border-color: #21262d;"><i class="bi bi-telephone"></i></span>
            <input type="text" name="no_hp" class="form-control border-start-0 text-white" style="background-color: #161b22; border-color: #21262d; font-family: 'Outfit', sans-serif !important; padding: 0.75rem 1rem;" value="{{ $user->no_hp }}" placeholder="08xxxxxxxx">
        </div>
    </div>
    <button type="submit" class="btn btn-touch w-100 fw-bold rounded-pill mb-3 shadow-sm" style="background: var(--gradient-bronze); color: white; border: none; font-family: 'Outfit', sans-serif !important; padding: 0.75rem 1.25rem;">
        Simpan Perubahan
    </button>
</form>
