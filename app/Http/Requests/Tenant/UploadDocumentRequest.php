<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $rental = $this->route('rental');

        return $this->user()->can('uploadDocument', $rental);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document_type' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $rental = $this->route('rental');
                    $requiredDocs = $rental->room->roomType->kost->documentRequirements()
                        ->where('is_required', true)
                        ->pluck('document_type')
                        ->toArray();

                    if (! in_array($value, $requiredDocs)) {
                        $fail("Tipe dokumen {$value} tidak diperlukan untuk kost ini.");
                    }
                },
            ],
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
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
            'file.required' => 'File dokumen harus diunggah.',
            'file.file' => 'File yang diunggah tidak valid.',
            'file.mimes' => 'File harus berformat JPG, PNG, atau PDF.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
