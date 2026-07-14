<?php
require_once __DIR__ . '/../app/core/SessionManager.php';
require_once __DIR__ . '/../app/core/procedural_core.php';
require_once __DIR__ . '/../app/controllers/auth.php';
require_once __DIR__ . '/../app/controllers/gerant.php';

startSession();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$base = str_replace('index.php', '', $scriptName);
$chemin = trim($uri, '/');

if ($base !== '' && $base !== '/' && strpos($uri, $base) === 0) {
    $chemin = trim(substr($uri, strlen($base)), '/');
} elseif (preg_match('#^/index\.php/(.*)$#', $uri, $matches)) {
    $chemin = trim($matches[1], '/');
}

if ($chemin === '') {
    $chemin = 'login';
}

$methodeHttp = $_SERVER['REQUEST_METHOD'];
if ($methodeHttp === 'HEAD') {
    $methodeHttp = 'GET';
}

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
