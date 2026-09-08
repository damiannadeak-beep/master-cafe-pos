<form action="/konsumen/profil/update" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-5 text-center">
                                                    <div class="position-relative d-inline-block shadow-sm rounded-circle" style="width: 120px; height: 120px;">
                                                        <img id="foto-preview" 
                                                             src="{{ $user->foto ? asset('uploads/profil/' . $user->foto) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=c08e5c&color=fff&size=120' }}" 
                                                             alt="Foto Profil" 
                                                             class="rounded-circle border border-4" 
                                                             style="width: 120px; height: 120px; object-fit: cover; cursor: pointer; border-color: #c08e5c !important;" 
                                                             onclick="document.getElementById('foto-input').click();"
                                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=c08e5c&color=fff&size=120';">
                                                        <div class="position-absolute bottom-0 end-0 text-white rounded-circle d-flex align-items-center justify-content-center border border-2 border-dark" 
                                                             style="background: var(--gradient-bronze); width: 36px; height: 36px; cursor: pointer; transform: translate(5%, 5%);" 
                                                             onclick="document.getElementById('foto-input').click();" title="Ganti Foto">
                                                            <i class="bi bi-camera-fill"></i>
                                                        </div>
                                                    </div>
                                                    <input type="file" id="foto-input" name="foto" class="d-none" accept="image/*" onchange="previewFoto(event)">
                                                    <small class="text-secondary d-block mt-3">Klik ikon kamera untuk mengganti foto (JPG/PNG). Maksimal 2MB.</small>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label text-secondary small fw-bold text-uppercase">Nama Lengkap</label>
                                                    <input type="text" name="name" class="form-control form-control-lg " value="{{ $user->name }}" required>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label text-secondary small fw-bold text-uppercase">Email</label>
                                                    <input type="email" name="email" class="form-control form-control-lg " value="{{ $user->email }}" required>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label text-secondary small fw-bold text-uppercase">Nomor HP</label>
                                                    <div class="input-group input-group-lg">
                                                        <span class="input-group-text  border-end-0"><i class="bi bi-telephone text-secondary"></i></span>
                                                        <input type="text" name="no_hp" class="form-control  border-start-0" value="{{ $user->no_hp }}" placeholder="08xxxxxxxx">
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-auth-primary btn-touch w-100 fw-bold rounded-pill mb-4 shadow-sm" style="background: var(--gradient-bronze); color: white; border: none;">
                                                    Simpan Perubahan
                                                </button>
                                            </form>
