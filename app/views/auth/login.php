<div class="max-w-sm mx-auto flex flex-col justify-center px-6" style="min-height:90vh">

  <div class="text-center mb-8">
    <div class="w-14 h-14 bg-gray-900 rounded-xl mx-auto mb-3 flex items-center justify-center text-white font-bold text-xl">C</div>
    <h1 class="font-bold text-xl">Gestion des Cotisations</h1>
    <p class="text-sm text-gray-500 mt-1">Cohorte P8 - ODC Sonatel Academy</p>
  </div>

  <form method="POST" action="/login" class="space-y-3">
    <div>
      <label class="text-xs text-gray-500 block mb-1">Email</label>
      <input type="email" name="email" placeholder="nom@exemple.com" required class="w-full border rounded-lg px-3 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs text-gray-500 block mb-1">Mot de passe</label>
      <input type="password" name="mot_de_passe" placeholder="••••••••" required class="w-full border rounded-lg px-3 py-2.5 text-sm">
    </div>
    <button type="submit" class="w-full bg-gray-900 text-white rounded-lg py-2.5 text-sm font-medium mt-2">Se connecter</button>
  </form>

  <p class="text-center text-sm text-gray-500 mt-6">
    Pas encore inscrit ? <a href="/inscription" class="text-gray-900 font-medium">Créer un compte</a>
  </p>

  <div class="text-[11px] text-gray-400 mt-8 border-t pt-4">
    <p class="font-medium mb-1">Comptes de démonstration :</p>
    <p>Gérant : mamadou@odc.sn / gerant123</p>
    <p>Coach : coach@odc.sn / coach123</p>
    <p>Apprenant : ismaila@odc.sn / apprenant123</p>
  </div>

</div>
