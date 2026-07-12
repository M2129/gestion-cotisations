<div class="p-4">
  <div class="flex items-center mb-4">
    <a href="/gerant/dashboard" class="mr-2 text-gray-500">&larr;</a>
    <h1 class="font-bold text-base">Nouveau paiement</h1>
  </div>

  <form method="POST" action="/gerant/paiements/create" class="space-y-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenCsrf) ?>">

    <div>
      <label class="text-xs text-gray-500 block mb-1">Apprenant</label>
      <select name="apprenant_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
        <?php foreach ($apprenants as $a): ?>
          <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="text-xs text-gray-500 block mb-1">Montant versé (FCFA)</label>
      <input type="number" name="montant" min="1" step="1" value="<?= (int)$montantFixeHebdo ?>" required class="w-full border rounded-lg px-3 py-2 text-sm">
    </div>

    <div>
      <label class="text-xs text-gray-500 block mb-1">Cible du paiement</label>
      <div class="space-y-2">
        <label class="flex items-center gap-2 border rounded-lg px-3 py-2 text-sm bg-gray-50">
          <input type="radio" name="cible" value="hebdo" checked> Cotisations hebdomadaires (ventilation auto)
        </label>
        <?php foreach ($campagnesActives as $c): ?>
          <label class="flex items-center gap-2 border rounded-lg px-3 py-2 text-sm">
            <input type="radio" name="cible" value="campagne" onclick="document.getElementById('campagne_id').value=<?= $c['id'] ?>">
            <?= htmlspecialchars($c['titre']) ?>
            <?= $c['montantFixe'] === null ? '<span class="text-gray-400">(montant libre)</span>' : '' ?>
          </label>
        <?php endforeach; ?>
      </div>
      <input type="hidden" name="campagne_id" id="campagne_id" value="">
    </div>

    <div class="bg-blue-50 rounded-lg p-3 text-xs text-blue-800">
      La ventilation automatique validera les semaines consécutives les plus anciennes non payées, dans la limite du montant versé.
    </div>

    <button type="submit" class="w-full bg-gray-900 text-white rounded-lg py-2.5 text-sm font-medium">Valider le paiement</button>
  </form>
</div>
