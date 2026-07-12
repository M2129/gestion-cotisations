<?php
require_once __DIR__ . '/../core/ModeleSession.php';

/**
 * CampagneModel
 * Gère les campagnes ponctuelles : anniversaire, décès (cas social), autre (3.3).
 * $_SESSION['campagnes'] = liste de campagnes (via ModeleSession)
 * $_SESSION['participations_campagnes'][campagneId][apprenantId] = montant
 */
class CampagneModel extends ModeleSession
{
    private const CLE_PARTICIPATIONS = 'participations_campagnes';

    public function __construct()
    {
        parent::__construct('campagnes');
    }

    public function initialiserDonneesDemo(): void
    {
        if (!SessionManager::has($this->cleSession)) {
            $this->sauvegarderTous([]);
        }
        if (!SessionManager::has(self::CLE_PARTICIPATIONS)) {
            SessionManager::set(self::CLE_PARTICIPATIONS, []);
        }
    }

    public function getActives(): array
    {
        return array_values(array_filter($this->getTous(), fn($c) => $c['statut'] === 'active'));
    }

    private function creerBase(string $type, string $titre, ?float $montantFixe, DateTime $dateLimite): array
    {
        return $this->ajouter([
            'type'         => $type, // anniversaire | deces | autre
            'titre'        => htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'),
            'montantFixe'  => $montantFixe, // null = montant libre
            'dateCreation' => date('Y-m-d H:i:s'),
            'dateLimite'   => $dateLimite->format('Y-m-d H:i:s'),
            'statut'       => 'active',
        ]);
    }

    /** Anniversaires : montant fixe, collecté la dernière semaine du mois */
    public function creerCampagneAnniversaire(float $montantFixe): array
    {
        $finDuMois = new DateTime('last day of this month 23:59:59');
        return $this->creerBase('anniversaire', 'Anniversaire - ' . self::nomMoisEnFrancais(), $montantFixe, $finDuMois);
    }

    /** Décès / cas social : montant libre, clôture stricte à 7 jours */
    public function creerCampagneDeces(string $titre): array
    {
        $dateLimite = (new DateTime())->modify('+7 days');
        return $this->creerBase('deces', $titre, null, $dateLimite);
    }

    /** Autre événement : montant et date limite libres */
    public function creerCampagneAutre(string $titre, float $montant, DateTime $dateLimite): array
    {
        return $this->creerBase('autre', $titre, $montant, $dateLimite);
    }

    public function estCloturee(array $campagne): bool
    {
        return $campagne['statut'] === 'cloturee'
            || new DateTime() > new DateTime($campagne['dateLimite']);
    }

    /** Clôture automatique des campagnes expirées (à appeler à chaque requête) */
    public function cloturerCampagnesExpirees(): void
    {
        foreach ($this->getTous() as $c) {
            if ($c['statut'] === 'active' && new DateTime() > new DateTime($c['dateLimite'])) {
                $this->mettreAJour($c['id'], ['statut' => 'cloturee']);
            }
        }
    }

    public function enregistrerParticipation(int $campagneId, int $apprenantId, float $montant): void
    {
        $participations = SessionManager::get(self::CLE_PARTICIPATIONS, []);
        $participations[$campagneId][$apprenantId] = ($participations[$campagneId][$apprenantId] ?? 0) + $montant;
        SessionManager::set(self::CLE_PARTICIPATIONS, $participations);
    }

    public function getTotalCollecte(int $campagneId): float
    {
        $participations = SessionManager::get(self::CLE_PARTICIPATIONS, []);
        return array_sum($participations[$campagneId] ?? []);
    }

    public function aParticipe(int $campagneId, int $apprenantId): bool
    {
        $participations = SessionManager::get(self::CLE_PARTICIPATIONS, []);
        return isset($participations[$campagneId][$apprenantId]);
    }

    private static function nomMoisEnFrancais(): string
    {
        $mois = [1=>'janvier',2=>'février',3=>'mars',4=>'avril',5=>'mai',6=>'juin',
                 7=>'juillet',8=>'août',9=>'septembre',10=>'octobre',11=>'novembre',12=>'décembre'];
        return $mois[(int) date('n')];
    }
}
