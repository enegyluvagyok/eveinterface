(function () {
  var bar = null;
  var timer = null;
  var width = 0;

  function start() {
    if (bar) return;
    bar = document.createElement('div');
    bar.id = 'page-loader';
    document.body.appendChild(bar);
    width = 0;
    requestAnimationFrame(function () {
      bar.style.opacity = '1';
      bar.style.width = '20%';
    });
    width = 20;
    timer = setInterval(function () {
      width += (90 - width) / 8;
      bar.style.width = width + '%';
    }, 200);
  }

  function reset() {
    if (timer) clearInterval(timer);
    timer = null;
    if (bar) { bar.remove(); bar = null; }
  }

  document.addEventListener('click', function (e) {
    var a = e.target.closest('a[href]');
    if (!a || e.defaultPrevented) return;
    if (a.target === '_blank' || a.hasAttribute('download')) return;
    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var href = a.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:')) return;
    var url;
    try { url = new URL(href, window.location.href); } catch (err) { return; }
    if (url.origin !== window.location.origin) return;
    if (url.href === window.location.href) return;
    start();
  });

  document.addEventListener('submit', function (e) {
    if (e.defaultPrevented) return;
    start();
  });

  window.addEventListener('pageshow', reset);
})();
