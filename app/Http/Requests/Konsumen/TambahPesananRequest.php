<?php

namespace App\Http\Requests\Konsumen;

use Illuminate\Foundation\Http\FormRequest;

class TambahPesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('guest_phone') && $this->guest_phone !== null) {
            // Bersihkan semua karakter selain angka
            $phone = preg_replace('/[^0-9]/', '', (string)$this->guest_phone);
            $this->merge([
                'guest_phone' => $phone !== '' ? $phone : null,
            ]);
        }
    }

    public function rules(): array
    {
        $isTakeaway = $this->input('tipe_pesanan') === 'takeaway' || empty($this->input('id_meja'));
        $requirePhone = $isTakeaway && !auth()->check();

        return [
            'id_meja' => 'nullable|integer',
            'tipe_pesanan' => 'nullable|in:dine_in,takeaway',
            'guest_name' => 'nullable|string|max:100',
            'guest_phone' => [
                $requirePhone ? 'required' : 'nullable',
                'string',
                'regex:/^(08[0-9]{8,12}|628[0-9]{8,12})$/',
            ],
            'items' => 'required|array',
            'items.*.id_menu' => 'required|exists:menu,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga' => 'nullable|numeric|min:0',
            'items.*.catatan' => 'nullable|string|max:255',
            'items.*.variants' => 'nullable|array',
            'promo_id' => 'nullable|exists:promos,id',
            'user_lat' => 'nullable|numeric',
            'user_lng' => 'nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'guest_phone.required' => 'Nomor WhatsApp wajib diisi untuk pesanan Takeaway (bawa pulang).',
            'guest_phone.regex' => 'Format nomor WhatsApp tidak valid. Masukkan nomor seluler Indonesia yang diawali 08 atau 628 dengan panjang 10 hingga 14 angka (contoh: 081234567890).',
            'items.required' => 'Keranjang pesanan tidak boleh kosong.',
            'items.*.id_menu.required' => 'Menu yang dipilih tidak valid.',
            'items.*.jumlah.min' => 'Jumlah item minimal 1 porsi.',
        ];
    }
}
