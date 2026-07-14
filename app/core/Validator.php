<?php
/**
 * Validator
 * Règles de validation réutilisables pour les formulaires et les imports.
 */

function required($value): bool
{
    return trim((string) ($value ?? '')) !== '';
}

function unique(string $value, callable $existsCallback): bool
{
    return !$existsCallback($value);
}

function isEmail(string $value): bool
{
    return filter_var(trim($value), FILTER_VALIDATE_EMAIL) !== false;
}

function isPassword(string $password, int $minLength = 8): bool
{
    return is_string($password) && mb_strlen($password, 'UTF-8') >= $minLength;
}
