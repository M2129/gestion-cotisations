<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestion des Cotisations - P8</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { font-family: -apple-system, Arial, sans-serif; background:#f3f2ee; }
</style>
</head>
<body>
<div class="w-full max-w-[480px] md:max-w-3xl lg:max-w-5xl mx-auto min-h-screen bg-white pb-24 md:pb-10">
<?php if (isset($utilisateur) && $utilisateur): ?>
  <header class="bg-gray-900 text-white p-4 md:px-8 sticky top-0 z-10 flex justify-between items-center">
    <div>
      <div class="text-xs text-gray-400">
        <?= match($utilisateur['role']) { 'gerant' => 'Espace Gérant', 'coach' => 'Espace Coach', default => 'Espace Apprenant' } ?>
      </div>
      <div class="font-bold text-lg"><?= htmlspecialchars($utilisateur['prenom']) ?></div>
    </div>
    <a href="/logout" class="text-xs border border-gray-600 rounded px-3 py-1">Déconnexion</a>
  </header>
  <?php if ($utilisateur['role'] === 'gerant'): ?>
    <!-- Navigation desktop/tablette : cachée sur mobile, la nav mobile est en bas (footer.php) -->
    <nav class="hidden md:flex gap-6 px-8 py-3 border-b text-sm text-gray-500">
      <?php include __DIR__ . '/nav_gerant.php'; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>

<?php // Messages flash : gérés une seule fois ici pour toutes les vues (DRY) ?>
<?php if (!empty($flashSucces)): ?>
  <div class="mx-4 md:mx-8 mt-4 bg-green-50 text-green-700 text-xs rounded-lg p-3"><?= htmlspecialchars($flashSucces) ?></div>
<?php endif; ?>
<?php if (!empty($flashErreur)): ?>
  <div class="mx-4 md:mx-8 mt-4 bg-red-50 text-red-700 text-xs rounded-lg p-3"><?= htmlspecialchars($flashErreur) ?></div>
<?php endif; ?>
