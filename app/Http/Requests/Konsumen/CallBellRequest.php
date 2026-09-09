<?php

namespace App\Http\Requests\Konsumen;

use Illuminate\Foundation\Http\FormRequest;

class CallBellRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_meja' => 'required|exists:meja,id',
        ];
    }
}
