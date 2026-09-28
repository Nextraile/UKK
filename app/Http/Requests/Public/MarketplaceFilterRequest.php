<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for filtering marketplace listings.
 *
 * Handles search, price range, categories, and rating filters.
 */
class MarketplaceFilterRequest extends FormRequest
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
            'search' => 'nullable|string|max:255',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gte:price_min',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'rating_min' => 'nullable|numeric|min:1|max:5',
        ];
    }
}
