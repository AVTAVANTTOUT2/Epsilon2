<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string ...$fields): self
    {
        foreach ($fields as $field) {
            $value = $this->data[$field] ?? null;
            if ($value === null || $value === '') {
                $this->errors[$field] = "Le champ {$field} est requis.";
            }
        }
        return $this;
    }

    public function email(string $field): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Adresse email invalide.';
        }
        return $this;
    }

    public function minLength(string $field, int $min): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && mb_strlen($value) < $min) {
            $this->errors[$field] = "Le champ {$field} doit contenir au moins {$min} caractères.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && mb_strlen($value) > $max) {
            $this->errors[$field] = "Le champ {$field} ne doit pas dépasser {$max} caractères.";
        }
        return $this;
    }

    public function integer(string $field): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field] = "Le champ {$field} doit être un nombre entier.";
        }
        return $this;
    }

    public function range(string $field, int $min, int $max): self
    {
        $value = (int)($this->data[$field] ?? 0);
        if ($value < $min || $value > $max) {
            $this->errors[$field] = "Le champ {$field} doit être entre {$min} et {$max}.";
        }
        return $this;
    }

    public function matches(string $field, string $matchField, string $label): self
    {
        $value = $this->data[$field] ?? '';
        $match = $this->data[$matchField] ?? '';
        if ($value !== $match) {
            $this->errors[$field] = "Les champs {$field} et {$label} ne correspondent pas.";
        }
        return $this;
    }

    public function inArray(string $field, array $allowed): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field] = "La valeur du champ {$field} n'est pas autorisée.";
        }
        return $this;
    }

    public function custom(string $field, callable $rule, string $message): self
    {
        $value = $this->data[$field] ?? null;
        if (!$rule($value)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function passes(): bool
    {
        return count($this->errors) === 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function error(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    public function validate(): array
    {
        if (!$this->passes()) {
            throw new ValidationException($this->errors, $this->data);
        }
        return $this->data;
    }
}

final class ValidationException extends \RuntimeException
{
    private array $errors;
    private array $old;

    public function __construct(array $errors, array $old)
    {
        parent::__construct('Validation échouée.');
        $this->errors = $errors;
        $this->old = $old;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getOld(): array
    {
        return $this->old;
    }
}
