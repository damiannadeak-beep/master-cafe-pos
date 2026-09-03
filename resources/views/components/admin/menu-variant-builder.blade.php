<!-- VARIAN & TOPING -->
                <h5 class="fw-bold mb-3"><i class="bi bi-tags text-success me-2"></i>Varian & Toping (Add-ons)</h5>
                <p class="text-white-50 small mb-3">Atur pilihan seperti "Level Pedas" atau tambahan "Toping" yang memiliki harga tersendiri.</p>
                
                <input type="hidden" name="variants_json" id="variants_json_input" value="{{ is_array(old('variants_json', $menu->variants_json)) ? json_encode(old('variants_json', $menu->variants_json)) : (old('variants_json', $menu->variants_json) ?: '[]') }}">
                
                <div id="variants-container"></div>
                
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-success" id="add-variant-group">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Grup Kosong
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addPresetVariant('saus')">
                        <i class="bi bi-magic me-1"></i> Preset Pilihan Saus
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addPresetVariant('pedas')">
                        <i class="bi bi-fire me-1"></i> Preset Level Pedas
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addPresetVariant('suhu')">
                        <i class="bi bi-thermometer-half me-1"></i> Preset Suhu (Es/Hot)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addPresetVariant('toping')">
                        <i class="bi bi-egg-fried me-1"></i> Preset Toping Extra
                    </button>
                </div>
                <hr class="my-4">


                <div class="mb-3">
                    <label class="form-label">Gambar Produk</label>
                    @if($menu->image_url)
                        <div class="mb-2"><img src="{{ $menu->image_url }}" onerror="this.onerror=null; this.src='/storage/placeholder.svg';" alt="gambar" style="max-width:120px; height:auto;"></div>
                    @endif
                    <input type="file" name="image" accept="image/*" class="form-control">
                </div>

                <button class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>