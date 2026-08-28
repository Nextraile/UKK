<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'kost_rating' => [
                'nullable',
                'integer',
                'between:1,5',
                'required_without:room_rating',
            ],
            'kost_comment' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'room_rating' => [
                'nullable',
                'integer',
                'between:1,5',
                'required_without:kost_rating',
            ],
            'room_comment' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'images' => [
                'nullable',
                'array',
                'max:5',
            ],
            'images.*' => [
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048', // 2MB
            ],
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kost_rating.required_without' => 'Minimal salah satu rating harus diisi (Kost atau Kamar).',
            'kost_rating.between' => 'Rating kost harus antara 1-5.',
            'room_rating.required_without' => 'Minimal salah satu rating harus diisi (Kost atau Kamar).',
            'room_rating.between' => 'Rating kamar harus antara 1-5.',
            'kost_comment.max' => 'Komentar kost maksimal 2000 karakter.',
            'room_comment.max' => 'Komentar kamar maksimal 2000 karakter.',
            'images.max' => 'Maksimal 5 gambar.',
            'images.*.image' => 'File harus berupa gambar.',
            'images.*.mimes' => 'Gambar harus berformat JPEG, PNG, atau JPG.',
            'images.*.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
