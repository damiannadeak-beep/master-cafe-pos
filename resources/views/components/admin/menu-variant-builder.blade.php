<!-- VARIAN & TOPING BUILDER -->
<div class="variant-builder-section">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <span class="p-2 rounded-3 text-warning" style="background: rgba(192, 142, 92, 0.15); border: 1px solid rgba(192, 142, 92, 0.3);">
                    <i class="bi bi-tags-fill"></i>
                </span>
                <span>Varian & Toping (Add-ons)</span>
                <span class="badge rounded-pill text-white fw-normal" style="background: rgba(192, 142, 92, 0.25); border: 1px solid rgba(192, 142, 92, 0.4); font-size: 0.72rem;">Opsional</span>
            </h5>
            <p class="text-white-50 small mb-0">Atur pilihan seperti "Level Pedas", "Pilihan Suhu", atau tambahan "Toping" dengan harga tersendiri.</p>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-primary shadow-sm" id="add-variant-group">
                <i class="bi bi-plus-circle me-1"></i> Tambah Grup Varian
            </button>
        </div>
    </div>

    <!-- Quick Preset Buttons -->
    <div class="p-3 rounded-3 mb-3" style="background: #11141a; border: 1px solid #21262d;">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="small fw-semibold text-white-50"><i class="bi bi-lightning-charge text-warning me-1"></i> Preset Cepat (Klik untuk menambahkan):</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm text-white" style="background: rgba(234, 88, 12, 0.15); border: 1px solid rgba(234, 88, 12, 0.4);" onclick="addPresetVariant('pedas')">
                <i class="bi bi-fire text-danger me-1"></i> Preset Level Pedas
            </button>
            <button type="button" class="btn btn-sm text-white" style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.4);" onclick="addPresetVariant('suhu')">
                <i class="bi bi-thermometer-half text-info me-1"></i> Preset Suhu (Dingin / Hangat)
            </button>
            <button type="button" class="btn btn-sm text-white" style="background: rgba(234, 179, 8, 0.15); border: 1px solid rgba(234, 179, 8, 0.4);" onclick="addPresetVariant('toping')">
                <i class="bi bi-egg-fried text-warning me-1"></i> Preset Toping Extra
            </button>
            <button type="button" class="btn btn-sm text-white" style="background: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.4);" onclick="addPresetVariant('saus')">
                <i class="bi bi-magic text-purple me-1"></i> Preset Pilihan Saus
            </button>
        </div>
    </div>

    <input type="hidden" name="variants_json" id="variants_json_input" value="{{ is_array(old('variants_json', $menu->variants_json)) ? json_encode(old('variants_json', $menu->variants_json)) : (old('variants_json', $menu->variants_json) ?: '[]') }}">

    <!-- Container for dynamic variant cards -->
    <div id="variants-container"></div>
</div>