<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for validating kost_id parameter.
 *
 * Used in rental creation and other tenant operations requiring kost selection.
 */
class ValidateKostIdRequest extends FormRequest
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
        return [
            'kost_id' => 'required|exists:kosts,id',
        ];
    }
}
