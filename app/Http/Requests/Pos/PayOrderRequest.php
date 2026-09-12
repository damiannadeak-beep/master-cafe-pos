<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class PayOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metode' => 'required|in:cash,qris',
            'email_pelanggan' => 'nullable|email',
            'nominal_tunai' => 'nullable|numeric|min:0',
            'is_uang_pas' => 'nullable|boolean',
        ];
    }
}
