<?php
/**
 * Validator
 * Lightweight, dependency-free input validation. Collects field-level
 * error messages that views can render next to each form control.
 */
class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $label): self
    {
        if (!isset($this->data[$field]) || trim((string)$this->data[$field]) === '') {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    public function numeric(string $field, string $label): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "{$label} must be a number.";
        }
        return $this;
    }

    public function min(string $field, float $min, string $label): self
    {
        if (isset($this->data[$field]) && is_numeric($this->data[$field]) && (float)$this->data[$field] < $min) {
            $this->errors[$field] = "{$label} must be at least {$min}.";
        }
        return $this;
    }

    public function email(string $field, string $label): self
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} must be a valid email address.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label): self
    {
        $value = (string)($this->data[$field] ?? '');
        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if ($value !== '' && $length > $max) {
            $this->errors[$field] = "{$label} must be {$max} characters or fewer.";
        }
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /** Trim + strip tags from every string value; safe default for text inputs. */
    public static function clean(array $input): array
    {
        $out = [];
        foreach ($input as $key => $value) {
            $out[$key] = is_string($value) ? trim(strip_tags($value)) : $value;
        }
        return $out;
    }

    /** Escape for safe HTML output. Use on every piece of user data printed in views. */
    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
