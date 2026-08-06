<?php $title = t('admin_users_edit.title_tag'); require dirname(__DIR__, 2) . '/layouts/header.php'; ?>
<div class="mx-auto max-w-md rounded-2xl border border-brand-ink-light bg-brand-panel p-8">
<h1 class="text-2xl font-bold"><?= t('admin_users_edit.title_tag') ?></h1>
<p class="mt-2 text-sm text-slate-400"><?= e($editUser['name']) ?> · <?= e($editUser['email']) ?></p>
<?php require dirname(__DIR__, 2) . '/partials/flash.php'; ?>
<form class="mt-6 space-y-5" method="post" action="/admin/users/edit"><?= csrf_field() ?>
<input type="hidden" name="id" value="<?= e($editUser['id']) ?>">
<label class="block"><span class="text-sm"><?= t('common.phone') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" type="tel" name="phone" value="<?= e($editUser['phone'] ?? '') ?>" placeholder="+36301234567"></label>
<label class="block"><span class="text-sm"><?= t('admin_users.col_role') ?></span>
  <select class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" name="role">
    <option value="user" <?= $editUser['role'] === 'user' ? 'selected' : '' ?>><?= t('role.user') ?></option>
    <option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>><?= t('role.admin') ?></option>
  </select>
</label>
<div class="grid gap-4 sm:grid-cols-2">
  <?php
    $scopeItems = $contractors;
    $scopeSelectedIds = $selectedContractorIds;
    $scopeFieldName = 'contractor_ids';
    $scopeLegend = t('user_form.contractors_legend');
    $scopeEmptyText = t('user_form.no_contractors');
    $scopeSearchPlaceholder = t('user_form.scope_search_placeholder');
    require dirname(__DIR__, 2) . '/partials/scope_picker.php';
  ?>
  <?php
    $scopeItems = $subcontractors;
    $scopeSelectedIds = $selectedSubcontractorIds;
    $scopeFieldName = 'subcontractor_ids';
    $scopeLegend = t('user_form.subcontractors_legend');
    $scopeEmptyText = t('user_form.no_subcontractors');
    $scopeSearchPlaceholder = t('user_form.scope_search_placeholder');
    require dirname(__DIR__, 2) . '/partials/scope_picker.php';
  ?>
</div>
<p class="text-xs text-slate-500"><?= t('user_form.scope_note') ?></p>
<button class="w-full rounded bg-brand-red px-4 py-3 font-semibold text-white hover:bg-brand-red-dark"><?= t('user_form.submit_save') ?></button>
</form></div>
<script src="/assets/js/scope-picker.js" defer></script>
<?php require dirname(__DIR__, 2) . '/layouts/footer.php'; ?>
