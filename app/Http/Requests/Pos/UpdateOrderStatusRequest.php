<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // We assume authentication is handled by middleware
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:processing,completed',
        ];
    }
}
