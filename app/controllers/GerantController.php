<?php
require_once __DIR__ . '/../core/Controleur.php';
require_once __DIR__ . '/../models/ApprenantModel.php';
require_once __DIR__ . '/../models/SemaineModel.php';
require_once __DIR__ . '/../models/CampagneModel.php';
require_once __DIR__ . '/../models/PaiementModel.php';

class GerantController extends Controleur
{
    private ApprenantModel $apprenantModel;
    private SemaineModel $semaineModel;
    private CampagneModel $campagneModel;
    private PaiementModel $paiementModel;

    public function __construct()
    {
        $this->proteger(['gerant']);

        $this->apprenantModel = new ApprenantModel();
        $this->semaineModel = new SemaineModel();
        $this->campagneModel = new CampagneModel();
        $this->paiementModel = new PaiementModel();

        $this->apprenantModel->initialiserDonneesDemo();
        $this->semaineModel->initialiserDonneesDemo();
        $this->campagneModel->initialiserDonneesDemo();
        $this->paiementModel->initialiserDonneesDemo();

        // Logique métier (5.) : clôture automatique des campagnes expirées à chaque requête
        $this->campagneModel->cloturerCampagnesExpirees();
    }

    public function dashboard(): void
    {
        $apprenants = $this->apprenantModel->getParRole('apprenant');
        $semaineCourante = $this->semaineModel->getSemaineCourante();
        $numeroCourant = $semaineCourante['numero'] ?? 1;
        $debut = max(1, $numeroCourant - 5);

        $fenetreSemaines = array_filter(
            $this->semaineModel->getToutesLesSemaines(),
            fn($s) => $s['numero'] >= $debut && $s['numero'] <= $numeroCourant
        );

        $tableauCroise = [];
        $nbRetard = 0;
        foreach ($apprenants as $a) {
            $ligne = ['apprenant' => $a, 'statuts' => []];
            $enRetard = false;
            foreach ($fenetreSemaines as $s) {
                $ligne['statuts'][$s['numero']] = $this->statutSemaine($a['id'], $s['numero']);
                if ($ligne['statuts'][$s['numero']] === 'retard') {
                    $enRetard = true;
                }
            }
            $tableauCroise[] = $ligne;
            if ($enRetard) $nbRetard++;
        }

        $this->render('gerant/dashboard', [
            'apprenants'       => $apprenants,
            'fenetreSemaines'  => $fenetreSemaines,
            'tableauCroise'    => $tableauCroise,
            'totalCollecte'    => $this->paiementModel->getTotalCollecteGlobal(),
            'nbRetard'         => $nbRetard,
            'campagnesActives' => $this->campagneModel->getActives(),
        ]);
    }

    /** Détermine le statut d'affichage (payee | retard | attente) pour une cellule du tableau croisé */
    private function statutSemaine(int $apprenantId, int $numeroSemaine): string
    {
        if ($this->semaineModel->estPayee($apprenantId, $numeroSemaine)) return 'payee';
        if ($this->semaineModel->estEnRetard($apprenantId, $numeroSemaine)) return 'retard';
        return 'attente';
    }

    public function afficherFormulairePaiement(): void
    {
        $this->render('gerant/paiement_create', [
            'apprenants'       => $this->apprenantModel->getParRole('apprenant'),
            'campagnesActives' => $this->campagneModel->getActives(),
            'montantFixeHebdo' => $this->semaineModel->getMontantFixe(),
            'tokenCsrf'        => SessionManager::genererTokenCsrf(),
        ]);
    }

    public function enregistrerPaiement(): void
    {
        $this->requireCsrf();

        $apprenantId = (int) ($_POST['apprenant_id'] ?? 0);
        $montant = (float) ($_POST['montant'] ?? 0);
        $cible = $_POST['cible'] ?? 'hebdo';

        if ($apprenantId <= 0 || $montant <= 0 || !$this->apprenantModel->getParId($apprenantId)) {
            $this->setFlash('erreur', 'Apprenant ou montant invalide.');
            $this->rediriger('/gerant/paiements/create');
        }

        if ($cible === 'hebdo') {
            $resultat = $this->paiementModel->enregistrerPaiementHebdo($apprenantId, $montant);
            $nbSemaines = count($resultat['ventilation']['semainesValidees']);
            $this->setFlash('succes', "{$nbSemaines} semaine(s) validée(s), reliquat de {$resultat['ventilation']['reliquat']} FCFA.");
        } else {
            $campagneId = (int) ($_POST['campagne_id'] ?? 0);
            $this->paiementModel->enregistrerPaiementCampagne($apprenantId, $campagneId, $montant);
            $this->setFlash('succes', 'Participation à la campagne enregistrée.');
        }

        $this->rediriger('/gerant/dashboard');
    }

    public function afficherFormulaireCampagne(): void
    {
        $this->render('gerant/campagne_create', [
            'tokenCsrf' => SessionManager::genererTokenCsrf(),
        ]);
    }

    public function enregistrerCampagne(): void
    {
        $this->requireCsrf();

        $type = $_POST['type'] ?? 'autre';
        $titre = $this->nettoyer($_POST['titre'] ?? '');

        match ($type) {
            'anniversaire' => $this->campagneModel->creerCampagneAnniversaire((float) ($_POST['montant'] ?? 0)),
            'deces'        => $this->campagneModel->creerCampagneDeces($titre ?: 'Cas social'),
            default        => $this->campagneModel->creerCampagneAutre(
                $titre ?: 'Événement',
                (float) ($_POST['montant'] ?? 0),
                new DateTime($_POST['date_limite'] ?? '+30 days')
            ),
        };

        $this->setFlash('succes', 'Campagne créée avec succès.');
        $this->rediriger('/gerant/dashboard');
    }

    public function apprenants(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_creer'])) {
            $this->requireCsrf();
            $this->apprenantModel->creer(
                $this->nettoyer($_POST['prenom'] ?? ''),
                $this->nettoyer($_POST['nom'] ?? ''),
                filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
                substr(bin2hex(random_bytes(4)), 0, 8),
                'apprenant'
            );
            $this->setFlash('succes', 'Apprenant ajouté avec succès.');
            $this->rediriger('/gerant/apprenants');
        }

        $this->render('gerant/apprenants', [
            'apprenants' => $this->apprenantModel->getParRole('apprenant'),
            'tokenCsrf'  => SessionManager::genererTokenCsrf(),
        ]);
    }
}
