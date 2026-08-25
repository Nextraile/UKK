<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
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
        $kostId = $this->route('kost')->id;

        return [
            'kost_id' => ['required', 'integer', Rule::exists('kosts', 'id')],
            'room_type_id' => [
                'required',
                'integer',
                Rule::exists('room_types', 'id')->where('kost_id', $kostId),
            ],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('rooms')->where('kost_id', $kostId),
            ],
            'status' => ['required', 'in:available,unavailable'],
            'internal_notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom error messages for validator.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'room_type_id.exists' => 'Room type must belong to this kost.',
            'code.unique' => 'Room code already exists in this kost.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'kost_id' => $this->route('kost')->id,
            'status' => $this->input('status', 'available'),
        ]);
    }
}
