<?php
require_once __DIR__ . '/../core/SessionManager.php';

function semaine_initialiserDonneesDemo(): void
{
    if (hasData('semaines')) {
        return;
    }

    save('parametrage_hebdo', ['montantFixe' => 2500]);

    $semaines = [];
    $debut = new DateTime('2026-01-10');
    for ($i = 1; $i <= 40; $i++) {
        $dateLimite = (clone $debut)->modify('+' . ($i - 1) . ' weeks')->setTime(0, 0, 0);
        $semaines[$i] = [
            'numero' => $i,
            'dateLimite' => $dateLimite->format('Y-m-d H:i:s'),
            'montantFixe' => 2500,
        ];
    }
    save('semaines', $semaines);

    $statuts = [
        3 => array_fill_keys(range(1, 2), true),
        4 => array_fill_keys(range(1, 5), true),
        5 => array_fill_keys(range(1, 1), true),
    ];
    save('paiements_semaines', $statuts);
}

function semaine_getMontantFixe(): float
{
    return getData('parametrage_hebdo', ['montantFixe' => 2500])['montantFixe'];
}

function semaine_definirMontantFixe(float $montant): void
{
    save('parametrage_hebdo', ['montantFixe' => $montant]);
}

function semaine_getToutesLesSemaines(): array
{
    return getData('semaines', []);
}

function semaine_getSemaineCourante(): array
{
    $semaines = semaine_getToutesLesSemaines();
    $maintenant = new DateTime();
    foreach ($semaines as $s) {
        if (new DateTime($s['dateLimite']) >= $maintenant) {
            return $s;
        }
    }
    return end($semaines) ?: [];
}

function semaine_estPayee(int $apprenantId, int $numeroSemaine): bool
{
    $statuts = getData('paiements_semaines', []);
    return !empty($statuts[$apprenantId][$numeroSemaine]);
}

function semaine_estEnRetard(int $apprenantId, int $numeroSemaine): bool
{
    if (semaine_estPayee($apprenantId, $numeroSemaine)) {
        return false;
    }
    $semaines = semaine_getToutesLesSemaines();
    if (!isset($semaines[$numeroSemaine])) {
        return false;
    }
    return new DateTime() > new DateTime($semaines[$numeroSemaine]['dateLimite']);
}

function semaine_getSemainesNonPayeesTriees(int $apprenantId): array
{
    $nonPayees = [];
    foreach (semaine_getToutesLesSemaines() as $numero => $s) {
        if (!semaine_estPayee($apprenantId, $numero)) {
            $nonPayees[] = $numero;
        }
    }
    sort($nonPayees);
    return $nonPayees;
}

function semaine_marquerPayee(int $apprenantId, int $numeroSemaine): void
{
    $statuts = getData('paiements_semaines', []);
    $statuts[$apprenantId][$numeroSemaine] = true;
    save('paiements_semaines', $statuts);
}

function semaine_ventiler(int $apprenantId, float $montantVerse): array
{
    $montantFixe = semaine_getMontantFixe();
    $semainesValidees = [];
    $restant = $montantVerse;

    foreach (semaine_getSemainesNonPayeesTriees($apprenantId) as $numero) {
        if ($restant < $montantFixe) {
            break;
        }
        semaine_marquerPayee($apprenantId, $numero);
        $semainesValidees[] = $numero;
        $restant -= $montantFixe;
    }

    return [
        'semainesValidees' => $semainesValidees,
        'reliquat' => round($restant, 2),
    ];
}
