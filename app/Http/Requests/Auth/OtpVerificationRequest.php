<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validating the submitted OTP code during email verification.
 *
 * Validates that the code is a 6-digit numeric string (FR-004).
 */
class OtpVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Any authenticated user may submit an OTP verification attempt.
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
            'otp_code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules, in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'otp_code.required' => 'Kode OTP wajib diisi.',
            'otp_code.size' => 'Kode OTP harus 6 digit.',
            'otp_code.regex' => 'Kode OTP hanya boleh berisi angka.',
        ];
    }
}
