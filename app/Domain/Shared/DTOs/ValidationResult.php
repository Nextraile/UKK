<?php

declare(strict_types=1);

namespace App\Domain\Shared\DTOs;

/**
 * Data Transfer Object for file validation results.
 *
 * This immutable DTO encapsulates the outcome of file validation,
 * providing a clean interface to check validity and retrieve error messages.
 */
final readonly class ValidationResult
{
    /**
     * Create a new validation result instance.
     *
     * @param  bool  $isValid  Whether the file passed all validation checks
     * @param  array<int, string>  $errors  List of validation error messages (empty if valid)
     */
    public function __construct(
        private bool $isValid,
        private array $errors = []
    ) {}

    /**
     * Check if the validation passed.
     *
     * @return bool True when the file is valid and safe to store
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Get all validation errors.
     *
     * @return array<int, string> Array of error messages (empty if valid)
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the first validation error message.
     *
     * Useful for displaying a single error message to users.
     *
     * @return string|null The first error message, or null if valid
     */
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Create a successful validation result.
     */
    public static function success(): self
    {
        return new self(true, []);
    }

    /**
     * Create a failed validation result.
     *
     * @param  array<int, string>  $errors  List of validation errors
     */
    public static function failure(array $errors): self
    {
        return new self(false, $errors);
    }
}
