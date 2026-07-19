<?php $title = t('home.title_tag'); require __DIR__ . '/layouts/header.php'; ?>
<section class="py-16">
  <div class="flex items-start gap-8">
    <img src="/assets/img/gewiss-guard-logo.png" alt="Gewiss Guard Security" class="h-32 w-auto shrink-0 md:h-44">
    <div>
      <p class="mb-3 text-sm font-semibold uppercase tracking-[.3em] text-brand-gold">EVE · Gewiss Guard Security</p>
      <h1 class="text-4xl font-bold tracking-tight md:text-6xl"><?= t('home.title') ?></h1>
    </div>
  </div>
  <p class="mt-6 max-w-xl text-lg text-slate-300"><?= t('home.description') ?></p>
  <div class="mt-8 flex gap-4"><a class="rounded bg-brand-red px-5 py-3 font-semibold text-white hover:bg-brand-red-dark" href="/login"><?= t('auth.login') ?></a></div>
</section>
<?php require __DIR__ . '/layouts/footer.php'; ?>
