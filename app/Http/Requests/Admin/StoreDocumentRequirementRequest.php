<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for creating a new document requirement for a kost.
 *
 * Validates document type uniqueness per kost and requirement status.
 */
class StoreDocumentRequirementRequest extends FormRequest
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
        $kost = $this->route('kost');

        return [
            'document_type' => [
                'required',
                'string',
                Rule::in(array_keys(config('kost.document_types'))),
                Rule::unique('kost_document_requirements')->where('kost_id', $kost->id),
            ],
            'is_required' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
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
            'document_type.required' => 'Jenis dokumen harus dipilih.',
            'document_type.in' => 'Jenis dokumen tidak valid.',
            'document_type.unique' => 'Jenis dokumen ini sudah ditambahkan untuk kost ini.',
            'is_required.required' => 'Status wajib harus dipilih.',
            'is_required.boolean' => 'Status wajib tidak valid.',
            'reason.max' => 'Alasan maksimal 500 karakter.',
        ];
    }
}
