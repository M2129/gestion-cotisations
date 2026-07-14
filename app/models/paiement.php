<?php
require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/semaine.php';
require_once __DIR__ . '/campagne.php';

function paiement_initialiserDonneesDemo(): void
{
    if (!hasData('paiements')) {
        save('paiements', []);
    }
}

function paiement_getTous(): array
{
    return getData('paiements', []);
}

function paiement_sauvegarderTous(array $collection): void
{
    save('paiements', $collection);
}

function paiement_prochainId(): int
{
    $collection = paiement_getTous();
    return empty($collection) ? 1 : max(array_column($collection, 'id')) + 1;
}

function paiement_enregistrerLigne(int $apprenantId, float $montant, string $cible, array $details = []): array
{
    $paiement = [
        'id' => paiement_prochainId(),
        'apprenantId' => $apprenantId,
        'montant' => $montant,
        'cible' => $cible,
        'details' => $details,
        'datePaiement' => date('Y-m-d H:i:s'),
    ];
    $collection = paiement_getTous();
    $collection[] = $paiement;
    paiement_sauvegarderTous($collection);
    return $paiement;
}

function paiement_getHistoriquePourApprenant(int $apprenantId): array
{
    return array_values(array_filter(paiement_getTous(), fn($p) => $p['apprenantId'] === $apprenantId));
}

function paiement_getTotalCollecteGlobal(): float
{
    return array_sum(array_column(paiement_getTous(), 'montant'));
}

function paiement_enregistrerPaiementHebdo(int $apprenantId, float $montant): array
{
    $resultat = semaine_ventiler($apprenantId, $montant);
    $paiement = paiement_enregistrerLigne($apprenantId, $montant, 'hebdo', [
        'semainesValidees' => $resultat['semainesValidees'],
        'reliquat' => $resultat['reliquat'],
    ]);
    return array_merge($paiement, ['ventilation' => $resultat]);
}

function paiement_enregistrerPaiementCampagne(int $apprenantId, int $campagneId, float $montant): array
{
    campagne_enregistrerParticipation($campagneId, $apprenantId, $montant);
    return paiement_enregistrerLigne($apprenantId, $montant, 'campagne', ['campagneId' => $campagneId]);
}

function paiement_enregistrerPassif(int $apprenantId, float $montant, string $description, string $dateOrigine): array
{
    return paiement_enregistrerLigne($apprenantId, $montant, 'passif', [
        'description' => htmlspecialchars($description, ENT_QUOTES, 'UTF-8'),
        'dateOrigine' => $dateOrigine,
    ]);
}
