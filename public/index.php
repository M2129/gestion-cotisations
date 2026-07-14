<?php
require_once __DIR__ . '/../app/core/SessionManager.php';
require_once __DIR__ . '/../app/core/procedural_core.php';
require_once __DIR__ . '/../app/controllers/auth.php';
require_once __DIR__ . '/../app/controllers/gerant.php';

startSession();

$base = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$chemin = trim(substr($uri, strlen($base)), '/');
if ($chemin === '') {
    $chemin = 'login';
}

$methodeHttp = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        'login'                   => 'afficherLogin',
        'inscription'             => 'afficherInscription',
        'logout'                  => 'deconnexion',
        'gerant/dashboard'        => 'dashboard',
        'gerant/paiements/create' => 'afficherFormulairePaiement',
        'gerant/campagnes/create' => 'afficherFormulaireCampagne',
        'gerant/apprenants'       => 'apprenants',
    ],
    'POST' => [
        'login'                   => 'traiterLogin',
        'inscription'             => 'traiterInscription',
        'gerant/paiements/create' => 'enregistrerPaiement',
        'gerant/campagnes/create' => 'enregistrerCampagne',
        'gerant/apprenants'       => 'apprenants',
    ],
];

if (!isset($routes[$methodeHttp][$chemin])) {
    http_response_code(404);
    echo "Page introuvable : /{$chemin}";
    exit;
}

$action = $routes[$methodeHttp][$chemin];
if (!function_exists($action)) {
    http_response_code(500);
    echo "Action introuvable : {$action}";
    exit;
}

$action();
