<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestion des Cotisations - P8</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { font-family: -apple-system, Arial, sans-serif; background:#f3f2ee; }
  .app { max-width: 480px; margin: 0 auto; min-height: 100vh; background:#fff; }
</style>
</head>
<body>
<div class="app pb-20">
<?php if (isset($utilisateur) && $utilisateur): ?>
  <header class="bg-gray-900 text-white p-4 sticky top-0 z-10 flex justify-between items-center">
    <div>
      <div class="text-xs text-gray-400">
        <?= match($utilisateur['role']) { 'gerant' => 'Espace Gérant', 'coach' => 'Espace Coach', default => 'Espace Apprenant' } ?>
      </div>
      <div class="font-bold text-lg"><?= htmlspecialchars($utilisateur['prenom']) ?></div>
    </div>
    <a href="/logout" class="text-xs border border-gray-600 rounded px-3 py-1">Déconnexion</a>
  </header>
<?php endif; ?>

<?php // Messages flash : gérés une seule fois ici pour toutes les vues (DRY) ?>
<?php if (!empty($flashSucces)): ?>
  <div class="mx-4 mt-4 bg-green-50 text-green-700 text-xs rounded-lg p-3"><?= htmlspecialchars($flashSucces) ?></div>
<?php endif; ?>
<?php if (!empty($flashErreur)): ?>
  <div class="mx-4 mt-4 bg-red-50 text-red-700 text-xs rounded-lg p-3"><?= htmlspecialchars($flashErreur) ?></div>
<?php endif; ?>
