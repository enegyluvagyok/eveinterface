<!doctype html>
<html lang="<?= e(app('locale')) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= e(\App\Core\Csrf::token()) ?>">
  <title><?= e($title ?? config('app.name')) ?></title>
  <link rel="icon" href="/assets/img/eve-logo.png">
  <link rel="stylesheet" href="/assets/css/app.css">
  <script src="/assets/js/loader.js" defer></script>
</head>
<body class="flex min-h-screen flex-col bg-brand-ink text-slate-100">
<?php
$authUser = \App\Core\Auth::check() ? \App\Core\Auth::user() : null;
$currentPath = urlencode($_SERVER['REQUEST_URI'] ?? '/');
$locales = ['hu' => 'HU', 'en' => 'EN', 'zh' => '中文'];
?>
<nav class="border-b border-brand-ink-light bg-brand-panel-strong">
  <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
    <a href="/" class="flex items-center gap-3">
      <img src="/assets/img/eve-logo.png" alt="EVE" class="h-8 w-auto">
      <span class="hidden font-semibold tracking-wide text-slate-100 sm:inline">Gewiss Gatepass Interface</span>
    </a>
    <div class="flex items-center gap-4 text-sm">
      <?php if ($authUser): ?>
        <a href="/dashboard" class="hover:text-brand-gold"><?= t('nav.dashboard') ?></a>
        <a href="/employees" class="hover:text-brand-gold"><?= t('nav.employees') ?></a>
        <?php if (($authUser['role'] ?? '') === 'admin'): ?>
          <a href="/admin/users" class="hover:text-brand-gold"><?= t('nav.users') ?></a>
          <a href="/admin/contractors" class="hover:text-brand-gold"><?= t('nav.contractors') ?></a>
          <a href="/admin/subcontractors" class="hover:text-brand-gold"><?= t('nav.subcontractors') ?></a>
        <?php endif; ?>
        <form method="post" action="/logout"><?= csrf_field() ?><button class="hover:text-brand-gold"><?= t('nav.logout') ?></button></form>
      <?php else: ?>
        <a href="/login" class="rounded bg-brand-red px-3 py-2 font-medium text-white hover:bg-brand-red-dark"><?= t('auth.login') ?></a>
      <?php endif; ?>
      <div class="flex items-center gap-2 border-l border-brand-ink-light pl-4 text-xs">
        <?php foreach ($locales as $code => $label): ?>
          <a href="/lang?to=<?= e($code) ?>&redirect=<?= $currentPath ?>" class="<?= app('locale') === $code ? 'font-semibold text-brand-gold' : 'text-slate-400 hover:text-brand-gold' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</nav>
<main class="mx-auto w-full max-w-6xl flex-1 px-6 py-12">
