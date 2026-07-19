<?php $title = t('nav.contractors'); require dirname(__DIR__, 2) . '/layouts/header.php'; ?>
<h1 class="text-2xl font-bold"><?= t('nav.contractors') ?></h1>
<?php require dirname(__DIR__, 2) . '/partials/flash.php'; ?>
<div class="grid gap-8 md:grid-cols-[2fr_1fr]">
  <div class="overflow-x-auto rounded-2xl border border-brand-ink-light">
    <table class="w-full text-left text-sm">
      <thead class="bg-brand-panel-strong text-slate-400"><tr><th class="px-4 py-3"><?= t('common.id') ?></th><th class="px-4 py-3"><?= t('common.name') ?></th></tr></thead>
      <tbody>
        <?php foreach ($contractors as $c): ?>
          <tr class="border-t border-brand-ink-light"><td class="px-4 py-3"><?= e($c['id']) ?></td><td class="px-4 py-3"><?= e($c['name']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$contractors): ?><tr><td class="px-4 py-6 text-slate-400" colspan="2"><?= t('admin_contractors.empty') ?></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="rounded-2xl border border-brand-ink-light bg-brand-panel p-6">
    <h2 class="font-semibold text-brand-gold"><?= t('admin_contractors.new_heading') ?></h2>
    <form class="mt-4 space-y-4" method="post" action="/admin/contractors"><?= csrf_field() ?>
    <label class="block"><span class="text-sm"><?= t('common.id') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" type="number" min="1" name="id" value="<?= old('id') ?>" required></label>
    <label class="block"><span class="text-sm"><?= t('common.name') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" name="name" value="<?= old('name') ?>" required></label>
    <button class="w-full rounded bg-brand-red px-4 py-3 font-semibold text-white hover:bg-brand-red-dark"><?= t('admin_contractors.submit') ?></button>
    </form>
  </div>
</div>
<?php require dirname(__DIR__, 2) . '/layouts/footer.php'; ?>
