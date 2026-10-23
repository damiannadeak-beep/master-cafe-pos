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
            'metode' => 'required|in:cash,qris,split',
            'email_pelanggan' => 'nullable|email',
            'nominal_tunai' => 'nullable|numeric|min:0',
            'nominal_nontunai' => 'nullable|numeric|min:0',
            'catatan_split' => 'nullable|string',
            'is_uang_pas' => 'nullable|boolean',
            'order_ids' => 'nullable|array',
            'order_ids.*' => 'integer',
        ];
    }
}
