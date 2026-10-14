<?php
declare(strict_types=1);

namespace App;

final class Validator
{
    private array $errors = [];

    public function required(string $field, mixed $value, string $label): self
    {
        if ($value === null || trim((string)$value) === '') {
            $this->errors[$field] = "$label is required.";
        }
        return $this;
    }

    public function email(string $field, ?string $value): self
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Invalid email address.';
        }
        return $this;
    }

    /**
     * Validate an Indian mobile number.
     * Accepts: 10 digits | +91XXXXXXXXXX | 91XXXXXXXXXX | 0XXXXXXXXXX (with optional spaces/dashes).
     * Must start with 6, 7, 8, or 9 (standard Indian mobile prefix).
     */
    public function phone(string $field, ?string $value): self
    {
        if ($value === null || $value === '') return $this;

        // Strip everything except digits
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        // Strip leading +91, 91, or 0
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // Now must be exactly 10 digits, starting with 6-9
        if (!preg_match('/^[6-9][0-9]{9}$/', $digits)) {
            $this->errors[$field] = 'Phone must be a 10-digit Indian mobile number.';
        }
        return $this;
    }

    public function password(string $field, ?string $value, int $min = 8): self
    {
        if ($value === null || strlen($value) < $min) {
            $this->errors[$field] = "Password must be at least $min characters.";
        } elseif (!preg_match('/[A-Za-z]/', $value) || !preg_match('/[0-9]/', $value)) {
            $this->errors[$field] = 'Password must contain letters and numbers.';
        }
        return $this;
    }

    public function matches(string $field, ?string $value, ?string $other): self
    {
        if ($value !== $other) {
            $this->errors[$field] = 'Passwords do not match.';
        }
        return $this;
    }

    public function minLength(string $field, ?string $value, int $min, string $label): self
    {
        if ($value !== null && mb_strlen(trim($value)) < $min) {
            $this->errors[$field] = "$label must be at least $min characters.";
        }
        return $this;
    }

    public function in(string $field, mixed $value, array $allowed, string $label): self
    {
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = "Invalid $label.";
        }
        return $this;
    }

    public function fails(): bool { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
}