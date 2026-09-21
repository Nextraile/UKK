<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Rules\SecureFileValidation;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation request for uploading kost images.
 *
 * Admin uploads images for their own kosts.
 */
class StoreKostImageRequest extends FormRequest
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
            'image' => [
                'required',
                'file',
                'min:1', // Prevent 0-byte files
                new SecureFileValidation('kost_image'),
            ],
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
            'image' => 'gambar kost',
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
            'image.required' => 'Gambar wajib diunggah.',
        ];
    }
}
