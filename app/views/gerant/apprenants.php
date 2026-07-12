<div class="p-4">
  <h1 class="font-bold text-base mb-4">Gestion des apprenants</h1>

  <details class="mb-4 border rounded-lg p-3">
    <summary class="text-sm font-medium cursor-pointer">+ Ajouter manuellement</summary>
    <form method="POST" action="/gerant/apprenants" class="space-y-2 mt-3">
      <input type="hidden" name="action_creer" value="1">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenCsrf) ?>">
      <input type="text" name="prenom" placeholder="Prénom" required class="w-full border rounded-lg px-3 py-2 text-sm">
      <input type="text" name="nom" placeholder="Nom" required class="w-full border rounded-lg px-3 py-2 text-sm">
      <input type="email" name="email" placeholder="Email" required class="w-full border rounded-lg px-3 py-2 text-sm">
      <button type="submit" class="w-full bg-gray-900 text-white rounded-lg py-2 text-sm">Ajouter</button>
      <p class="text-[11px] text-gray-400">Un mot de passe temporaire sera généré (reprise des passifs incluse pour les inscriptions rétroactives).</p>
    </form>
  </details>

  <details class="mb-4 border rounded-lg p-3">
    <summary class="text-sm font-medium cursor-pointer">+ Importer un fichier Excel/CSV</summary>
    <p class="text-xs text-gray-500 mt-2 mb-2">Colonnes attendues : prenom, nom, email</p>
    <input type="file" accept=".csv,.xlsx" class="text-xs">
    <p class="text-[11px] text-gray-400 mt-2">Le traitement du fichier est délégué à ImportController::traiterCsv() (à brancher sur cette action).</p>
  </details>

  <h2 class="font-bold text-sm mb-2">Liste (<?= count($apprenants) ?>)</h2>
  <div class="border rounded-lg divide-y">
    <?php foreach ($apprenants as $a): ?>
      <div class="flex justify-between items-center px-3 py-2 text-sm">
        <div>
          <div><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></div>
          <div class="text-[11px] text-gray-400"><?= htmlspecialchars($a['email']) ?></div>
        </div>
        <span class="text-[11px] <?= $a['actif'] ? 'text-green-600' : 'text-gray-400' ?>">
          <?= $a['actif'] ? 'Actif' : 'Inactif' ?>
        </span>
      </div>
    <?php endforeach; ?>
  </div>
</div>
