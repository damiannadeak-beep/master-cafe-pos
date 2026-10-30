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
                function ($attribute, $value, $fail) {
                    if (empty($value)) return;

                    $norm = (string) $value;
                    if (str_starts_with($norm, '628')) {
                        $norm = '0' . substr($norm, 2);
                    }

                    // 1. Validasi Prefix Operator Seluler Indonesia (4 digit awal)
                    $validPrefixes = [
                        // Telkomsel
                        '0811', '0812', '0813', '0821', '0822', '0823', '0851', '0852', '0853',
                        // Indosat Ooredoo
                        '0814', '0815', '0816', '0855', '0856', '0857', '0858',
                        // XL Axiata & Axis
                        '0817', '0818', '0819', '0831', '0832', '0833', '0838', '0859', '0877', '0878',
                        // Tri (3)
                        '0895', '0896', '0897', '0898', '0899',
                        // Smartfren
                        '0881', '0882', '0883', '0884', '0885', '0886', '0887', '0888', '0889',
                    ];

                    $prefix = substr($norm, 0, 4);
                    if (!in_array($prefix, $validPrefixes)) {
                        $fail('Nomor WhatsApp tidak valid. Awalan operator ' . $prefix . ' tidak terdaftar pada operator seluler Indonesia.');
                        return;
                    }

                    // 2. Deteksi Pola Angka Berulang (cth: 081111111111, 082222222222, 089999999999)
                    if (preg_match('/(.)\1{5,}/', $norm)) {
                        $fail('Nomor WhatsApp terdeteksi tidak wajar (mengandung deretan angka kembar berulang).');
                        return;
                    }

                    // 3. Deteksi Pola Angka Berurutan Palsu (cth: 081234567890, 089876543210)
                    $fakeSequences = ['12345678', '87654321', '01234567', '76543210'];
                    foreach ($fakeSequences as $seq) {
                        if (str_contains($norm, $seq)) {
                            $fail('Nomor WhatsApp tidak valid (terdeteksi deretan angka berurutan palsu).');
                            return;
                        }
                    }

                    // 4. Deteksi Variasi Angka Terlalu Sedikit (cth: 081200000000 atau nomor fiktif)
                    $remainingDigits = substr($norm, 4);
                    if (count(array_unique(str_split($remainingDigits))) < 3) {
                        $fail('Nomor WhatsApp tidak valid (kombinasi digit terlalu sedikit / terdeteksi nomor fiktif).');
                        return;
                    }
                },
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
