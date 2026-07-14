<?php
require_once __DIR__ . '/../core/SessionManager.php';

function campagne_initialiserDonneesDemo(): void
{
    if (!hasData('campagnes')) {
        save('campagnes', []);
    }
    if (!hasData('participations_campagnes')) {
        save('participations_campagnes', []);
    }
}

function campagne_getToutes(): array
{
    return getData('campagnes', []);
}

function campagne_getActives(): array
{
    return array_values(array_filter(campagne_getToutes(), fn($c) => $c['statut'] === 'active'));
}

function campagne_sauvegarderToutes(array $collection): void
{
    save('campagnes', $collection);
}

function campagne_prochainId(): int
{
    $collection = campagne_getToutes();
    return empty($collection) ? 1 : max(array_column($collection, 'id')) + 1;
}

function campagne_ajouter(array $item): array
{
    $item['id'] = campagne_prochainId();
    $collection = campagne_getToutes();
    $collection[] = $item;
    campagne_sauvegarderToutes($collection);
    return $item;
}

function campagne_creerBase(string $type, string $titre, ?float $montantFixe, DateTime $dateLimite): array
{
    return campagne_ajouter([
        'type' => $type,
        'titre' => htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'),
        'montantFixe' => $montantFixe,
        'dateCreation' => date('Y-m-d H:i:s'),
        'dateLimite' => $dateLimite->format('Y-m-d H:i:s'),
        'statut' => 'active',
    ]);
}

function campagne_creerAnniversaire(float $montantFixe): array
{
    $finDuMois = new DateTime('last day of this month 23:59:59');
    return campagne_creerBase('anniversaire', 'Anniversaire - ' . campagne_nomMoisEnFrancais(), $montantFixe, $finDuMois);
}

function campagne_creerDeces(string $titre): array
{
    $dateLimite = (new DateTime())->modify('+7 days');
    return campagne_creerBase('deces', $titre, null, $dateLimite);
}

function campagne_creerAutre(string $titre, float $montant, DateTime $dateLimite): array
{
    return campagne_creerBase('autre', $titre, $montant, $dateLimite);
}

function campagne_getParId(int $id): ?array
{
    foreach (campagne_getToutes() as $c) {
        if ($c['id'] === $id) {
            return $c;
        }
    }
    return null;
}

function campagne_estCloturee(array $campagne): bool
{
    return $campagne['statut'] === 'cloturee' || new DateTime() > new DateTime($campagne['dateLimite']);
}

function campagne_cloturerCampagnesExpirees(): void
{
    foreach (campagne_getToutes() as $c) {
        if ($c['statut'] === 'active' && new DateTime() > new DateTime($c['dateLimite'])) {
            campagne_mettreAJour($c['id'], ['statut' => 'cloturee']);
        }
    }
}

function campagne_mettreAJour(int $id, array $donnees): void
{
    $collection = campagne_getToutes();
    foreach ($collection as &$item) {
        if ($item['id'] === $id) {
            $item = array_merge($item, $donnees);
            break;
        }
    }
    unset($item);
    campagne_sauvegarderToutes($collection);
}

function campagne_enregistrerParticipation(int $campagneId, int $apprenantId, float $montant): void
{
    $participations = getData('participations_campagnes', []);
    $participations[$campagneId][$apprenantId] = ($participations[$campagneId][$apprenantId] ?? 0) + $montant;
    save('participations_campagnes', $participations);
}

function campagne_getTotalCollecte(int $campagneId): float
{
    $participations = getData('participations_campagnes', []);
    return array_sum($participations[$campagneId] ?? []);
}

function campagne_aParticipe(int $campagneId, int $apprenantId): bool
{
    $participations = getData('participations_campagnes', []);
    return isset($participations[$campagneId][$apprenantId]);
}

function campagne_nomMoisEnFrancais(): string
{
    $mois = [1=>'janvier',2=>'février',3=>'mars',4=>'avril',5=>'mai',6=>'juin',7=>'juillet',8=>'août',9=>'septembre',10=>'octobre',11=>'novembre',12=>'décembre'];
    return $mois[(int) date('n')];
}
