<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation request for updating room type.
 *
 * Admin updates room types for their own kosts.
 */
class UpdateRoomTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $kostId = $this->route('kost')->id;
        $roomTypeId = $this->route('room_type')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('room_types', 'name')
                    ->where('kost_id', $kostId)
                    ->ignore($roomTypeId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'room_size' => ['required', 'string', 'max:50'],
            'max_occupants' => ['required', 'integer', 'min:1', 'max:10'],
            'security_deposit' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string', 'max:255'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'rules' => ['nullable', 'array'],
            'rules.*' => ['string', 'max:255'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer', 'exists:room_type_images,id'],
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
            'name' => 'nama tipe kamar',
            'description' => 'deskripsi',
            'room_size' => 'ukuran kamar',
            'max_occupants' => 'kapasitas maksimal',
            'security_deposit' => 'deposit keamanan',
            'facilities' => 'fasilitas',
            'facilities.*' => 'fasilitas',
            'images' => 'gambar',
            'images.*' => 'gambar',
            'rules' => 'peraturan',
            'rules.*' => 'peraturan',
            'delete_images' => 'gambar yang dihapus',
            'delete_images.*' => 'gambar',
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
            'name.unique' => 'Nama tipe kamar sudah digunakan untuk kost ini.',
            'images.max' => 'Maksimal 10 gambar dapat diunggah.',
            'images.*.image' => 'File harus berupa gambar.',
            'images.*.mimes' => 'Format gambar harus: JPEG, JPG, PNG, atau WebP.',
            'images.*.max' => 'Ukuran gambar maksimal 5MB per file.',
        ];
    }
}
