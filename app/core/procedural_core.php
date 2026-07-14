<?php
require_once __DIR__ . '/SessionManager.php';

function proteger(array $rolesAutorises): void
{
    if (!getData('utilisateur')) {
        rediriger('/login');
    }
    $utilisateur = getData('utilisateur');
    if (!in_array($utilisateur['role'], $rolesAutorises, true)) {
        http_response_code(403);
        exit('Accès refusé pour votre profil.');
    }
}

function requireCsrf(): void
{
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Requête invalide (token de sécurité manquant ou expiré).');
    }
}

function setFlash(string $type, string $message): void
{
    save('flash_' . $type, $message);
}

function getFlash(string $type): ?string
{
    $cle = 'flash_' . $type;
    $message = getData($cle);
    removeData($cle);
    return $message;
}

function render(string $vue, array $donnees = []): void
{
    $utilisateur = getData('utilisateur');
    $flashSucces = getFlash('succes');
    $flashErreur = getFlash('erreur');

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

function rediriger(string $chemin): void
{
    header('Location: ' . $chemin);
    exit;
}

function nettoyer(?string $valeur): string
{
    return htmlspecialchars(trim($valeur ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirigerVersEspace(string $role): void
{
    switch ($role) {
        case 'gerant':
            rediriger('/gerant/dashboard');
            break;
        case 'coach':
            rediriger('/coach/dashboard');
            break;
        default:
            rediriger('/apprenant/dashboard');
            break;
    }
}
