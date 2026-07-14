<?php
/**
 * Validator
 * Règles de validation réutilisables pour les formulaires et les imports.
 */
class Validator
{
    public static function required($value): bool
    {
        return trim((string) ($value ?? '')) !== '';
    }

    public static function unique(string $value, callable $existsCallback): bool
    {
        return !$existsCallback($value);
    }

    public static function isEmail(string $value): bool
    {
        return filter_var(trim($value), FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isPassword(string $password, int $minLength = 8): bool
    {
        return is_string($password) && mb_strlen($password, 'UTF-8') >= $minLength;
    }
}
