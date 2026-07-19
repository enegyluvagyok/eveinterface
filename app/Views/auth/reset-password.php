<?php $title = t('auth.reset_password_title'); require dirname(__DIR__) . '/layouts/header.php'; ?>
<div class="mx-auto max-w-md rounded-2xl border border-brand-ink-light bg-brand-panel p-8">
<h1 class="text-2xl font-bold"><?= t('auth.reset_password_title') ?></h1>
<?php require dirname(__DIR__) . '/partials/flash.php'; ?>
<form class="mt-6 space-y-5" method="post" action="/reset-password"><?= csrf_field() ?>
<input type="hidden" name="token" value="<?= e($token) ?>">
<label class="block"><span class="text-sm"><?= t('auth.reset_password_new_label') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" type="password" name="password" minlength="12" required></label>
<label class="block"><span class="text-sm"><?= t('auth.reset_password_confirm_label') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" type="password" name="password_confirmation" minlength="12" required></label>
<button class="w-full rounded bg-brand-red px-4 py-3 font-semibold text-white hover:bg-brand-red-dark"><?= t('auth.reset_password_submit') ?></button>
</form>
</div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
