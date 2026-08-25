<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePriceSchemeRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_value' => ['required', 'integer', 'min:1'],
            'duration_unit' => ['required', 'in:day,week,month'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama paket harga harus diisi.',
            'name.max' => 'Nama paket harga maksimal 100 karakter.',
            'price.required' => 'Harga harus diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga minimal 0.',
            'duration_value.required' => 'Durasi harus diisi.',
            'duration_value.integer' => 'Durasi harus berupa angka bulat.',
            'duration_value.min' => 'Durasi minimal 1.',
            'duration_unit.required' => 'Satuan durasi harus dipilih.',
            'duration_unit.in' => 'Satuan durasi tidak valid.',
        ];
    }
}
