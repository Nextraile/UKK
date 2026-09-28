<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for updating image sort order for a kost.
 *
 * Validates array of image IDs that exist in the database.
 */
class UpdateImageSortOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image_ids' => ['required', 'array'],
            'image_ids.*' => ['required', 'integer', 'exists:kost_images,id'],
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
            'image_ids.required' => 'Data urutan gambar tidak ditemukan.',
            'image_ids.array' => 'Data urutan gambar harus berupa array.',
            'image_ids.*.exists' => 'Gambar tidak ditemukan.',
        ];
    }
}
