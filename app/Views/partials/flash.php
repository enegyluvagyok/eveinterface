<?php if ($flash = $_SESSION['_flash'] ?? null): ?>
  <p class="mb-6 rounded bg-emerald-950 p-3 text-emerald-200"><?= e($flash) ?></p>
<?php endif; ?>
<?php if ($warning = $_SESSION['_flash_warning'] ?? null): ?>
  <p class="mb-6 rounded bg-brand-gold/20 p-3 text-brand-gold"><?= e($warning) ?></p>
<?php endif; ?>
<?php foreach ($_SESSION['_errors'] ?? [] as $error): ?>
  <p class="mb-6 rounded bg-red-950 p-3 text-red-200"><?= e($error) ?></p>
<?php endforeach; ?>
