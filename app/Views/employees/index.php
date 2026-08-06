<?php $title = t('nav.employees'); require dirname(__DIR__) . '/layouts/header.php'; ?>
<div class="flex items-center justify-between">
  <h1 class="text-2xl font-bold"><?= t('nav.employees') ?></h1>
  <a href="/employees/create" class="rounded bg-brand-red px-4 py-2 font-semibold text-white hover:bg-brand-red-dark"><?= t('employees.new') ?></a>
</div>
<?php require dirname(__DIR__) . '/partials/flash.php'; ?>
<form method="get" action="/employees" class="mt-6 grid gap-4 rounded-2xl border border-brand-ink-light bg-brand-panel p-6 sm:grid-cols-2 lg:grid-cols-7">
  <label class="block"><span class="text-xs text-slate-400"><?= t('employees.filter_date_from') ?></span>
    <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" class="mt-1 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-3 py-2 text-sm">
  </label>
  <label class="block"><span class="text-xs text-slate-400"><?= t('employees.filter_date_to') ?></span>
    <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" class="mt-1 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-3 py-2 text-sm">
  </label>
  <label class="block"><span class="text-xs text-slate-400"><?= t('employees.contractor_label') ?></span>
    <select name="contractor_id" class="mt-1 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-3 py-2 text-sm">
      <option value=""><?= t('employees.filter_all') ?></option>
      <?php foreach ($contractors as $c): ?>
        <option value="<?= e($c['id']) ?>" <?= (string)($filters['contractor_id'] ?? '') === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label class="block"><span class="text-xs text-slate-400"><?= t('employees.subcontractor_label') ?></span>
    <select name="subcontractor_id" class="mt-1 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-3 py-2 text-sm">
      <option value=""><?= t('employees.filter_all') ?></option>
      <?php foreach ($subcontractors as $s): ?>
        <option value="<?= e($s['id']) ?>" <?= (string)($filters['subcontractor_id'] ?? '') === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label class="block"><span class="text-xs text-slate-400"><?= t('employees.filter_search') ?></span>
    <input type="text" name="q" list="employee-suggestions" value="<?= e($filters['q'] ?? '') ?>" placeholder="<?= t('employees.filter_search_placeholder') ?>" class="mt-1 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-3 py-2 text-sm">
    <datalist id="employee-suggestions">
      <?php foreach ($suggestions as $s): ?><option value="<?= e($s) ?>"><?php endforeach; ?>
    </datalist>
  </label>
  <label class="block"><span class="text-xs text-slate-400"><?= t('employees.filter_medical_fitness') ?></span>
    <select name="medical_fitness_status" class="mt-1 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-3 py-2 text-sm">
      <option value=""><?= t('employees.filter_all') ?></option>
      <option value="valid" <?= (string)($filters['medical_fitness_status'] ?? '') === 'valid' ? 'selected' : '' ?>><?= t('employees.medical_fitness_valid') ?></option>
      <option value="expired" <?= (string)($filters['medical_fitness_status'] ?? '') === 'expired' ? 'selected' : '' ?>><?= t('employees.medical_fitness_expired') ?></option>
    </select>
  </label>
  <label class="block"><span class="text-xs text-slate-400"><?= t('employees.filter_card_color') ?></span>
    <select name="card_color" class="mt-1 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-3 py-2 text-sm">
      <option value=""><?= t('employees.filter_all') ?></option>
      <?php foreach (\App\Models\Employee::CARD_COLORS as $color): ?>
        <option value="<?= e($color) ?>" <?= (string)($filters['card_color'] ?? '') === $color ? 'selected' : '' ?>><?= t('employees.card_color_' . $color) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <div class="flex items-end gap-2 lg:col-span-7">
    <button class="rounded bg-brand-red px-4 py-2 text-sm font-semibold text-white hover:bg-brand-red-dark"><?= t('employees.filter_submit') ?></button>
    <a href="/employees" class="rounded border border-brand-ink-light px-4 py-2 text-sm hover:border-brand-red"><?= t('employees.filter_clear') ?></a>
  </div>
</form>
<div class="mt-6 overflow-x-auto rounded-2xl border border-brand-ink-light">
  <table class="w-full text-left text-sm">
    <thead class="bg-brand-panel-strong text-slate-400">
      <tr>
        <th class="px-4 py-3"><?= t('employees.col_photo') ?></th>
        <th class="px-4 py-3"><?= t('common.name') ?></th>
        <th class="px-4 py-3"><?= t('employees.contractor_label') ?></th>
        <th class="px-4 py-3"><?= t('employees.subcontractor_label') ?></th>
        <th class="px-4 py-3"><?= t('employees.col_idcard') ?></th>
        <th class="px-4 py-3"><?= t('employees.col_medical_fitness') ?></th>
        <th class="px-4 py-3"><?= t('employees.col_card_color') ?></th>
        <th class="px-4 py-3"><?= t('employees.col_created_by') ?></th>
        <th class="px-4 py-3"><?= t('employees.col_status') ?></th>
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($employees as $emp): ?>
        <tr class="border-t border-brand-ink-light">
          <td class="px-4 py-3">
            <?php if ($emp['avatar']): ?>
              <img src="/employees/photo?path=<?= e(basename($emp['avatar'])) ?>" alt="" class="h-10 w-10 rounded-full object-cover">
            <?php else: ?>
              <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-panel-strong text-xs text-slate-500">—</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3"><?= e($emp['fullname']) ?></td>
          <td class="px-4 py-3"><?= e($emp['contractor_name']) ?></td>
          <td class="px-4 py-3"><?= e($emp['subcontractor_name']) ?></td>
          <td class="px-4 py-3"><?= e($emp['idcard']) ?></td>
          <td class="px-4 py-3">
            <?php if ($emp['medical_fitness_until']): ?>
              <?php $isValid = $emp['medical_fitness_until'] >= date('Y-m-d'); ?>
              <span class="rounded px-2 py-1 <?= $isValid ? 'bg-emerald-950 text-emerald-300' : 'bg-red-950 text-red-300' ?>"><?= e($emp['medical_fitness_until']) ?></span>
            <?php else: ?>
              <span class="text-slate-500">—</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <?php if ($emp['card_color']): ?>
              <?php $cardColorClasses = ['red' => 'bg-red-950 text-red-300', 'green' => 'bg-emerald-950 text-emerald-300', 'blue' => 'bg-blue-950 text-blue-300']; ?>
              <span class="rounded px-2 py-1 <?= $cardColorClasses[$emp['card_color']] ?? '' ?>"><?= t('employees.card_color_' . $emp['card_color']) ?></span>
            <?php else: ?>
              <span class="text-slate-500">—</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 text-slate-400"><?= e($emp['created_by_name']) ?></td>
          <td class="px-4 py-3">
            <?php if ($emp['imported_at']): ?>
              <span class="rounded bg-emerald-950 px-2 py-1 text-emerald-300"><?= t('employees.status_imported') ?></span>
            <?php elseif (!$emp['photo']): ?>
              <span class="rounded bg-red-950 px-2 py-1 text-red-300"><?= t('employees.status_incomplete') ?></span>
            <?php else: ?>
              <span class="rounded bg-brand-gold/20 px-2 py-1 text-brand-gold"><?= t('employees.status_pending') ?></span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <?php if (!$emp['imported_at']): ?>
              <div class="flex items-center gap-3">
                <a href="/employees/edit?id=<?= e($emp['id']) ?>" class="text-brand-gold hover:underline"><?= t('admin_users.edit_link') ?></a>
                <form method="post" action="/employees/delete" data-confirm="<?= e(t('employees.delete_confirm')) ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= e($emp['id']) ?>">
                  <button type="submit" class="text-red-400 hover:text-red-300 hover:underline"><?= t('employees.delete_link') ?></button>
                </form>
              </div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$employees): ?>
        <tr><td class="px-4 py-6 text-slate-400" colspan="10">
          <?= array_filter([$filters['date_from'] ?? null, $filters['date_to'] ?? null, $filters['contractor_id'] ?? null, $filters['subcontractor_id'] ?? null, $filters['q'] ?? null, $filters['medical_fitness_status'] ?? null, $filters['card_color'] ?? null])
              ? t('employees.empty_filtered') : t('employees.empty_none') ?>
        </td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php if ($total > 0): ?>
  <?php
    $pageQuery = array_filter([
        'date_from' => $filters['date_from'] ?? null,
        'date_to' => $filters['date_to'] ?? null,
        'contractor_id' => $filters['contractor_id'] ?? null,
        'subcontractor_id' => $filters['subcontractor_id'] ?? null,
        'q' => $filters['q'] ?? null,
        'medical_fitness_status' => $filters['medical_fitness_status'] ?? null,
        'card_color' => $filters['card_color'] ?? null,
    ], fn($v) => $v !== null && $v !== '');
    $pageUrl = fn(int $p): string => '/employees?' . http_build_query($pageQuery + ['page' => $p]);
    $rangeFrom = ($page - 1) * $perPage + 1;
    $rangeTo = min($page * $perPage, $total);
    $windowStart = max(1, $page - 2);
    $windowEnd = min($totalPages, $page + 2);
  ?>
  <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm">
    <span class="text-slate-400"><?= e(sprintf(t('employees.pagination_range'), $rangeFrom, $rangeTo, $total)) ?></span>
    <?php if ($totalPages > 1): ?>
      <div class="flex items-center gap-1">
        <?php if ($page > 1): ?>
          <a href="<?= e($pageUrl($page - 1)) ?>" class="rounded border border-brand-ink-light px-3 py-1.5 hover:border-brand-red"><?= t('employees.pagination_prev') ?></a>
        <?php endif; ?>
        <?php if ($windowStart > 1): ?>
          <a href="<?= e($pageUrl(1)) ?>" class="rounded border border-brand-ink-light px-3 py-1.5 hover:border-brand-red">1</a>
          <?php if ($windowStart > 2): ?><span class="px-1 text-slate-500">…</span><?php endif; ?>
        <?php endif; ?>
        <?php for ($p = $windowStart; $p <= $windowEnd; $p++): ?>
          <a href="<?= e($pageUrl($p)) ?>" class="rounded px-3 py-1.5 <?= $p === $page ? 'bg-brand-red text-white' : 'border border-brand-ink-light hover:border-brand-red' ?>"><?= $p ?></a>
        <?php endfor; ?>
        <?php if ($windowEnd < $totalPages): ?>
          <?php if ($windowEnd < $totalPages - 1): ?><span class="px-1 text-slate-500">…</span><?php endif; ?>
          <a href="<?= e($pageUrl($totalPages)) ?>" class="rounded border border-brand-ink-light px-3 py-1.5 hover:border-brand-red"><?= $totalPages ?></a>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
          <a href="<?= e($pageUrl($page + 1)) ?>" class="rounded border border-brand-ink-light px-3 py-1.5 hover:border-brand-red"><?= t('employees.pagination_next') ?></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
