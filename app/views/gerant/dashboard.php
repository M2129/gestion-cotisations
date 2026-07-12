<style>
  .grid-week { display:grid; grid-template-columns: 90px repeat(<?= count($fenetreSemaines) ?>, 34px); }
  .cell { display:flex; align-items:center; justify-content:center; height:34px; font-size:11px; }
  .paid { background:#c0dd97; color:#173404; }
  .late { background:#f09595; color:#501313; }
  .pending { background:#f1efe8; color:#5f5e5a; }
</style>

<section class="p-4 grid grid-cols-3 gap-2">
  <div class="bg-gray-50 rounded-lg p-3 text-center">
    <div class="text-[11px] text-gray-500">Collecté</div>
    <div class="font-bold text-sm"><?= number_format($totalCollecte, 0, ',', ' ') ?></div>
    <div class="text-[10px] text-gray-400">FCFA</div>
  </div>
  <div class="bg-gray-50 rounded-lg p-3 text-center">
    <div class="text-[11px] text-gray-500">Apprenants</div>
    <div class="font-bold text-sm"><?= count($apprenants) ?></div>
  </div>
  <div class="bg-red-50 rounded-lg p-3 text-center">
    <div class="text-[11px] text-red-600">En retard</div>
    <div class="font-bold text-sm text-red-600"><?= $nbRetard ?></div>
  </div>
</section>

<section class="px-4">
  <h2 class="font-bold text-sm mb-2">Tableau croisé - dernières semaines</h2>
  <div class="overflow-x-auto border rounded-lg">
    <div class="grid-week bg-gray-100 border-b">
      <div class="cell font-bold text-left pl-2">Apprenant</div>
      <?php foreach ($fenetreSemaines as $s): ?>
        <div class="cell font-bold">S<?= $s['numero'] ?></div>
      <?php endforeach; ?>
    </div>
    <?php foreach ($tableauCroise as $ligne): ?>
      <div class="grid-week border-b">
        <div class="cell text-left pl-2 truncate"><?= htmlspecialchars($ligne['apprenant']['prenom']) ?> <?= htmlspecialchars(substr($ligne['apprenant']['nom'],0,1)) ?>.</div>
        <?php foreach ($ligne['statuts'] as $statut): ?>
          <?php $classe = ['payee'=>'paid','retard'=>'late','attente'=>'pending'][$statut]; ?>
          <div class="cell <?= $classe ?>"><?= $statut === 'payee' ? '✓' : ($statut === 'retard' ? '✗' : '·') ?></div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="px-4 mt-4">
  <h2 class="font-bold text-sm mb-2">Campagnes actives</h2>
  <?php if (empty($campagnesActives)): ?>
    <p class="text-xs text-gray-400">Aucune campagne active pour le moment.</p>
  <?php endif; ?>
  <?php foreach ($campagnesActives as $c): ?>
    <div class="border rounded-lg p-3 mb-2 text-sm flex justify-between">
      <span><?= htmlspecialchars($c['titre']) ?></span>
      <span class="text-xs text-gray-500"><?= $c['montantFixe'] !== null ? number_format($c['montantFixe'],0,',',' ').' FCFA' : 'Libre' ?></span>
    </div>
  <?php endforeach; ?>
</section>

<section class="p-4">
  <a href="/gerant/paiements/create" class="block w-full bg-gray-900 text-white text-center rounded-lg py-2.5 text-sm font-medium mb-2">+ Enregistrer un paiement</a>
  <a href="/gerant/campagnes/create" class="block w-full border text-center rounded-lg py-2.5 text-sm font-medium">+ Créer une campagne ponctuelle</a>
</section>
