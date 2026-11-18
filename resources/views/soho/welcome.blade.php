<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>多平台电商店铺统一管理系统</title>

    <link rel="stylesheet" href="{{ asset('assets/soho/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/soho/css/bootstrap.min.css') }}">

    <style>
        :root {
            --primary-color: #667eea;
            --primary-dark: #5a6fd6;
            --secondary-color: #f093fb;
            --accent-color: #f5576c;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --bg-gradient-start: #667eea;
            --bg-gradient-end: #764ba2;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Microsoft YaHei', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            color: white;
            padding: 80px 0 100px;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .btn-primary-custom {
            background: white;
            color: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
            color: var(--primary-dark);
            text-decoration: none;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .nav-link {
            color: #374151;
            font-weight: 500;
            margin-left: 20px;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .stat-icon.shops {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-icon.goods {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-icon.orders {
            background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
        }

        .stat-icon.revenue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .feature-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            text-align: center;
            border: none;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(102, 126, 234, 0.15);
            border-color: var(--primary-color);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
        }

        .feature-icon.sync {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .feature-icon.inventory {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .feature-icon.order {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        }

        .feature-icon.analytics {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
        }

        .platform-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            margin-right: 10px;
            margin-bottom: 10px;
            font-size: 0.875rem;
        }

        .platform-taobao {
            background: linear-gradient(135deg, #ff4400 0%, #ff6600 100%);
            color: white;
        }

        .platform-jd {
            background: linear-gradient(135deg, #e71836 0%, #d9001b 100%);
            color: white;
        }

        .platform-pdd {
            background: linear-gradient(135deg, #e02e24 0%, #ff4d4f 100%);
            color: white;
        }

        .platform-amazon {
            background: linear-gradient(135deg, #ff9900 0%, #ffac33 100%);
            color: white;
        }

        .footer {
            background: #1f2937;
            color: #9ca3af;
            padding: 40px 0;
            margin-top: 60px;
        }

        .footer-links a {
            color: #9ca3af;
            margin-right: 20px;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
            text-decoration: none;
        }

        /* 图表卡片样式 */
        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: none;
            height: 100%;
        }

        .chart-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .chart-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f3f4f6;
        }

        .chart-container {
            width: 100%;
            height: 280px;
        }

        /* 统计卡片扩展样式 */
        .stat-card-new {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-card-new:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-icon-new {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }

        .stat-icon-new.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-icon-new.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-icon-new.orange {
            background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
        }

        .stat-icon-new.red {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }

        .stat-icon-new.purple {
            background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%);
        }

        .stat-icon-new.cyan {
            background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
        }

        .stat-info {
            flex: 1;
        }

        .stat-value-new {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .stat-label-new {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .stat-trend {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 4px;
        }

        .stat-trend.up {
            background: #dcfce7;
            color: #16a34a;
        }

        .stat-trend.down {
            background: #fee2e2;
            color: #dc2626;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.75rem;
            }
            .stat-card {
                margin-bottom: 16px;
            }
            .feature-card {
                margin-bottom: 16px;
            }
            .chart-card {
                margin-bottom: 16px;
            }
            .chart-container {
                height: 240px;
            }
            .stat-card-new {
                margin-bottom: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fa fa-shopping-bag me-2"></i>
                得道未来多平台电商店铺统一管理系统
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="#overview">系统概览</a>
                    <a class="nav-link" href="#features">核心功能</a>
                    <a class="nav-link" href="#platforms">支持平台</a>
                    <a class="nav-link" href="{{ route('soho.log.login') }}">
                        <i class="fa fa-sign-in me-1"></i>登录
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero 区域 -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="hero-title">
                        得道未来多平台电商店铺统一管理系统
                    </h1>
                    <p class="hero-subtitle">
                        一站式管理淘宝、京东、拼多多、亚马逊等多平台店铺，实现订单、库存、商品的统一管理与智能同步
                    </p>
                    <div class="flex-wrap gap-3">
                        <a href="#overview" class="btn btn-primary-custom">
                            <i class="fa fa-dashboard me-2"></i>了解更多
                        </a>
                        <a href="{{ route('soho.log.login') }}" class="btn btn-outline-light btn-lg" style="border-color: rgba(255,255,255,0.5);">
                            <i class="fa fa-user-circle me-2"></i>登录
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 d-none d-lg-block">
                    <div class="text-center">
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6">
                            <div class="text-6xl mb-4">🛒</div>
                            <h3 class="text-xl font-semibold mb-2">统一管理</h3>
                            <p class="opacity-80">多平台数据一站式整合</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 统计概览 -->
    <section id="overview" class="py-16 bg-f8fafc">
        <div class="container">
            <div class="row mb-8">
                <div class="col-md-12 text-center">
                    <h2 class="text-2xl font-bold text-gray-900">系统概览</h2>
                    <p class="text-gray-600 mt-2">实时掌握店铺运营数据</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon shops">
                            <i class="fa fa-store text-white fa-xl"></i>
                        </div>
                        <div class="stat-value text-primary">0</div>
                        <div class="stat-label">已接入店铺</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon goods">
                            <i class="fa fa-cube text-white fa-xl"></i>
                        </div>
                        <div class="stat-value text-success">0</div>
                        <div class="stat-label">商品总数</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon orders">
                            <i class="fa fa-shopping-cart text-white fa-xl"></i>
                        </div>
                        <div class="stat-value text-warning">0</div>
                        <div class="stat-label">今日订单</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fa fa-line-chart text-white fa-xl"></i>
                        </div>
                        <div class="stat-value text-info">¥0</div>
                        <div class="stat-label">今日营收</div>
                    </div>
                </div>
            </div>

            <!-- 可视化统计图表 -->
            <div class="row mt-4">
                <!-- 折线图：订单趋势 -->
                <div class="col-md-4">
                    <div class="chart-card">
                        <div class="chart-title">
                            <i class="fa fa-line-chart me-2" style="color: #667eea;"></i>
                            订单趋势（近7日）
                        </div>
                        <div id="lineChart" class="chart-container"></div>
                    </div>
                </div>

                <!-- 柱形图：各平台销量 -->
                <div class="col-md-4">
                    <div class="chart-card">
                        <div class="chart-title">
                            <i class="fa fa-bar-chart me-2" style="color: #11998e;"></i>
                            各平台销量统计
                        </div>
                        <div id="barChart" class="chart-container"></div>
                    </div>
                </div>

                <!-- 饼图：商品分类占比 -->
                <div class="col-md-4">
                    <div class="chart-card">
                        <div class="chart-title">
                            <i class="fa fa-pie-chart me-2" style="color: #fc4a1a;"></i>
                            商品分类占比
                        </div>
                        <div id="pieChart" class="chart-container"></div>
                    </div>
                </div>
            </div>

            <!-- 详细统计卡片 -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="stat-card-new">
                        <div class="stat-icon-new blue">
                            <i class="fa fa-user-plus"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value-new">1,248 <span class="stat-trend up">+12.5%</span></div>
                            <div class="stat-label-new">本月新增订单</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card-new">
                        <div class="stat-icon-new green">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value-new">386 <span class="stat-trend up">+8.2%</span></div>
                            <div class="stat-label-new">活跃用户</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card-new">
                        <div class="stat-icon-new orange">
                            <i class="fa fa-percent"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value-new">24.6% <span class="stat-trend up">+3.1%</span></div>
                            <div class="stat-label-new">订单转化率</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card-new">
                        <div class="stat-icon-new red">
                            <i class="fa fa-yen"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value-new">¥326,580 <span class="stat-trend down">-2.4%</span></div>
                            <div class="stat-label-new">月度营收</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="stat-card-new">
                        <div class="stat-icon-new purple">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value-new">2.8h <span class="stat-trend up">-5.6%</span></div>
                            <div class="stat-label-new">平均发货时长</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card-new">
                        <div class="stat-icon-new cyan">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value-new">96.8% <span class="stat-trend up">+1.2%</span></div>
                            <div class="stat-label-new">订单完成率</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card-new">
                        <div class="stat-icon-new orange">
                            <i class="fa fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value-new">4.8 <span class="stat-trend up">+0.3</span></div>
                            <div class="stat-label-new">用户满意度</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card-new">
                        <div class="stat-icon-new red">
                            <i class="fa fa-exchange"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value-new">1.2% <span class="stat-trend down">-0.5%</span></div>
                            <div class="stat-label-new">售后退款率</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 核心功能 -->
    <section id="features" class="py-16 bg-white">
        <div class="container">
            <div class="row mb-8">
                <div class="col-md-12 text-center">
                    <h2 class="text-2xl font-bold text-gray-900">核心功能</h2>
                    <p class="text-gray-600 mt-2">全方位电商管理解决方案</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon sync">
                            <i class="fa fa-refresh"></i>
                        </div>
                        <h3 class="h5 font-semibold mb-2">数据同步</h3>
                        <p class="text-gray-600 text-sm">自动同步多平台订单、库存、商品数据，实时更新无延迟</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon inventory">
                            <i class="fa fa-archive"></i>
                        </div>
                        <h3 class="h5 font-semibold mb-2">库存管理</h3>
                        <p class="text-gray-600 text-sm">统一库存管理，智能预警，避免超卖缺货</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon order">
                            <i class="fa fa-file-text"></i>
                        </div>
                        <h3 class="h5 font-semibold mb-2">订单管理</h3>
                        <p class="text-gray-600 text-sm">一站式处理多平台订单，支持批量发货、售后处理</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon analytics">
                            <i class="fa fa-bar-chart"></i>
                        </div>
                        <h3 class="h5 font-semibold mb-2">数据分析</h3>
                        <p class="text-gray-600 text-sm">多维度数据报表，销售趋势、库存分析一目了然</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 支持平台 -->
    <section id="platforms" class="py-16 bg-f8fafc">
        <div class="container">
            <div class="row mb-8">
                <div class="col-md-12 text-center">
                    <h2 class="text-2xl font-bold text-gray-900">支持平台</h2>
                    <p class="text-gray-600 mt-2">主流电商平台全覆盖</p>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="bg-white rounded-12 p-8 text-center">
                        <div class="flex flex-wrap justify-center">
                            <span class="platform-badge platform-taobao">
                                <i class="fa fa-shopping-bag me-1"></i>淘宝
                            </span>
                            <span class="platform-badge platform-jd">
                                <i class="fa fa-shopping-cart me-1"></i>京东
                            </span>
                            <span class="platform-badge platform-pdd">
                                <i class="fa fa-gift me-1"></i>拼多多
                            </span>
                            <span class="platform-badge platform-amazon">
                                <i class="fa fa-globe me-1"></i>亚马逊
                            </span>
                        </div>
                        <p class="mt-4 text-gray-500">更多平台接入中...</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA 区域 -->
    <section class="py-16 bg-white">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">开始您的电商统一管理之旅</h2>
                    <p class="text-gray-600 mb-6">立即注册，体验多平台店铺统一管理的便捷与高效</p>
                    <div class="flex-wrap justify-center gap-3">
                        <a href="{{ route('soho.log.login') }}" class="btn btn-primary-custom">
                            <i class="fa fa-user-plus me-2"></i>免费注册
                        </a>
                        <a href="{{ route('soho.log.login') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fa fa-sign-in me-2"></i>登录系统
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 页脚 -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="footer-links">
                        <a href="#overview">系统概览</a>
                        <a href="#features">核心功能</a>
                        <a href="#platforms">支持平台</a>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <p>多平台电商店铺统一管理系统 © {{ date('Y') }}. 保留所有权利.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('assets/soho/js/bootstrap.bundle.min.js') }}"></script>
    <!-- ECharts 用于可视化统计图表 -->
    <script src="{{ asset('assets/soho/js/echarts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ========== 折线图：订单趋势（近7日） ==========
            const lineChart = echarts.init(document.getElementById('lineChart'));
            lineChart.setOption({
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    borderWidth: 0,
                    textStyle: { color: '#fff', fontSize: 12 }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    top: '10%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: ['08-28', '08-29', '08-30', '08-31', '09-01', '09-02', '09-03'],
                    axisLine: { lineStyle: { color: '#e5e7eb' } },
                    axisLabel: { color: '#6b7280', fontSize: 11 }
                },
                yAxis: {
                    type: 'value',
                    axisLine: { show: false },
                    axisTick: { show: false },
                    splitLine: { lineStyle: { color: '#f3f4f6', type: 'dashed' } },
                    axisLabel: { color: '#6b7280', fontSize: 11 }
                },
                series: [{
                    name: '订单数量',
                    type: 'line',
                    data: [120, 182, 191, 234, 290, 330, 310],
                    smooth: true,
                    symbol: 'circle',
                    symbolSize: 6,
                    lineStyle: {
                        width: 3,
                        color: '#667eea'
                    },
                    itemStyle: { color: '#667eea' },
                    areaStyle: {
                        color: {
                            type: 'linear',
                            x: 0, y: 0, x2: 0, y2: 1,
                            colorStops: [
                                { offset: 0, color: 'rgba(102, 126, 234, 0.3)' },
                                { offset: 1, color: 'rgba(102, 126, 234, 0.02)' }
                            ]
                        }
                    }
                }]
            });

            // ========== 柱形图：各平台销量统计 ==========
            const barChart = echarts.init(document.getElementById('barChart'));
            barChart.setOption({
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    borderWidth: 0,
                    textStyle: { color: '#fff', fontSize: 12 }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    top: '10%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: ['淘宝', '京东', '拼多多', '亚马逊'],
                    axisLine: { lineStyle: { color: '#e5e7eb' } },
                    axisLabel: { color: '#6b7280', fontSize: 12 }
                },
                yAxis: {
                    type: 'value',
                    axisLine: { show: false },
                    axisTick: { show: false },
                    splitLine: { lineStyle: { color: '#f3f4f6', type: 'dashed' } },
                    axisLabel: { color: '#6b7280', fontSize: 11 }
                },
                series: [{
                    name: '销量',
                    type: 'bar',
                    data: [420, 310, 280, 180],
                    barWidth: '45%',
                    itemStyle: {
                        borderRadius: [6, 6, 0, 0],
                        color: {
                            type: 'linear',
                            x: 0, y: 0, x2: 0, y2: 1,
                            colorStops: [
                                { offset: 0, color: '#11998e' },
                                { offset: 1, color: '#38ef7d' }
                            ]
                        }
                    }
                }]
            });

            // ========== 饼图：商品分类占比 ==========
            const pieChart = echarts.init(document.getElementById('pieChart'));
            pieChart.setOption({
                tooltip: {
                    trigger: 'item',
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    borderWidth: 0,
                    textStyle: { color: '#fff', fontSize: 12 },
                    formatter: '{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'horizontal',
                    bottom: 0,
                    left: 'center',
                    itemWidth: 10,
                    itemHeight: 10,
                    textStyle: { color: '#6b7280', fontSize: 11 }
                },
                series: [{
                    name: '商品分类',
                    type: 'pie',
                    radius: ['45%', '70%'],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 6,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 14,
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: { show: false },
                    data: [
                        { value: 335, name: '服装', itemStyle: { color: '#667eea' } },
                        { value: 310, name: '数码', itemStyle: { color: '#11998e' } },
                        { value: 234, name: '美妆', itemStyle: { color: '#fc4a1a' } },
                        { value: 135, name: '家居', itemStyle: { color: '#4facfe' } },
                        { value: 148, name: '食品', itemStyle: { color: '#8e2de2' } }
                    ]
                }]
            });

            // 窗口大小变化时，图表自适应调整尺寸
            window.addEventListener('resize', function () {
                lineChart.resize();
                barChart.resize();
                pieChart.resize();
            });
        });
    </script>
</body>
</html>