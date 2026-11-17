<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} · 统一入口</title>
    <style>
        :root{
            --brand:#2f6fed; --brand-2:#667eea; --ok:#28a745; --warn:#ffc107;
            --ink:#1f2430; --muted:#6b7280; --line:#e6e8ee; --bg:#f6f7fb;
        }
        *{box-sizing:border-box}
        body{margin:0;font-family:"Microsoft YaHei","Segoe UI",system-ui,-apple-system,sans-serif;
             background:var(--bg);color:var(--ink);min-height:100vh;display:flex;flex-direction:column}
        header{padding:22px 32px;display:flex;align-items:center;justify-content:space-between;
               border-bottom:1px solid var(--line);background:#fff}
        .brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:18px;letter-spacing:.5px}
        .logo{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--brand-2),var(--brand));
              display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px}
        .meta{color:var(--muted);font-size:13px}
        main{flex:1;display:flex;align-items:center;justify-content:center;padding:48px 24px}
        .wrap{width:100%;max-width:1080px}
        h1{margin:0 0 8px;font-size:26px;letter-spacing:.5px}
        p.lead{margin:0 0 32px;color:var(--muted);font-size:15px}
        .cards{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
        @media (max-width:900px){.cards{grid-template-columns:1fr}}
        .card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:26px 24px 22px;
              text-decoration:none;color:inherit;display:flex;flex-direction:column;gap:12px;
              transition:transform .15s ease,box-shadow .15s ease,border-color .15s ease}
        .card:hover,.card:focus-visible{transform:translateY(-3px);box-shadow:0 12px 28px rgba(47,111,237,.12);
              border-color:#cfd9f5;outline:none}
        .icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center}
        .icon svg{width:22px;height:22px;stroke:currentColor;stroke-width:1.7;fill:none}
        .c-erp .icon{background:#eaf1ff;color:var(--brand)}
        .c-pm .icon{background:#eaf7ef;color:var(--ok)}
        .c-soho .icon{background:#fff5e0;color:#b78103}
        .name{font-size:17px;font-weight:700}
        .desc{color:var(--muted);font-size:13px;line-height:1.7;min-height:44px}
        .go{margin-top:auto;font-size:13px;font-weight:600;color:var(--brand);display:flex;align-items:center;gap:6px}
        .go::after{content:"→";transition:transform .15s ease}
        .card:hover .go::after{transform:translateX(3px)}
        footer{padding:18px 32px;color:var(--muted);font-size:12px;border-top:1px solid var(--line);background:#fff;
               display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
    </style>
</head>
<body>
<header>
    <div class="brand"><span class="logo">DW</span><span>{{ config('app.name') }}</span></div>
    <div class="meta">统一身份 · 三板块</div>
</header>

<main>
    <div class="wrap">
        <h1>选择进入的管理板块</h1>
        <p class="lead">三个板块共用一套账号与组织架构；登录后将按你的部门归属自动进入对应板块。</p>

        <div class="cards">
            <a class="card c-erp" href="/erp">
                <span class="icon">
                    <svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20h14V9.5"/><path d="M9.5 20v-5.5h5V20"/></svg>
                </span>
                <span class="name">ERP 电子厂管理</span>
                <span class="desc">销售、采购、生产、库存、质检、物流，以及人事与财务基础台账。</span>
                <span class="go">进入 ERP</span>
            </a>

            <a class="card c-pm" href="/pm">
                <span class="icon">
                    <svg viewBox="0 0 24 24"><rect x="3.5" y="4.5" width="17" height="16" rx="2.5"/><path d="M8 3v3M16 3v3M3.5 9.5h17"/><path d="M8 14h3M8 17h5"/></svg>
                </span>
                <span class="name">研发项目管理</span>
                <span class="desc">项目立项、任务分解、甘特与协同、文件审核流、知识库与 AI 工作台。</span>
                <span class="go">进入 PM</span>
            </a>

            <a class="card c-soho" href="/soho">
                <span class="icon">
                    <svg viewBox="0 0 24 24"><path d="M4 8.5h16l-1.2 11.5H5.2L4 8.5Z"/><path d="M9 8.5V6a3 3 0 0 1 6 0v2.5"/></svg>
                </span>
                <span class="name">多平台电商管理</span>
                <span class="desc">多店铺订单、商品与平台 SKU、库存同步、采购履约、价格与售后、财务报表。</span>
                <span class="go">进入 SOHO</span>
            </a>
        </div>
    </div>
</main>

<footer>
    <span>DDWL 统一管理平台 · 骨架版本（P1 基座）</span>
    <span>Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }}</span>
</footer>
</body>
</html>
