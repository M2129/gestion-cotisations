<?php
require_once __DIR__ . '/../core/ModeleSession.php';
require_once __DIR__ . '/SemaineModel.php';
require_once __DIR__ . '/CampagneModel.php';

/**
 * PaiementModel
 * Saisie déclarative des paiements (3.4) : aucune API externe.
 * $_SESSION['paiements'] = liste chronologique de tous les paiements.
 */
class PaiementModel extends ModeleSession
{
    private SemaineModel $semaineModel;
    private CampagneModel $campagneModel;

    public function __construct()
    {
        parent::__construct('paiements');
        $this->semaineModel = new SemaineModel();
        $this->campagneModel = new CampagneModel();
    }

    public function initialiserDonneesDemo(): void
    {
        if (!SessionManager::has($this->cleSession)) {
            $this->sauvegarderTous([]);
        }
    }

    public function getHistoriquePourApprenant(int $apprenantId): array
    {
        return array_values(array_filter($this->getTous(), fn($p) => $p['apprenantId'] === $apprenantId));
    }

    public function getTotalCollecteGlobal(): float
    {
        return array_sum(array_column($this->getTous(), 'montant'));
    }

    private function enregistrerLigne(int $apprenantId, float $montant, string $cible, array $details = []): array
    {
        return $this->ajouter([
            'apprenantId'  => $apprenantId,
            'montant'      => $montant,
            'cible'        => $cible, // hebdo | campagne | passif
            'details'      => $details,
            'datePaiement' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Cible = cotisations hebdomadaires (3.1) : déclenche la ventilation automatique */
    public function enregistrerPaiementHebdo(int $apprenantId, float $montant): array
    {
        $resultat = $this->semaineModel->ventiler($apprenantId, $montant);
        $paiement = $this->enregistrerLigne($apprenantId, $montant, 'hebdo', [
            'semainesValidees' => $resultat['semainesValidees'],
            'reliquat'         => $resultat['reliquat'],
        ]);
        return array_merge($paiement, ['ventilation' => $resultat]);
    }

    /** Cible = campagne (anniversaire, décès, autre) */
    public function enregistrerPaiementCampagne(int $apprenantId, int $campagneId, float $montant): array
    {
        $this->campagneModel->enregistrerParticipation($campagneId, $apprenantId, $montant);
        return $this->enregistrerLigne($apprenantId, $montant, 'campagne', ['campagneId' => $campagneId]);
    }

    /** Reprise des passifs (3.6) : paiement/dette antérieur à l'application */
    public function enregistrerPassif(int $apprenantId, float $montant, string $description, string $dateOrigine): array
    {
        return $this->enregistrerLigne($apprenantId, $montant, 'passif', [
            'description' => htmlspecialchars($description, ENT_QUOTES, 'UTF-8'),
            'dateOrigine' => $dateOrigine,
        ]);
    }
}
