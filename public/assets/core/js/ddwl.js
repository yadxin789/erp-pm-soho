/* DDWL 共享交互（T0-10）。只做两件事：
   1) 把 :user-invalid 的视觉态与 aria-invalid 的语义态对齐（含 live region 播报）
   2) 原生 <dialog> 的打开/关闭兜底（优先走声明式 command/commandfor） */
(function () {
  'use strict';

  var live = document.getElementById('dw-live');

  function syncField(el) {
    var errId = el.getAttribute('data-error');
    if (!errId) return;
    var err = document.getElementById(errId);
    if (!err) return;

    var invalid = false;
    try { invalid = el.matches(':user-invalid'); } catch (e) { invalid = false; }

    el.setAttribute('aria-invalid', invalid ? 'true' : 'false');

    var ids = (el.getAttribute('aria-describedby') || '').split(' ').filter(Boolean);
    ids = ids.filter(function (id) { return id !== errId; });
    if (invalid) ids.push(errId);
    if (ids.length) el.setAttribute('aria-describedby', ids.join(' '));
    else el.removeAttribute('aria-describedby');

    err.style.display = invalid ? 'block' : 'none';
    if (invalid && live) live.textContent = err.textContent.trim();
  }

  Array.prototype.forEach.call(document.querySelectorAll('[data-error]'), function (el) {
    ['blur', 'change', 'input', 'invalid'].forEach(function (ev) {
      el.addEventListener(ev, function () { syncField(el); });
    });
    syncField(el);
  });

  document.addEventListener('click', function (e) {
    var opener = e.target.closest('[data-dialog-open]');
    if (opener) {
      var dlg = document.getElementById(opener.getAttribute('data-dialog-open'));
      if (dlg && typeof dlg.showModal === 'function') { e.preventDefault(); dlg.showModal(); }
      return;
    }
    var closer = e.target.closest('[data-dialog-close]');
    if (closer) {
      var d = closer.closest('dialog');
      if (d) d.close(closer.value || '');
    }
  });
})();