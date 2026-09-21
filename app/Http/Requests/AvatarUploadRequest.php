<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\SecureFileValidation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AvatarUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'file',
                'min:1', // Prevent 0-byte files
                new SecureFileValidation('avatar'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avatar.required' => 'File avatar wajib diupload.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.mimes' => 'Format avatar harus JPEG, PNG, atau WebP.',
            'avatar.max' => 'Ukuran avatar maksimal 2MB.',
        ];
    }
}
