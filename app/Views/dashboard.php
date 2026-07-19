<?php $title = t('nav.dashboard'); require __DIR__ . '/layouts/header.php'; ?>
<div class="rounded-2xl border border-brand-ink-light bg-brand-panel p-8">
<p class="text-sm uppercase tracking-widest text-brand-gold"><?= t('dashboard.welcome') ?></p>
<h1 class="mt-2 text-3xl font-bold"><?= e($user['name'] ?? '') ?></h1>
<p class="mt-4 text-slate-300"><?= t('dashboard.logged_in_label') ?> <?= e($user['email'] ?? '') ?> (<?= t('role.' . ($user['role'] ?? '')) ?>)</p>
</div>
<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
  <a href="/employees" class="rounded-2xl border border-brand-ink-light bg-brand-panel p-6 hover:border-brand-red">
    <h2 class="text-lg font-semibold text-brand-gold"><?= t('nav.employees') ?></h2>
    <p class="mt-2 text-sm text-slate-400"><?= t('dashboard.employees_desc') ?></p>
  </a>
  <?php if (($user['role'] ?? '') === 'admin'): ?>
  <a href="/admin/users" class="rounded-2xl border border-brand-ink-light bg-brand-panel p-6 hover:border-brand-red">
    <h2 class="text-lg font-semibold text-brand-gold"><?= t('nav.users') ?></h2>
    <p class="mt-2 text-sm text-slate-400"><?= t('dashboard.users_desc') ?></p>
  </a>
  <a href="/admin/contractors" class="rounded-2xl border border-brand-ink-light bg-brand-panel p-6 hover:border-brand-red">
    <h2 class="text-lg font-semibold text-brand-gold"><?= t('nav.contractors') ?></h2>
    <p class="mt-2 text-sm text-slate-400"><?= t('dashboard.contractors_desc') ?></p>
  </a>
  <a href="/admin/subcontractors" class="rounded-2xl border border-brand-ink-light bg-brand-panel p-6 hover:border-brand-red">
    <h2 class="text-lg font-semibold text-brand-gold"><?= t('nav.subcontractors') ?></h2>
    <p class="mt-2 text-sm text-slate-400"><?= t('dashboard.subcontractors_desc') ?></p>
  </a>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/layouts/footer.php'; ?>
