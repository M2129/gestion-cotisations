<?php
/**
 * SessionManager
 * Abstrait tous les accès à $_SESSION pour sécuriser et centraliser
 * la persistance des données (aucune base de données dans ce projet).
 */
class SessionManager
{
    public static function demarrer(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Cookie de session durci : inaccessible en JS, non transmis en cross-site
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            ]);
            session_start();
        }
    }

    /** Régénère l'identifiant de session (à appeler après une connexion réussie) */
    public static function regenererId(): void
    {
        session_regenerate_id(true);
    }

    public static function get(string $cle, $defaut = null)
    {
        return $_SESSION[$cle] ?? $defaut;
    }

    public static function set(string $cle, $valeur): void
    {
        $_SESSION[$cle] = $valeur;
    }

    public static function has(string $cle): bool
    {
        return isset($_SESSION[$cle]);
    }

    public static function remove(string $cle): void
    {
        unset($_SESSION[$cle]);
    }

    /** Ajoute un élément à un tableau stocké en session */
    public static function push(string $cle, $valeur): void
    {
        if (!isset($_SESSION[$cle]) || !is_array($_SESSION[$cle])) {
            $_SESSION[$cle] = [];
        }
        $_SESSION[$cle][] = $valeur;
    }

    /** Génère et stocke un token CSRF pour un formulaire */
    public static function genererTokenCsrf(): string
    {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('csrf_token');
    }

    public static function verifierTokenCsrf(?string $token): bool
    {
        return $token !== null
            && self::has('csrf_token')
            && hash_equals(self::get('csrf_token'), $token);
    }

    /** Alias public pour démarrer la session. */
    public static function startSession(): void
    {
        self::demarrer();
    }

    /** Alias public pour sauvegarder une donnée en session. */
    public static function save(string $key, $data): void
    {
        self::set($key, $data);
    }

    /** Alias public pour récupérer une donnée en session. */
    public static function getData(string $key, $default = null)
    {
        return self::get($key, $default);
    }

    /** Alias public pour supprimer une donnée en session. */
    public static function removeData(string $key): void
    {
        self::remove($key);
    }

    public static function detruire(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
