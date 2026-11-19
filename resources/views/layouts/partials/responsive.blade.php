{{-- 通用窄屏适配：把未包在 .table-responsive 里的表格自动补上滚动容器，避免手机端横向溢出 --}}
<script>
(function () {
    if (window.__ddwlResponsive) { return; }
    window.__ddwlResponsive = true;

    function wrapTables(root) {
        var scope = root || document;
        var tables = scope.querySelectorAll('table');
        tables.forEach(function (table) {
            if (table.closest('.table-responsive')) { return; }
            if (table.dataset.noResponsive === '1') { return; }
            var wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            wrapper.setAttribute('data-auto-responsive', '1');
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        wrapTables(document);
        setupCoreSidebar();
    });

    /* Core 平台：窄屏把固定侧栏改成可滑出的抽屉（含遮罩与关闭） */
    function setupCoreSidebar() {
        var sidebar = document.querySelector('.dw-nav') || document.querySelector('.dw-sidebar');
        var strip = document.querySelector('.dw-strip');
        if (!sidebar || !strip) { return; }
        if (!window.matchMedia || !window.matchMedia('(max-width: 991.98px)').matches) { return; }

        var openClass = sidebar.classList.contains('dw-nav') ? 'dw-nav-open' : 'dw-sidebar-open';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'dw-menu-btn';
        btn.setAttribute('aria-label', '打开菜单');
        btn.setAttribute('aria-expanded', 'false');
        btn.innerHTML = '<i class="fas fa-bars" aria-hidden="true"></i>';
        strip.insertBefore(btn, strip.firstChild);

        var mask = document.createElement('div');
        mask.className = 'dw-sidebar-mask';
        mask.hidden = true;

        function open() {
            sidebar.classList.add(openClass);
            document.body.appendChild(mask);
            mask.hidden = false;
            btn.setAttribute('aria-expanded', 'true');
        }

        function close() {
            sidebar.classList.remove(openClass);
            mask.hidden = true;
            btn.setAttribute('aria-expanded', 'false');
        }

        btn.addEventListener('click', function () {
            if (sidebar.classList.contains(openClass)) { close(); } else { open(); }
        });
        mask.addEventListener('click', close);

        sidebar.addEventListener('click', function (event) {
            if (event.target.closest('a')) { close(); }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { close(); }
        });
    }
})();
</script>
