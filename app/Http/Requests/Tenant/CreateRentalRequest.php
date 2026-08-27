<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateRentalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasVerifiedEmail();
    }

    /**
     * Prepare data for validation.
     *
     * Cast duration to int (HTTP input arrives as string, Action expects int).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'duration' => $this->duration ? (int) $this->duration : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * FR-122: start_date min = today+4 days, max = today+30 days
     * FR-065: duration value 1-24 (reasonable range)
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $minStartDate = now()->addDays(4)->format('Y-m-d');
        $maxStartDate = now()->addDays(30)->format('Y-m-d');

        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'price_scheme_id' => ['required', 'exists:price_schemes,id'],
            'start_date' => ['required', 'date', 'after_or_equal:'.$minStartDate, 'before_or_equal:'.$maxStartDate],
            'duration' => ['required', 'integer', 'min:1', 'max:24'],
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
            'start_date.after_or_equal' => 'Tanggal mulai minimal 4 hari dari sekarang (waktu verifikasi payment + dokumen).',
            'start_date.before_or_equal' => 'Tanggal mulai maksimal 30 hari dari sekarang.',
            'duration.max' => 'Durasi maksimal 24 unit.',
        ];
    }

    /**
     * Prepare data for Action class.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Add user_id to validated data
        $validated['user_id'] = auth()->id();

        return $validated;
    }
}
