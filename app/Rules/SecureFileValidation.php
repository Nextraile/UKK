<?php

declare(strict_types=1);

namespace App\Rules;

use App\Domain\Shared\Services\SecureFileUploadService;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

/**
 * Custom validation rule for secure file uploads.
 *
 * This rule integrates SecureFileUploadService validation into Laravel's
 * validation system, allowing usage in Form Request classes:
 *
 * Example:
 * 'avatar' => ['required', new SecureFileValidation('avatar')],
 */
class SecureFileValidation implements Rule
{
    /**
     * Validation errors collected during validation.
     *
     * @var array<int, string>
     */
    private array $errors = [];

    /**
     * Create a new rule instance.
     *
     * @param  string  $uploadType  The upload type key from config/secure-uploads.php
     */
    public function __construct(private string $uploadType) {}

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute  The attribute name being validated
     * @param  mixed  $value  The value to validate
     * @return bool True if validation passes, false otherwise
     */
    public function passes($attribute, $value): bool
    {
        if (! $value instanceof UploadedFile) {
            $this->errors[] = 'File harus berupa upload yang valid.';

            return false;
        }

        $service = app(SecureFileUploadService::class);
        $result = $service->validate($value, $this->uploadType);

        if (! $result->isValid()) {
            $this->errors = $result->getErrors();

            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string The error message(s)
     */
    public function message(): string
    {
        return implode(' ', $this->errors);
    }
}
