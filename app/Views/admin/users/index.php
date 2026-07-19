<?php $title = t('nav.users'); require dirname(__DIR__, 2) . '/layouts/header.php'; ?>
<div class="flex items-center justify-between">
  <h1 class="text-2xl font-bold"><?= t('nav.users') ?></h1>
  <a href="/admin/users/create" class="rounded bg-brand-red px-4 py-2 font-semibold text-white hover:bg-brand-red-dark"><?= t('admin_users.new') ?></a>
</div>
<?php require dirname(__DIR__, 2) . '/partials/flash.php'; ?>
<?php if ($inviteLink = $_SESSION['_invite_link'] ?? null): ?>
  <div class="mb-6 rounded-2xl border border-brand-gold/40 bg-brand-gold/10 p-4 text-sm">
    <p class="text-brand-gold"><?= t('user_form.invite_link_fallback') ?></p>
    <a href="<?= e($inviteLink) ?>" class="mt-1 block break-all text-slate-200 hover:underline"><?= e($inviteLink) ?></a>
  </div>
<?php endif; ?>
<div class="mt-6 overflow-x-auto rounded-2xl border border-brand-ink-light">
  <table class="w-full text-left text-sm">
    <thead class="bg-brand-panel-strong text-slate-400">
      <tr><th class="px-4 py-3"><?= t('common.name') ?></th><th class="px-4 py-3"><?= t('common.email') ?></th><th class="px-4 py-3"><?= t('common.phone') ?></th><th class="px-4 py-3"><?= t('admin_users.col_role') ?></th><th class="px-4 py-3"><?= t('admin_users.col_created') ?></th><th class="px-4 py-3"></th></tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr class="border-t border-brand-ink-light">
          <td class="px-4 py-3"><?= e($u['name']) ?></td>
          <td class="px-4 py-3"><?= e($u['email']) ?></td>
          <td class="px-4 py-3 text-slate-400"><?= e($u['phone'] ?? '') ?: '—' ?></td>
          <td class="px-4 py-3"><span class="rounded bg-brand-gold/20 px-2 py-1 text-brand-gold"><?= t('role.' . $u['role']) ?></span></td>
          <td class="px-4 py-3 text-slate-400"><?= e($u['created_at']) ?></td>
          <td class="px-4 py-3"><a href="/admin/users/edit?id=<?= e($u['id']) ?>" class="text-brand-gold hover:underline"><?= t('admin_users.edit_link') ?></a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$users): ?><tr><td class="px-4 py-6 text-slate-400" colspan="6"><?= t('admin_users.empty') ?></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require dirname(__DIR__, 2) . '/layouts/footer.php'; ?>
