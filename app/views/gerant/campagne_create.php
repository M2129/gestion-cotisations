<div class="p-4 md:p-8 max-w-md md:mx-auto">
  <div class="flex items-center mb-4">
    <a href="/gerant/dashboard" class="mr-2 text-gray-500">&larr;</a>
    <h1 class="font-bold text-base">Nouvelle campagne ponctuelle</h1>
  </div>

  <form method="POST" action="/gerant/campagnes/create" class="space-y-3" id="formCampagne">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenCsrf) ?>">

    <div>
      <label class="text-xs text-gray-500 block mb-1">Type de campagne</label>
      <select name="type" id="type" onchange="basculerChamps()" class="w-full border rounded-lg px-3 py-2 text-sm">
        <option value="anniversaire">Anniversaire (montant fixe, fin de mois)</option>
        <option value="deces">Cas social / Décès (montant libre, 7 jours)</option>
        <option value="autre">Autre événement</option>
      </select>
    </div>

    <div id="champTitre" class="hidden">
      <label class="text-xs text-gray-500 block mb-1">Titre</label>
      <input type="text" name="titre" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="ex: Sortie pédagogique">
    </div>

    <div id="champMontant">
      <label class="text-xs text-gray-500 block mb-1">Montant fixe (FCFA)</label>
      <input type="number" name="montant" min="1" value="3000" class="w-full border rounded-lg px-3 py-2 text-sm">
    </div>

    <div id="champDate" class="hidden">
      <label class="text-xs text-gray-500 block mb-1">Date limite</label>
      <input type="date" name="date_limite" class="w-full border rounded-lg px-3 py-2 text-sm">
    </div>

    <div class="bg-gray-50 rounded-lg p-3 text-xs text-gray-600" id="regleAffaire">
      Collectée automatiquement lors de la dernière semaine du mois en cours.
    </div>

    <button type="submit" class="w-full bg-gray-900 text-white rounded-lg py-2.5 text-sm font-medium">Créer la campagne</button>
  </form>
</div>

<script>
function basculerChamps() {
  const type = document.getElementById('type').value;
  document.getElementById('champTitre').classList.toggle('hidden', type === 'anniversaire');
  document.getElementById('champMontant').classList.toggle('hidden', type === 'deces');
  document.getElementById('champDate').classList.toggle('hidden', type !== 'autre');

  const regles = {
    anniversaire: 'Collectée automatiquement lors de la dernière semaine du mois en cours. Montant fixe pour chaque apprenant.',
    deces: 'Clôture automatique après 7 jours. Montant libre selon la volonté de chaque apprenant.',
    autre: 'Montant et date limite définis librement par le Gérant.'
  };
  document.getElementById('regleAffaire').textContent = regles[type];
}
</script>
