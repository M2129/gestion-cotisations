<?php
require_once __DIR__ . '/../core/SessionManager.php';

/**
 * SemaineModel
 * Gère les ~40 semaines de cotisation hebdomadaire fixe.
 * Structure session : $_SESSION['semaines'] = [numero => [...]]
 * Structure session : $_SESSION['paiements_semaines'][apprenantId][numeroSemaine] = true
 */
class SemaineModel
{
    private const CLE_SEMAINES  = 'semaines';
    private const CLE_STATUTS   = 'paiements_semaines';
    private const CLE_PARAM     = 'parametrage_hebdo';
    private const NB_SEMAINES   = 40;
    private const MONTANT_DEFAUT = 2500;

    public function initialiserDonneesDemo(): void
    {
        if (SessionManager::has(self::CLE_SEMAINES)) {
            return;
        }

        SessionManager::set(self::CLE_PARAM, ['montantFixe' => self::MONTANT_DEFAUT]);

        $semaines = [];
        // Date de départ arbitraire de la formation
        $debut = new DateTime('2026-01-10'); // premier samedi

        for ($i = 1; $i <= self::NB_SEMAINES; $i++) {
            $dateLimite = (clone $debut)->modify('+' . ($i - 1) . ' weeks')->setTime(0, 0, 0);
            $semaines[$i] = [
                'numero'      => $i,
                'dateLimite'  => $dateLimite->format('Y-m-d H:i:s'),
                'montantFixe' => self::MONTANT_DEFAUT,
            ];
        }
        SessionManager::set(self::CLE_SEMAINES, $semaines);

        // Quelques paiements de démonstration
        $statuts = [
            3 => array_fill_keys(range(1, 2), true),  // Ismaïla à jour jusqu'à S2
            4 => array_fill_keys(range(1, 5), true),  // Aissatou à jour jusqu'à S5
            5 => array_fill_keys(range(1, 1), true),  // Sadio à jour jusqu'à S1
        ];
        SessionManager::set(self::CLE_STATUTS, $statuts);
    }

    public function getMontantFixe(): float
    {
        return SessionManager::get(self::CLE_PARAM, ['montantFixe' => self::MONTANT_DEFAUT])['montantFixe'];
    }

    public function definirMontantFixe(float $montant): void
    {
        SessionManager::set(self::CLE_PARAM, ['montantFixe' => $montant]);
    }

    public function getToutesLesSemaines(): array
    {
        return SessionManager::get(self::CLE_SEMAINES, []);
    }

    public function getSemaineCourante(): array
    {
        $semaines = $this->getToutesLesSemaines();
        $maintenant = new DateTime();
        foreach ($semaines as $s) {
            if (new DateTime($s['dateLimite']) >= $maintenant) {
                return $s;
            }
        }
        return end($semaines) ?: [];
    }

    public function estPayee(int $apprenantId, int $numeroSemaine): bool
    {
        $statuts = SessionManager::get(self::CLE_STATUTS, []);
        return !empty($statuts[$apprenantId][$numeroSemaine]);
    }

    public function estEnRetard(int $apprenantId, int $numeroSemaine): bool
    {
        if ($this->estPayee($apprenantId, $numeroSemaine)) {
            return false;
        }
        $semaines = $this->getToutesLesSemaines();
        if (!isset($semaines[$numeroSemaine])) {
            return false;
        }
        return new DateTime() > new DateTime($semaines[$numeroSemaine]['dateLimite']);
    }

    /** Retourne les numéros de semaines non payées, dans l'ordre chronologique */
    public function getSemainesNonPayeesTriees(int $apprenantId): array
    {
        $nonPayees = [];
        foreach ($this->getToutesLesSemaines() as $numero => $s) {
            if (!$this->estPayee($apprenantId, $numero)) {
                $nonPayees[] = $numero;
            }
        }
        sort($nonPayees);
        return $nonPayees;
    }

    public function marquerPayee(int $apprenantId, int $numeroSemaine): void
    {
        $statuts = SessionManager::get(self::CLE_STATUTS, []);
        $statuts[$apprenantId][$numeroSemaine] = true;
        SessionManager::set(self::CLE_STATUTS, $statuts);
    }

    /**
     * Ventilation automatique : consomme le montant versé pour valider
     * les semaines consécutives non payées les plus anciennes en premier.
     * Retourne les numéros de semaines validées et le reliquat non affecté.
     */
    public function ventiler(int $apprenantId, float $montantVerse): array
    {
        $montantFixe = $this->getMontantFixe();
        $semainesValidees = [];
        $restant = $montantVerse;

        foreach ($this->getSemainesNonPayeesTriees($apprenantId) as $numero) {
            if ($restant < $montantFixe) {
                break;
            }
            $this->marquerPayee($apprenantId, $numero);
            $semainesValidees[] = $numero;
            $restant -= $montantFixe;
        }

        return [
            'semainesValidees' => $semainesValidees,
            'reliquat' => round($restant, 2),
        ];
    }
}
