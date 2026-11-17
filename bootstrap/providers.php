<?php

use App\Core\Providers\CoreServiceProvider;
use App\Erp\Providers\ErpServiceProvider;
use App\Pm\Providers\PmServiceProvider;
use App\Providers\AppServiceProvider;
use App\Soho\Providers\SohoServiceProvider;

return [
    AppServiceProvider::class,

    // 四模块（顺序：Core → 三业务模块）
    CoreServiceProvider::class,
    ErpServiceProvider::class,
    PmServiceProvider::class,
    SohoServiceProvider::class,
];
