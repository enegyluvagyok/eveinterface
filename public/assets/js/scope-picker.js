(function () {
  function parseJson(el) {
    if (!el) return [];
    try { return JSON.parse(el.textContent || '[]'); } catch (e) { return []; }
  }

  function initPicker(root) {
    var fieldName = root.dataset.fieldName;
    var input = root.querySelector('.scope-picker-input');
    var dropdown = root.querySelector('.scope-picker-dropdown');
    var chips = root.querySelector('.scope-picker-chips');
    if (!fieldName || !input || !dropdown || !chips) return;

    var options = parseJson(root.querySelector('.scope-picker-options'));
    var selected = new Set(parseJson(root.querySelector('.scope-picker-selected')));
    var activeIndex = -1;
    var currentMatches = [];

    function optionById(id) {
      for (var i = 0; i < options.length; i++) if (options[i].id === id) return options[i];
      return null;
    }

    function renderChips() {
      chips.innerHTML = '';
      selected.forEach(function (id) {
        var opt = optionById(id);
        if (!opt) return;

        var chip = document.createElement('span');
        chip.className = 'inline-flex max-w-full items-center gap-2 rounded-lg border border-brand-ink-light bg-brand-panel px-3 py-1.5 text-xs text-slate-100';

        var label = document.createElement('span');
        label.textContent = opt.name + ' (#' + opt.id + ')';
        chip.appendChild(label);

        var remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'text-slate-400 hover:text-brand-red';
        remove.setAttribute('aria-label', 'Remove');
        remove.textContent = '×';
        remove.addEventListener('click', function () {
          selected.delete(id);
          renderChips();
          input.focus();
        });
        chip.appendChild(remove);

        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = fieldName + '[]';
        hidden.value = String(id);
        chip.appendChild(hidden);

        chips.appendChild(chip);
      });
    }

    function closeDropdown() {
      dropdown.classList.add('hidden');
      dropdown.innerHTML = '';
      activeIndex = -1;
      currentMatches = [];
    }

    function highlight(index) {
      var items = dropdown.querySelectorAll('[data-option-index]');
      items.forEach(function (item, i) {
        item.classList.toggle('bg-brand-panel-strong', i === index);
      });
      activeIndex = index;
    }

    function selectOption(opt) {
      selected.add(opt.id);
      renderChips();
      input.value = '';
      closeDropdown();
      input.focus();
    }

    function openDropdown() {
      var query = input.value.trim().toLowerCase();
      currentMatches = options.filter(function (opt) {
        if (selected.has(opt.id)) return false;
        if (query === '') return true;
        return opt.name.toLowerCase().indexOf(query) !== -1 || String(opt.id).indexOf(query) !== -1;
      }).slice(0, 30);

      dropdown.innerHTML = '';
      if (!currentMatches.length) {
        closeDropdown();
        return;
      }
      currentMatches.forEach(function (opt, index) {
        var item = document.createElement('button');
        item.type = 'button';
        item.dataset.optionIndex = String(index);
        item.className = 'block w-full px-3 py-2 text-left text-sm hover:bg-brand-panel-strong';
        item.textContent = opt.name + ' (#' + opt.id + ')';
        item.addEventListener('mousedown', function (e) {
          e.preventDefault();
          selectOption(opt);
        });
        dropdown.appendChild(item);
      });
      dropdown.classList.remove('hidden');
      activeIndex = -1;
    }

    input.addEventListener('input', openDropdown);
    input.addEventListener('focus', openDropdown);

    input.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (dropdown.classList.contains('hidden')) { openDropdown(); return; }
        highlight(Math.min(activeIndex + 1, currentMatches.length - 1));
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        highlight(Math.max(activeIndex - 1, 0));
      } else if (e.key === 'Enter') {
        if (!dropdown.classList.contains('hidden') && currentMatches.length) {
          e.preventDefault();
          selectOption(currentMatches[activeIndex >= 0 ? activeIndex : 0]);
        }
      } else if (e.key === 'Escape') {
        closeDropdown();
      } else if (e.key === 'Backspace' && input.value === '' && selected.size) {
        var last = Array.from(selected).pop();
        selected.delete(last);
        renderChips();
      }
    });

    document.addEventListener('click', function (e) {
      if (!root.contains(e.target)) closeDropdown();
    });

    renderChips();
  }

  document.querySelectorAll('[data-scope-picker]').forEach(initPicker);
})();
