<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Rules\SecureFileValidation;
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
            'proof' => ['required', new SecureFileValidation('payment_proof')],
        ];
    }

    public function messages(): array
    {
        return [
            'proof.required' => 'Bukti pembayaran wajib diupload.',
        ];
    }
}
