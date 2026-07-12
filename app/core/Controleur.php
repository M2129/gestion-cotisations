<?php
require_once __DIR__ . '/SessionManager.php';

/**
 * Controleur
 * Classe de base : contrôle d'accès par rôle, gestion centralisée des
 * messages flash et du CSRF, rendu des vues.
 * Toute cette logique est écrite une seule fois ici et réutilisée par
 * tous les contrôleurs (Gérant, Apprenant, Coach) — principe DRY.
 */
abstract class Controleur
{
    /** Bloque l'accès si l'utilisateur n'est pas connecté ou n'a pas le bon rôle */
    protected function proteger(array $rolesAutorises): void
    {
        if (!SessionManager::has('utilisateur')) {
            $this->rediriger('/login');
        }
        $utilisateur = SessionManager::get('utilisateur');
        if (!in_array($utilisateur['role'], $rolesAutorises, true)) {
            http_response_code(403);
            exit('Accès refusé pour votre profil.');
        }
    }

    /** Vérifie le token CSRF d'une requête POST ; arrête l'exécution si invalide */
    protected function requireCsrf(): void
    {
        if (!SessionManager::verifierTokenCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            exit('Requête invalide (token de sécurité manquant ou expiré).');
        }
    }

    /** Enregistre un message flash (affiché automatiquement à la prochaine vue) */
    protected function setFlash(string $type, string $message): void
    {
        SessionManager::set('flash_' . $type, $message);
    }

    private function consommerFlash(string $type): ?string
    {
        $cle = 'flash_' . $type;
        $message = SessionManager::get($cle);
        SessionManager::remove($cle);
        return $message;
    }

    protected function render(string $vue, array $donnees = []): void
    {
        $utilisateur = SessionManager::get('utilisateur');
        $flashSucces = $this->consommerFlash('succes');
        $flashErreur = $this->consommerFlash('erreur');

        extract($donnees, EXTR_SKIP);
        $cheminVue = __DIR__ . '/../views/' . $vue . '.php';

        require __DIR__ . '/../views/layout/header.php';
        if (file_exists($cheminVue)) {
            require $cheminVue;
        } else {
            echo "Vue introuvable : {$vue}";
        }
        require __DIR__ . '/../views/layout/footer.php';
    }

    protected function rediriger(string $chemin): void
    {
        header('Location: ' . $chemin);
        exit;
    }

    /** Nettoie une entrée POST/GET contre les failles XSS */
    protected function nettoyer(?string $valeur): string
    {
        return htmlspecialchars(trim($valeur ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
