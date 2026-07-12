<?php
require_once __DIR__ . '/SessionManager.php';

/**
 * ModeleSession
 * Classe mère pour tout modèle stocké comme liste en session.
 * Factorise la logique commune (lecture, ID auto-incrémenté, mise à jour,
 * suppression) pour éviter la répétition dans chaque modèle (DRY).
 */
abstract class ModeleSession
{
    protected string $cleSession;

    public function __construct(string $cleSession)
    {
        $this->cleSession = $cleSession;
    }

    public function getTous(): array
    {
        return SessionManager::get($this->cleSession, []);
    }

    public function getParId(int $id): ?array
    {
        foreach ($this->getTous() as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }
        return null;
    }

    public function supprimer(int $id): void
    {
        $restants = array_values(array_filter(
            $this->getTous(),
            fn($item) => $item['id'] !== $id
        ));
        $this->sauvegarderTous($restants);
    }

    protected function sauvegarderTous(array $collection): void
    {
        SessionManager::set($this->cleSession, $collection);
    }

    protected function prochainId(): int
    {
        $collection = $this->getTous();
        return empty($collection) ? 1 : max(array_column($collection, 'id')) + 1;
    }

    /** Ajoute un élément avec un ID auto-généré et le persiste */
    protected function ajouter(array $item): array
    {
        $item['id'] = $this->prochainId();
        $collection = $this->getTous();
        $collection[] = $item;
        $this->sauvegarderTous($collection);
        return $item;
    }

    /** Met à jour partiellement un élément identifié par son ID */
    protected function mettreAJour(int $id, array $donnees): void
    {
        $collection = $this->getTous();
        foreach ($collection as &$item) {
            if ($item['id'] === $id) {
                $item = array_merge($item, $donnees);
                break;
            }
        }
        unset($item);
        $this->sauvegarderTous($collection);
    }
}
