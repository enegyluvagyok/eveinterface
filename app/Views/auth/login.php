<?php $title = t('auth.login'); require dirname(__DIR__) . '/layouts/header.php'; ?>
<div class="flex justify-center pb-8">
  <img src="/assets/img/gewiss-guard-logo.png" alt="Gewiss Guard Security" class="h-40 w-auto">
</div>
<div class="mx-auto max-w-md rounded-2xl border border-brand-ink-light bg-brand-panel p-8">
<h1 class="text-2xl font-bold"><?= t('auth.login') ?></h1>
<?php foreach ($_SESSION['_errors'] ?? [] as $error): ?><p class="mt-4 rounded bg-red-950 p-3 text-red-200"><?= e($error) ?></p><?php endforeach; ?>
<form class="mt-6 space-y-5" method="post" action="/login"><?= csrf_field() ?>
<label class="block"><span class="text-sm"><?= t('common.email') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" type="email" name="email" value="<?= old('email') ?>" required></label>
<label class="block"><span class="text-sm"><?= t('common.password') ?></span><input class="mt-2 w-full rounded border border-brand-ink-light bg-brand-panel-strong px-4 py-3" type="password" name="password" required></label>
<button class="w-full rounded bg-brand-red px-4 py-3 font-semibold text-white hover:bg-brand-red-dark"><?= t('auth.login') ?></button>
</form>
<p class="mt-4 text-center text-sm"><a href="/forgot-password" class="text-brand-gold hover:underline"><?= t('auth.forgot_password_link') ?></a></p>
</div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
