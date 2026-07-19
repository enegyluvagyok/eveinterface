<?php $title = t('auth.forgot_password_title'); require dirname(__DIR__) . '/layouts/header.php'; ?>
<div class="mx-auto max-w-md rounded-2xl border border-brand-ink-light bg-brand-panel p-8">
<h1 class="text-2xl font-bold"><?= t('auth.forgot_password_title') ?></h1>
<p class="mt-2 text-sm text-slate-400"><?= t('auth.forgot_password_intro') ?></p>
<?php require dirname(__DIR__) . '/partials/flash.php'; ?>
<form class="mt-6 space-y-5" method="post" action="/forgot-password"><?= csrf_field() ?>
<label class="block"><span class="text-sm"><?= t('common.email') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" type="email" name="email" value="<?= old('email') ?>" required></label>
<button class="w-full rounded bg-brand-red px-4 py-3 font-semibold text-white hover:bg-brand-red-dark"><?= t('auth.forgot_password_submit') ?></button>
</form>
<p class="mt-4 text-center text-sm"><a href="/login" class="text-brand-gold hover:underline"><?= t('auth.back_to_login') ?></a></p>
</div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
