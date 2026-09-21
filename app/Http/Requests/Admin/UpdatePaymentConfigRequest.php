<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Rules\SecureFileValidation;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation request for updating kost payment configuration.
 *
 * Admin updates QRIS image and bank account information.
 */
class UpdatePaymentConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller via Policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'qris_image' => [
                'nullable',
                'file',
                'min:1', // Prevent 0-byte files
                new SecureFileValidation('qris'),
            ],
            'bank_name' => ['required_with:account_number', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder_name' => ['required_with:account_number', 'string', 'max:150'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'qris_image' => 'gambar QRIS',
            'bank_name' => 'nama bank',
            'account_number' => 'nomor rekening',
            'account_holder_name' => 'nama pemilik rekening',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bank_name.required_with' => 'Nama bank wajib diisi jika nomor rekening diisi.',
            'account_holder_name.required_with' => 'Nama pemilik rekening wajib diisi jika nomor rekening diisi.',
        ];
    }
}
