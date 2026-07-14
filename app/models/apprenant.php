<?php
require_once __DIR__ . '/../core/SessionManager.php';

function apprenant_initialiserDonneesDemo(): void
{
    if (hasData('apprenants')) {
        return;
    }

    save('apprenants', [
        ['id' => 1, 'prenom' => 'Mamadou', 'nom' => 'Ndiaye', 'email' => 'mamadou@odc.sn', 'motDePasse' => password_hash('gerant123', PASSWORD_DEFAULT), 'role' => 'gerant', 'dateInscription' => '2026-01-05', 'actif' => true],
        ['id' => 2, 'prenom' => 'Fatou', 'nom' => 'Diop', 'email' => 'coach@odc.sn', 'motDePasse' => password_hash('coach123', PASSWORD_DEFAULT), 'role' => 'coach', 'dateInscription' => '2026-01-05', 'actif' => true],
        ['id' => 3, 'prenom' => 'Ismaïla', 'nom' => 'Sarr', 'email' => 'ismaila@odc.sn', 'motDePasse' => password_hash('apprenant123', PASSWORD_DEFAULT), 'role' => 'apprenant', 'dateInscription' => '2026-01-05', 'actif' => true],
        ['id' => 4, 'prenom' => 'Aissatou', 'nom' => 'Ba', 'email' => 'aissatou@odc.sn', 'motDePasse' => password_hash('apprenant123', PASSWORD_DEFAULT), 'role' => 'apprenant', 'dateInscription' => '2026-01-05', 'actif' => true],
        ['id' => 5, 'prenom' => 'Sadio', 'nom' => 'Diallo', 'email' => 'sadio@odc.sn', 'motDePasse' => password_hash('apprenant123', PASSWORD_DEFAULT), 'role' => 'apprenant', 'dateInscription' => '2026-01-05', 'actif' => true],
    ]);
}

function apprenant_getTous(): array
{
    return getData('apprenants', []);
}

function apprenant_getParId(int $id): ?array
{
    foreach (apprenant_getTous() as $item) {
        if ($item['id'] === $id) {
            return $item;
        }
    }
    return null;
}

function apprenant_getParEmail(string $email): ?array
{
    foreach (apprenant_getTous() as $a) {
        if (strtolower($a['email']) === strtolower($email)) {
            return $a;
        }
    }
    return null;
}

function apprenant_getParRole(string $role): array
{
    return array_values(array_filter(apprenant_getTous(), fn($a) => $a['role'] === $role));
}

function apprenant_prochainId(): int
{
    $collection = apprenant_getTous();
    return empty($collection) ? 1 : max(array_column($collection, 'id')) + 1;
}

function apprenant_sauvegarderTous(array $collection): void
{
    save('apprenants', $collection);
}

function apprenant_creer(string $prenom, string $nom, string $email, string $motDePasse, string $role = 'apprenant'): array
{
    $item = [
        'id' => apprenant_prochainId(),
        'prenom' => htmlspecialchars(trim($prenom), ENT_QUOTES, 'UTF-8'),
        'nom' => htmlspecialchars(trim($nom), ENT_QUOTES, 'UTF-8'),
        'email' => filter_var($email, FILTER_SANITIZE_EMAIL),
        'motDePasse' => password_hash($motDePasse, PASSWORD_DEFAULT),
        'role' => $role,
        'dateInscription' => date('Y-m-d'),
        'actif' => true,
    ];
    $collection = apprenant_getTous();
    $collection[] = $item;
    apprenant_sauvegarderTous($collection);
    return $item;
}

function apprenant_supprimer(int $id): void
{
    $restants = array_values(array_filter(
        apprenant_getTous(),
        fn($item) => $item['id'] !== $id
    ));
    apprenant_sauvegarderTous($restants);
}

function apprenant_importerDepuisCsv(array $lignes): int
{
    $compteur = 0;
    foreach ($lignes as $ligne) {
        if (empty($ligne['email']) || apprenant_getParEmail($ligne['email'])) {
            continue;
        }
        $motDePasseTemporaire = substr(bin2hex(random_bytes(4)), 0, 8);
        apprenant_creer($ligne['prenom'] ?? '', $ligne['nom'] ?? '', $ligne['email'], $motDePasseTemporaire, 'apprenant');
        $compteur++;
    }
    return $compteur;
}
