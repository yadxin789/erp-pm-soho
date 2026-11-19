{{-- 通用表单防护：提交一次即锁定按钮，避免重复提交；成功/失败提示自动淡出 --}}
<script>
(function () {
    if (window.__ddwlFormGuard) { return; }
    window.__ddwlFormGuard = true;

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) { return; }
        if (form.dataset.noGuard === '1') { return; }

        // 让浏览器先完成原生校验
        if (typeof form.checkValidity === 'function' && !form.checkValidity()) { return; }

        var buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        window.setTimeout(function () {
            buttons.forEach(function (btn) {
                if (btn.dataset.loadingSet === '1') { return; }
                btn.dataset.loadingSet = '1';
                btn.dataset.originalText = btn.innerHTML;
                btn.disabled = true;
                btn.classList.add('disabled');
                if (btn.tagName === 'BUTTON') {
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>处理中…';
                } else {
                    btn.value = '处理中…';
                }
            });
        }, 0);
    }, true);

    // 提示条 6 秒后自动收起
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.alert-dismissible.fade.show').forEach(function (alert) {
            window.setTimeout(function () {
                if (window.bootstrap && window.bootstrap.Alert) {
                    window.bootstrap.Alert.getOrCreateInstance(alert).close();
                } else {
                    alert.classList.remove('show');
                }
            }, 6000);
        });
    });
})();
</script>
