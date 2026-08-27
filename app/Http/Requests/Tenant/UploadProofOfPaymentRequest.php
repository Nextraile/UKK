<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UploadProofOfPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'proof' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'], // 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'proof.required' => 'Bukti pembayaran wajib diupload.',
            'proof.image' => 'File harus berupa gambar.',
            'proof.mimes' => 'Format file harus jpeg, png, atau jpg.',
            'proof.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
