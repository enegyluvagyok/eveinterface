<?php $title = t('admin_users.new'); require dirname(__DIR__, 2) . '/layouts/header.php'; ?>
<div class="mx-auto max-w-md rounded-2xl border border-brand-ink-light bg-brand-panel p-8">
<h1 class="text-2xl font-bold"><?= t('admin_users.new') ?></h1>
<?php require dirname(__DIR__, 2) . '/partials/flash.php'; ?>
<form class="mt-6 space-y-5" method="post" action="/admin/users"><?= csrf_field() ?>
<label class="block"><span class="text-sm"><?= t('common.name') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" name="name" value="<?= old('name') ?>" required></label>
<label class="block"><span class="text-sm"><?= t('common.email') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" type="email" name="email" value="<?= old('email') ?>" required></label>
<label class="block"><span class="text-sm"><?= t('common.phone') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" type="tel" name="phone" value="<?= old('phone') ?>" placeholder="+36301234567"></label>
<p class="text-xs text-slate-500"><?= t('user_form.invite_note') ?></p>
<label class="block"><span class="text-sm"><?= t('admin_users.col_role') ?></span>
  <select class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" name="role">
    <option value="user" <?= old('role', 'user') === 'user' ? 'selected' : '' ?>><?= t('role.user') ?></option>
    <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>><?= t('role.admin') ?></option>
  </select>
</label>
<div class="grid gap-4 sm:grid-cols-2">
  <fieldset class="rounded border border-brand-ink-light bg-brand-panel-strong p-4">
    <legend class="px-1 text-sm text-slate-400"><?= t('user_form.contractors_legend') ?></legend>
    <div class="mt-2 max-h-48 space-y-2 overflow-y-auto">
      <?php foreach ($contractors as $c): ?>
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" name="contractor_ids[]" value="<?= e($c['id']) ?>" <?= in_array((int)$c['id'], $_SESSION['_old']['contractorIds'] ?? [], true) ? 'checked' : '' ?>>
          <?= e($c['name']) ?> (#<?= e($c['id']) ?>)
        </label>
      <?php endforeach; ?>
      <?php if (!$contractors): ?><p class="text-xs text-slate-500"><?= t('user_form.no_contractors') ?></p><?php endif; ?>
    </div>
  </fieldset>
  <fieldset class="rounded border border-brand-ink-light bg-brand-panel-strong p-4">
    <legend class="px-1 text-sm text-slate-400"><?= t('user_form.subcontractors_legend') ?></legend>
    <div class="mt-2 max-h-48 space-y-2 overflow-y-auto">
      <?php foreach ($subcontractors as $s): ?>
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" name="subcontractor_ids[]" value="<?= e($s['id']) ?>" <?= in_array((int)$s['id'], $_SESSION['_old']['subcontractorIds'] ?? [], true) ? 'checked' : '' ?>>
          <?= e($s['name']) ?> (#<?= e($s['id']) ?>)
        </label>
      <?php endforeach; ?>
      <?php if (!$subcontractors): ?><p class="text-xs text-slate-500"><?= t('user_form.no_subcontractors') ?></p><?php endif; ?>
    </div>
  </fieldset>
</div>
<p class="text-xs text-slate-500"><?= t('user_form.scope_note') ?></p>
<button class="w-full rounded bg-brand-red px-4 py-3 font-semibold text-white hover:bg-brand-red-dark"><?= t('user_form.submit_create') ?></button>
</form></div>
<?php require dirname(__DIR__, 2) . '/layouts/footer.php'; ?>
