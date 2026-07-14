<?php
require_once __DIR__ . '/../core/procedural_core.php';
require_once __DIR__ . '/../models/apprenant.php';
require_once __DIR__ . '/../models/semaine.php';
require_once __DIR__ . '/../models/campagne.php';
require_once __DIR__ . '/../models/paiement.php';

function gerant_initialiserDonneesDemo(): void
{
    apprenant_initialiserDonneesDemo();
    semaine_initialiserDonneesDemo();
    campagne_initialiserDonneesDemo();
    paiement_initialiserDonneesDemo();
    campagne_cloturerCampagnesExpirees();
}

function dashboard(): void
{
    proteger(['gerant']);
    gerant_initialiserDonneesDemo();

    $apprenants = apprenant_getParRole('apprenant');
    $semaineCourante = semaine_getSemaineCourante();
    $numeroCourant = $semaineCourante['numero'] ?? 1;
    $debut = max(1, $numeroCourant - 5);

    $fenetreSemaines = array_filter(
        semaine_getToutesLesSemaines(),
        fn($s) => $s['numero'] >= $debut && $s['numero'] <= $numeroCourant
    );

    $tableauCroise = [];
    $nbRetard = 0;
    foreach ($apprenants as $a) {
        $ligne = ['apprenant' => $a, 'statuts' => []];
        $enRetard = false;
        foreach ($fenetreSemaines as $s) {
            $ligne['statuts'][$s['numero']] = statutSemaine($a['id'], $s['numero']);
            if ($ligne['statuts'][$s['numero']] === 'retard') {
                $enRetard = true;
            }
        }
        $tableauCroise[] = $ligne;
        if ($enRetard) {
            $nbRetard++;
        }
    }

    render('gerant/dashboard', [
        'apprenants'       => $apprenants,
        'fenetreSemaines'  => $fenetreSemaines,
        'tableauCroise'    => $tableauCroise,
        'totalCollecte'    => paiement_getTotalCollecteGlobal(),
        'nbRetard'         => $nbRetard,
        'campagnesActives' => campagne_getActives(),
    ]);
}

function statutSemaine(int $apprenantId, int $numeroSemaine): string
{
    if (semaine_estPayee($apprenantId, $numeroSemaine)) return 'payee';
    if (semaine_estEnRetard($apprenantId, $numeroSemaine)) return 'retard';
    return 'attente';
}

function afficherFormulairePaiement(): void
{
    proteger(['gerant']);
    gerant_initialiserDonneesDemo();

    render('gerant/paiement_create', [
        'apprenants'       => apprenant_getParRole('apprenant'),
        'campagnesActives' => campagne_getActives(),
        'montantFixeHebdo' => semaine_getMontantFixe(),
        'tokenCsrf'        => generateCsrfToken(),
    ]);
}

function enregistrerPaiement(): void
{
    proteger(['gerant']);
    requireCsrf();
    gerant_initialiserDonneesDemo();

    $apprenantId = (int) ($_POST['apprenant_id'] ?? 0);
    $montant = (float) ($_POST['montant'] ?? 0);
    $cible = $_POST['cible'] ?? 'hebdo';

    if ($apprenantId <= 0 || $montant <= 0 || !apprenant_getParId($apprenantId)) {
        setFlash('erreur', 'Apprenant ou montant invalide.');
        rediriger('/gerant/paiements/create');
    }

    if ($cible === 'hebdo') {
        $resultat = paiement_enregistrerPaiementHebdo($apprenantId, $montant);
        $nbSemaines = count($resultat['ventilation']['semainesValidees']);
        setFlash('succes', "{$nbSemaines} semaine(s) validée(s), reliquat de {$resultat['ventilation']['reliquat']} FCFA.");
    } else {
        $campagneId = (int) ($_POST['campagne_id'] ?? 0);
        paiement_enregistrerPaiementCampagne($apprenantId, $campagneId, $montant);
        setFlash('succes', 'Participation à la campagne enregistrée.');
    }

    rediriger('/gerant/dashboard');
}

function afficherFormulaireCampagne(): void
{
    protéger(['gerant']);
    render('gerant/campagne_create', [
        'tokenCsrf' => generateCsrfToken(),
    ]);
}

function enregistrerCampagne(): void
{
    proteger(['gerant']);
    requireCsrf();
    gerant_initialiserDonneesDemo();

    $type = $_POST['type'] ?? 'autre';
    $titre = nettoyer($_POST['titre'] ?? '');

    switch ($type) {
        case 'anniversaire':
            campagne_creerAnniversaire((float) ($_POST['montant'] ?? 0));
            break;
        case 'deces':
            campagne_creerDeces($titre ?: 'Cas social');
            break;
        default:
            campagne_creerAutre(
                $titre ?: 'Événement',
                (float) ($_POST['montant'] ?? 0),
                new DateTime($_POST['date_limite'] ?? '+30 days')
            );
            break;
    }

    setFlash('succes', 'Campagne créée avec succès.');
    rediriger('/gerant/dashboard');
}

function apprenants(): void
{
    proteger(['gerant']);
    gerant_initialiserDonneesDemo();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_creer'])) {
        requireCsrf();
        apprenant_creer(
            nettoyer($_POST['prenom'] ?? ''),
            nettoyer($_POST['nom'] ?? ''),
            filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
            substr(bin2hex(random_bytes(4)), 0, 8),
            'apprenant'
        );
        setFlash('succes', 'Apprenant ajouté avec succès.');
        rediriger('/gerant/apprenants');
    }

    render('gerant/apprenants', [
        'apprenants' => apprenant_getParRole('apprenant'),
        'tokenCsrf'  => generateCsrfToken(),
    ]);
}
