<?php $title = t('employees.new'); require dirname(__DIR__) . '/layouts/header.php'; ?>
<div class="mx-auto max-w-xl rounded-2xl border border-brand-ink-light bg-brand-panel p-8">
<h1 class="text-2xl font-bold"><?= t('employees.new') ?></h1>
<?php require dirname(__DIR__) . '/partials/flash.php'; ?>
<?php if (!$contractors || !$subcontractors): ?>
  <p class="mt-4 rounded bg-brand-gold/20 p-3 text-brand-gold"><?= t('employees_create.no_scope_warning') ?></p>
<?php endif; ?>
<form class="mt-6 space-y-5" method="post" action="/employees" enctype="multipart/form-data"><?= csrf_field() ?>
<label class="block"><span class="text-sm"><?= t('employees.col_code') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" name="employee_code" value="<?= old('employeeCode') ?>" required></label>
<label class="block"><span class="text-sm"><?= t('employees_create.fullname') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" name="fullname" value="<?= old('fullname') ?>" required></label>
<label class="block"><span class="text-sm"><?= t('employees_create.idcard') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" name="idcard" value="<?= old('idcard') ?>" required></label>
<label class="block"><span class="text-sm"><?= t('employees.contractor_label') ?></span>
  <select class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" name="contractor_id" required>
    <option value=""><?= t('employees_create.select_placeholder') ?></option>
    <?php foreach ($contractors as $c): ?>
      <option value="<?= e($c['id']) ?>" <?= old('contractorId') === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?> (#<?= e($c['id']) ?>)</option>
    <?php endforeach; ?>
  </select>
</label>
<label class="block"><span class="text-sm"><?= t('employees.subcontractor_label') ?></span>
  <select class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" name="subcontractor_id" required>
    <option value=""><?= t('employees_create.select_placeholder') ?></option>
    <?php foreach ($subcontractors as $s): ?>
      <option value="<?= e($s['id']) ?>" <?= old('subcontractorId') === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> (#<?= e($s['id']) ?>)</option>
    <?php endforeach; ?>
  </select>
</label>
<label class="block"><span class="text-sm"><?= t('employees_create.photo') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" type="file" name="photo" accept="image/jpeg,image/png"></label>
<button class="w-full rounded bg-brand-red px-4 py-3 font-semibold text-white hover:bg-brand-red-dark"><?= t('employees_create.submit') ?></button>
</form></div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
