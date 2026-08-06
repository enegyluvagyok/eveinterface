<fieldset class="rounded border border-brand-ink-light bg-brand-panel-strong p-4" data-scope-picker data-field-name="<?= e($scopeFieldName) ?>">
  <legend class="px-1 text-sm text-slate-400"><?= e($scopeLegend) ?></legend>
  <?php if ($scopeItems): ?>
    <div class="relative mt-2">
      <input type="text" class="scope-picker-input w-full rounded border border-brand-ink-light bg-brand-panel px-3 py-2 text-sm" placeholder="<?= e($scopeSearchPlaceholder) ?>" autocomplete="off">
      <div class="scope-picker-dropdown absolute z-10 mt-1 hidden max-h-48 w-full overflow-y-auto rounded border border-brand-ink-light bg-brand-panel shadow-lg"></div>
    </div>
    <div class="scope-picker-chips mt-3 flex flex-wrap gap-2"></div>
    <script type="application/json" class="scope-picker-options"><?= json_encode(
        array_map(fn($item) => ['id' => (int)$item['id'], 'name' => (string)$item['name']], $scopeItems),
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?></script>
    <script type="application/json" class="scope-picker-selected"><?= json_encode(
        array_values(array_map('intval', $scopeSelectedIds)),
        JSON_UNESCAPED_UNICODE
    ) ?></script>
    <noscript>
      <div class="mt-2 max-h-48 space-y-2 overflow-y-auto">
        <?php foreach ($scopeItems as $item): ?>
          <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="<?= e($scopeFieldName) ?>[]" value="<?= e($item['id']) ?>" <?= in_array((int)$item['id'], $scopeSelectedIds, true) ? 'checked' : '' ?>>
            <?= e($item['name']) ?> (#<?= e($item['id']) ?>)
          </label>
        <?php endforeach; ?>
      </div>
    </noscript>
  <?php else: ?>
    <p class="mt-2 text-xs text-slate-500"><?= e($scopeEmptyText) ?></p>
  <?php endif; ?>
</fieldset>
