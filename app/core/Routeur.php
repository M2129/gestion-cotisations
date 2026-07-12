<?php
/**
 * Routeur
 * Fait correspondre les URLs propres /{acteur}/{ressource}/{action}
 * à un [Controleur, methode].
 */
class Routeur
{
    private array $routes = [];

    public function ajouter(string $methodeHttp, string $chemin, string $controleur, string $methode): void
    {
        $this->routes[] = [
            'methodeHttp' => strtoupper($methodeHttp),
            'chemin'      => trim($chemin, '/'),
            'controleur'  => $controleur,
            'methode'     => $methode,
        ];
    }

    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // Retire le sous-dossier éventuel (ex: /cotisations/public/gerant/dashboard)
        $base = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        $chemin = trim(substr($uri, strlen($base)), '/');
        if ($chemin === '') {
            $chemin = 'login';
        }

        $methodeHttp = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route['chemin'] === $chemin && $route['methodeHttp'] === $methodeHttp) {
                $this->appeler($route['controleur'], $route['methode']);
                return;
            }
        }

        http_response_code(404);
        echo "Page introuvable : /{$chemin}";
    }

    private function appeler(string $nomControleur, string $methode): void
    {
        $fichier = __DIR__ . '/../controllers/' . $nomControleur . '.php';
        if (!file_exists($fichier)) {
            http_response_code(500);
            echo "Contrôleur introuvable : {$nomControleur}";
            return;
        }
        require_once $fichier;
        $controleur = new $nomControleur();
        $controleur->$methode();
    }
}
