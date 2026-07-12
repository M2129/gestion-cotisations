<?php
require_once __DIR__ . '/../core/ModeleSession.php';

/**
 * ApprenantModel
 * Lit et écrit exclusivement dans $_SESSION['apprenants'].
 * La logique générique (getTous, getParId, ID auto) vit dans ModeleSession.
 */
class ApprenantModel extends ModeleSession
{
    public function __construct()
    {
        parent::__construct('apprenants');
    }

    public function initialiserDonneesDemo(): void
    {
        if (SessionManager::has($this->cleSession)) {
            return;
        }

        $this->sauvegarderTous([
            ['id' => 1, 'prenom' => 'Mamadou', 'nom' => 'Ndiaye', 'email' => 'mamadou@odc.sn', 'motDePasse' => password_hash('gerant123', PASSWORD_DEFAULT), 'role' => 'gerant', 'dateInscription' => '2026-01-05', 'actif' => true],
            ['id' => 2, 'prenom' => 'Fatou', 'nom' => 'Diop', 'email' => 'coach@odc.sn', 'motDePasse' => password_hash('coach123', PASSWORD_DEFAULT), 'role' => 'coach', 'dateInscription' => '2026-01-05', 'actif' => true],
            ['id' => 3, 'prenom' => 'Ismaïla', 'nom' => 'Sarr', 'email' => 'ismaila@odc.sn', 'motDePasse' => password_hash('apprenant123', PASSWORD_DEFAULT), 'role' => 'apprenant', 'dateInscription' => '2026-01-05', 'actif' => true],
            ['id' => 4, 'prenom' => 'Aissatou', 'nom' => 'Ba', 'email' => 'aissatou@odc.sn', 'motDePasse' => password_hash('apprenant123', PASSWORD_DEFAULT), 'role' => 'apprenant', 'dateInscription' => '2026-01-05', 'actif' => true],
            ['id' => 5, 'prenom' => 'Sadio', 'nom' => 'Diallo', 'email' => 'sadio@odc.sn', 'motDePasse' => password_hash('apprenant123', PASSWORD_DEFAULT), 'role' => 'apprenant', 'dateInscription' => '2026-01-05', 'actif' => true],
        ]);
    }

    public function getParRole(string $role): array
    {
        return array_values(array_filter($this->getTous(), fn($a) => $a['role'] === $role));
    }

    public function getParEmail(string $email): ?array
    {
        foreach ($this->getTous() as $a) {
            if (strtolower($a['email']) === strtolower($email)) {
                return $a;
            }
        }
        return null;
    }

    /** Auto-inscription ou ajout manuel par le Gérant (3.2) */
    public function creer(string $prenom, string $nom, string $email, string $motDePasse, string $role = 'apprenant'): array
    {
        return $this->ajouter([
            'prenom'          => htmlspecialchars(trim($prenom), ENT_QUOTES, 'UTF-8'),
            'nom'             => htmlspecialchars(trim($nom), ENT_QUOTES, 'UTF-8'),
            'email'           => filter_var($email, FILTER_SANITIZE_EMAIL),
            'motDePasse'      => password_hash($motDePasse, PASSWORD_DEFAULT),
            'role'            => $role,
            'dateInscription' => date('Y-m-d'),
            'actif'           => true,
        ]);
    }

    /** Import Excel/CSV (3.2) : tableau associatif [prenom, nom, email] par ligne */
    public function importerDepuisCsv(array $lignes): int
    {
        $compteur = 0;
        foreach ($lignes as $ligne) {
            if (empty($ligne['email']) || $this->getParEmail($ligne['email'])) {
                continue; // évite les doublons
            }
            $motDePasseTemporaire = substr(bin2hex(random_bytes(4)), 0, 8);
            $this->creer($ligne['prenom'] ?? '', $ligne['nom'] ?? '', $ligne['email'], $motDePasseTemporaire, 'apprenant');
            $compteur++;
        }
        return $compteur;
    }
}
