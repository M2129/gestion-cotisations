<?php if (isset($utilisateur) && $utilisateur && $utilisateur['role'] === 'gerant'): ?>
  <nav class="fixed bottom-0 left-0 right-0 max-w-[480px] mx-auto bg-white border-t flex justify-around py-2 text-[11px] text-gray-500">
    <a href="/gerant/dashboard">Dashboard</a>
    <a href="/gerant/apprenants">Apprenants</a>
    <a href="/gerant/campagnes/create">Campagnes</a>
  </nav>
<?php endif; ?>
</div>
</body>
</html>
