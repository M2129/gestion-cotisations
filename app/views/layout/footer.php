<?php if (isset($utilisateur) && $utilisateur && $utilisateur['role'] === 'gerant'): ?>
  <!-- Navigation mobile : cachée dès la tablette (md), remplacée par la nav du header -->
  <nav class="md:hidden fixed bottom-0 left-0 right-0 w-full max-w-[480px] mx-auto bg-white border-t flex justify-around py-2 text-[11px] text-gray-500">
    <?php include __DIR__ . '/nav_gerant.php'; ?>
  </nav>
<?php endif; ?>
</div>
</body>
</html>
