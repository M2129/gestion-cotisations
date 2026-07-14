<?php
/**
 * SessionManager
 * Procedural storage API for session data.
 */

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        ]);
        session_start();
    }
}

function regenerateSessionId(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function save(string $key, $value): void
{
    $_SESSION[$key] = $value;
}

function getData(string $key, $default = null)
{
    return $_SESSION[$key] ?? $default;
}

function hasData(string $key): bool
{
    return isset($_SESSION[$key]);
}

function removeData(string $key): void
{
    unset($_SESSION[$key]);
}

function pushData(string $key, $value): void
{
    if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }
    $_SESSION[$key][] = $value;
}

function destroySession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_destroy();
    }
}

function generateCsrfToken(): string
{
    if (!hasData('csrf_token')) {
        save('csrf_token', bin2hex(random_bytes(32)));
    }
    return getData('csrf_token');
}

function verifyCsrfToken(?string $token): bool
{
    return $token !== null && hasData('csrf_token') && hash_equals(getData('csrf_token'), $token);
}
