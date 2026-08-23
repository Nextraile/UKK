<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation request for updating existing kost.
 *
 * Admin can only update kosts in draft or rejected status (enforced by policy).
 */
class UpdateKostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $kostId = $this->route('kost')->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('kosts', 'slug')->ignore($kostId)],
            'description' => ['nullable', 'string'],
            'contact_number' => ['required', 'string', 'max:20'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string', 'max:255'],
            'rules' => ['nullable', 'array'],
            'rules.*' => ['string', 'max:255'],
            'qris_image_path' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder_name' => ['nullable', 'string', 'max:150'],
            'status' => ['prohibited'], // FR-023: Status cannot be manually changed
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
            'name' => 'nama kost',
            'contact_number' => 'nomor kontak',
            'facilities.*' => 'fasilitas',
            'rules.*' => 'peraturan',
            'qris_image_path' => 'gambar QRIS',
            'bank_name' => 'nama bank',
            'account_number' => 'nomor rekening',
            'account_holder_name' => 'nama pemilik rekening',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.prohibited' => 'Status tidak boleh diubah secara manual. Gunakan workflow yang tersedia.',
        ];
    }
}
