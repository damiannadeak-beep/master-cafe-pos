<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class VoidOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['kasir', 'pemilik']);
    }

    public function rules(): array
    {
        return [
            'alasan' => 'required|string|max:255',
            'password' => 'required|string',
        ];
    }
}
