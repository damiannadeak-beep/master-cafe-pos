<!-- Card Rating & Feedback Tamu (Hanya Tampil Saat Pesanan Selesai / Completed) -->
@if($pesanan->status === 'completed')
<div class="card tracking-card shadow-sm p-4 mb-4 text-center" id="rating-container">
    <h6 class="text-white fw-bold mb-1">
        Bagaimana Pengalaman Anda?
    </h6>
    <p class="text-secondary small mb-3">Beri ulasan untuk membantu Master Cafe selalu melayani lebih baik.</p>

    @if($pesanan->rating)
        <div class="p-3 rounded-3" style="background-color: #0e1217; border: 1px solid #21262d;">
            <div class="mb-2">
                @for($i = 1; $i <= 5; $i++)
                    <i class="bi bi-star-fill text-warning fs-5"></i>
                @endfor
            </div>
            <p class="text-white small mb-1">"{{ $pesanan->rating->komentar ?: 'Layanan sangat memuaskan!' }}"</p>
            <small class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Terima kasih atas ulasan Anda!</small>
        </div>
    @else
        <form id="formRating" onsubmit="submitRating(event)">
            @csrf
            <input type="hidden" name="id_pesanan" value="{{ $pesanan->id }}">
            <input type="hidden" name="order_token" value="{{ $pesanan->order_token }}">
            <input type="hidden" name="rating" id="ratingValue" value="5">

            <div class="d-flex justify-content-center gap-2 mb-3" id="starGroup">
                <i class="bi bi-star-fill rating-star active" data-val="1" onclick="setRating(1)"></i>
                <i class="bi bi-star-fill rating-star active" data-val="2" onclick="setRating(2)"></i>
                <i class="bi bi-star-fill rating-star active" data-val="3" onclick="setRating(3)"></i>
                <i class="bi bi-star-fill rating-star active" data-val="4" onclick="setRating(4)"></i>
                <i class="bi bi-star-fill rating-star active" data-val="5" onclick="setRating(5)"></i>
            </div>

            <div class="mb-3">
                <textarea name="komentar" id="ratingComment" class="form-control rounded-3 text-white" 
                          placeholder="Tulis kesan atau saran Anda (opsional)..." rows="2" 
                          style="background-color: #0e1217; border: 1px solid #30363d; font-size: 0.9rem;"></textarea>
            </div>

            <div class="d-flex justify-content-center gap-2 align-items-center">
                <button type="submit" id="btnSubmitRating" class="btn btn-sm rounded-pill px-4 fw-bold shadow-sm" 
                        style="background: var(--gradient-bronze); color: white; border: none;">
                    Kirim Ulasan <i class="bi bi-send-fill ms-1"></i>
                </button>
                <button type="button" onclick="finishCustomerSession()" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                    Nanti Saja / Selesai
                </button>
            </div>
        </form>
    @endif
</div>
@endif
