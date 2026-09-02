<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->hasAnyRole(['pemilik', 'admin', 'adminpemilik']) || auth()->user()->hasRole('pemilik'));
    }

    public function rules(): array
    {
        return [
            'nama_menu' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'kategori' => 'required|string|max:100',
            'is_available' => 'nullable|boolean',
            'is_dynamic_price' => 'nullable|boolean',
            'deskripsi' => 'nullable|string',
            'variants_json' => 'nullable|json',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6000',
        ];
    }
}
