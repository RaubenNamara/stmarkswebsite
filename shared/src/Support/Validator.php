<?php

declare(strict_types=1);

namespace StMarks\Shared\Support;

/**
 * The single validation mechanism for the app (eSpace has two redundant ones -
 * Controller::validateRequired() and Service::validate() - this replaces both). Rules are given
 * per field as an array or pipe/comma-agnostic array of rule strings, e.g.:
 *   Validator::validate($data, ['email' => ['required', 'email'], 'age' => ['required', 'integer']])
 * Returns a field => message error map; empty array means valid.
 */
class Validator
{
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $fieldRules = is_array($fieldRules) ? $fieldRules : [$fieldRules];
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $error = self::applyRule($field, $value, $rule, $data);
                if ($error !== null) {
                    $errors[$field] = $error;
                    break; // one error per field is enough
                }
            }
        }

        return $errors;
    }

    private static function applyRule(string $field, mixed $value, string $rule, array $data): ?string
    {
        $param = null;
        if (str_contains($rule, ':')) {
            [$rule, $param] = explode(':', $rule, 2);
        }

        return match ($rule) {
            'required' => self::isEmpty($value) ? "The {$field} field is required" : null,
            'email' => (!self::isEmpty($value) && filter_var($value, FILTER_VALIDATE_EMAIL) === false)
                ? "The {$field} field must be a valid email address" : null,
            'min' => (!self::isEmpty($value) && self::length($value) < (int) $param)
                ? "The {$field} field must be at least {$param} characters" : null,
            'max' => (!self::isEmpty($value) && self::length($value) > (int) $param)
                ? "The {$field} field must not exceed {$param} characters" : null,
            'numeric' => (!self::isEmpty($value) && !is_numeric($value))
                ? "The {$field} field must be numeric" : null,
            'integer' => (!self::isEmpty($value) && filter_var($value, FILTER_VALIDATE_INT) === false)
                ? "The {$field} field must be an integer" : null,
            'string' => (!self::isEmpty($value) && !is_string($value))
                ? "The {$field} field must be a string" : null,
            'array' => (!self::isEmpty($value) && !is_array($value))
                ? "The {$field} field must be an array" : null,
            'boolean' => (!self::isEmpty($value) && !in_array($value, [true, false, 0, 1, '0', '1'], true))
                ? "The {$field} field must be true or false" : null,
            'date' => (!self::isEmpty($value) && strtotime((string) $value) === false)
                ? "The {$field} field must be a valid date" : null,
            'in' => (!self::isEmpty($value) && !in_array((string) $value, explode(',', (string) $param), true))
                ? "The {$field} field must be one of: {$param}" : null,
            'confirmed' => ($value !== ($data["{$field}_confirmation"] ?? null))
                ? "The {$field} confirmation does not match" : null,
            default => null,
        };
    }

    private static function isEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        if (is_string($value)) {
            return trim($value) === '';
        }
        if (is_array($value)) {
            return empty($value);
        }
        return false;
    }

    private static function length(mixed $value): int
    {
        return is_string($value) ? mb_strlen($value) : (is_array($value) ? count($value) : strlen((string) $value));
    }
}
