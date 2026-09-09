<?php

declare(strict_types=1);

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'superadmin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^08[0-9]{8,11}$/'],
        ];

        // Email uniqueness: ignore current admin on update
        if ($this->isMethod('POST')) {
            $rules['email'][] = Rule::unique('users', 'email');
            $rules['phone'][] = Rule::unique('users', 'phone');
            $rules['password'] = ['required', 'string', 'min:8'];
        } else {
            $rules['email'][] = Rule::unique('users', 'email')->ignore($this->route('admin'));
            $rules['phone'][] = Rule::unique('users', 'phone')->ignore($this->route('admin'));
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Nama depan wajib diisi.',
            'first_name.max' => 'Nama depan maksimal 100 karakter.',
            'last_name.max' => 'Nama belakang maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar dalam sistem.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon harus format 08******** (10-13 digit).',
            'phone.unique' => 'Nomor telepon sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }
}
