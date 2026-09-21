<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Rules\SecureFileValidation;
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
            'type' => [
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
            'document' => [
                'required',
                'file',
                'min:1', // Prevent 0-byte files
                new SecureFileValidation('rental_document'),
            ],
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
            'document.required' => 'File dokumen harus diunggah.',
            'document.file' => 'File yang diunggah tidak valid.',
            'document.mimes' => 'File harus berformat JPG, PNG, atau PDF.',
            'document.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
