<div class="max-w-sm mx-auto px-6 py-8">

  <div class="flex items-center mb-6">
    <a href="/login" class="mr-2 text-gray-500">&larr;</a>
    <h1 class="font-bold text-lg">Créer un compte</h1>
  </div>

  <p class="text-sm text-gray-500 mb-6">Inscris-toi pour suivre tes cotisations hebdomadaires et les campagnes de la classe.</p>

  <form method="POST" action="/inscription" class="space-y-3">
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-xs text-gray-500 block mb-1">Prénom</label>
        <input type="text" name="prenom" required class="w-full border rounded-lg px-3 py-2.5 text-sm">
      </div>
      <div>
        <label class="text-xs text-gray-500 block mb-1">Nom</label>
        <input type="text" name="nom" required class="w-full border rounded-lg px-3 py-2.5 text-sm">
      </div>
    </div>
    <div>
      <label class="text-xs text-gray-500 block mb-1">Email</label>
      <input type="email" name="email" required class="w-full border rounded-lg px-3 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs text-gray-500 block mb-1">Mot de passe</label>
      <input type="password" name="mot_de_passe" placeholder="8 caractères minimum" required minlength="8" class="w-full border rounded-lg px-3 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs text-gray-500 block mb-1">Confirmer le mot de passe</label>
      <input type="password" name="confirmation" required minlength="8" class="w-full border rounded-lg px-3 py-2.5 text-sm">
    </div>

    <label class="flex items-start gap-2 text-xs text-gray-500 pt-1">
      <input type="checkbox" required class="mt-0.5">
      J'accepte de participer aux cotisations hebdomadaires et ponctuelles de la classe.
    </label>

    <button type="submit" class="w-full bg-gray-900 text-white rounded-lg py-2.5 text-sm font-medium mt-2">Créer mon compte</button>
  </form>

  <p class="text-center text-sm text-gray-500 mt-6">
    Déjà inscrit ? <a href="/login" class="text-gray-900 font-medium">Se connecter</a>
  </p>

</div>
