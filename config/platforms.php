<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 平台配置
    |--------------------------------------------------------------------------
    | 配置各电商平台的API地址、OAuth参数、限流策略等
    */

    'taobao' => [
        'name' => '淘宝',
        'name_en' => 'Taobao',
        'icon' => 'taobao',
        'enabled' => true,
        'api' => [
            'gateway' => 'https://eco.taobao.com/router/rest',
            'timeout' => 30,
            'retry' => 3,
            'retry_delay' => 200,
        ],
        'oauth' => [
            'authorize_url' => 'https://oauth.taobao.com/authorize',
            'token_url' => 'https://oauth.taobao.com/token',
            'scope' => 'user_info,trade,order',
            'response_type' => 'code',
            'grant_type' => 'authorization_code',
        ],
        'webhook' => [
            'enabled' => true,
            'verify_token' => env('TAOBAO_WEBHOOK_VERIFY_TOKEN', ''),
        ],
        'rate_limit' => [
            'enabled' => true,
            'requests_per_minute' => 60,
        ],
    ],

    'jd' => [
        'name' => '京东',
        'name_en' => 'JD',
        'icon' => 'jd',
        'enabled' => true,
        'api' => [
            'gateway' => 'https://api.jd.com/routerjson',
            'timeout' => 30,
            'retry' => 3,
            'retry_delay' => 200,
        ],
        'oauth' => [
            'authorize_url' => 'https://oauth.jd.com/oauth/authorize',
            'token_url' => 'https://oauth.jd.com/oauth/token',
            'scope' => 'read,write',
            'response_type' => 'code',
            'grant_type' => 'authorization_code',
        ],
        'webhook' => [
            'enabled' => true,
            'verify_token' => env('JD_WEBHOOK_VERIFY_TOKEN', ''),
        ],
        'rate_limit' => [
            'enabled' => true,
            'requests_per_minute' => 100,
        ],
    ],

    'pinduoduo' => [
        'name' => '拼多多',
        'name_en' => 'Pinduoduo',
        'icon' => 'pinduoduo',
        'enabled' => true,
        'api' => [
            'gateway' => 'https://gw-api.pinduoduo.com/api/router',
            'timeout' => 30,
            'retry' => 3,
            'retry_delay' => 200,
        ],
        'oauth' => [
            'authorize_url' => 'https://open-api.pinduoduo.com/oauth/authorize',
            'token_url' => 'https://open-api.pinduoduo.com/oauth/token',
            'scope' => 'basic,order,goods',
            'response_type' => 'code',
            'grant_type' => 'authorization_code',
        ],
        'webhook' => [
            'enabled' => true,
            'verify_token' => env('PDD_WEBHOOK_VERIFY_TOKEN', ''),
        ],
        'rate_limit' => [
            'enabled' => true,
            'requests_per_minute' => 50,
        ],
    ],

    'amazon' => [
        'name' => '亚马逊',
        'name_en' => 'Amazon',
        'icon' => 'amazon',
        'enabled' => true,
        'api' => [
            'gateway' => 'https://sellingpartnerapi-na.amazon.com',
            'timeout' => 30,
            'retry' => 3,
            'retry_delay' => 500,
        ],
        'oauth' => [
            'authorize_url' => 'https://www.amazon.com/ap/oa',
            'token_url' => 'https://api.amazon.com/auth/o2/token',
            'scope' => 'sellingpartnerapi::orders',
            'response_type' => 'code',
            'grant_type' => 'authorization_code',
        ],
        'webhook' => [
            'enabled' => true,
            'verify_token' => env('AMAZON_WEBHOOK_VERIFY_TOKEN', ''),
        ],
        'rate_limit' => [
            'enabled' => true,
            'requests_per_minute' => 10,
        ],
        'marketplaces' => [
            'US' => [
                'id' => 'ATVPDKIKX0DER',
                'region' => 'na',
                'currency' => 'USD',
                'endpoint' => 'https://sellingpartnerapi-na.amazon.com',
            ],
            'CA' => [
                'id' => 'A2EUQ1WTGCTBG2',
                'region' => 'na',
                'currency' => 'CAD',
                'endpoint' => 'https://sellingpartnerapi-na.amazon.com',
            ],
            'GB' => [
                'id' => 'A1F83G8C2ARO7P',
                'region' => 'eu',
                'currency' => 'GBP',
                'endpoint' => 'https://sellingpartnerapi-eu.amazon.com',
            ],
            'DE' => [
                'id' => 'A1PA6795UKMFR9',
                'region' => 'eu',
                'currency' => 'EUR',
                'endpoint' => 'https://sellingpartnerapi-eu.amazon.com',
            ],
            'JP' => [
                'id' => 'A1VC38T7YXB528',
                'region' => 'fe',
                'currency' => 'JPY',
                'endpoint' => 'https://sellingpartnerapi-fe.amazon.com',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 订单状态映射
    |--------------------------------------------------------------------------
    | 各平台订单状态到统一状态的映射
    */
    'order_status_mapping' => [
        'taobao' => [
            'WAIT_BUYER_PAY' => 'pending',
            'WAIT_SELLER_SEND_GOODS' => 'paid',
            'WAIT_BUYER_CONFIRM_GOODS' => 'shipped',
            'WAIT_GOODS_RECEIVE_CONFIRM' => 'shipped',
            'TRADE_FINISHED' => 'completed',
            'TRADE_CLOSED' => 'cancelled',
            'TRADE_CLOSED_BY_TAOBAO' => 'cancelled',
        ],
        'jd' => [
            'WAIT_SELLER_STOCK_OUT' => 'paid',
            'WAIT_GOODS_RECEIVE_CONFIRM' => 'shipped',
            'FINISHED_L' => 'completed',
            'TRADE_CANCEL' => 'cancelled',
        ],
        'pinduoduo' => [
            '1' => 'paid',
            '2' => 'shipped',
            '3' => 'completed',
            '4' => 'cancelled',
            '5' => 'cancelled',
        ],
        'amazon' => [
            'Pending' => 'pending',
            'PendingAvailability' => 'pending',
            'Unshipped' => 'paid',
            'PartiallyShipped' => 'paid',
            'Shipped' => 'shipped',
            'Canceled' => 'cancelled',
            'InvoiceUnconfirmed' => 'completed',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 同步配置
    |--------------------------------------------------------------------------
    | 订单、商品、库存同步的默认配置
    */
    'sync' => [
        'orders' => [
            'enabled' => true,
            'interval_minutes' => 30,
            'batch_size' => 100,
            'lookback_minutes' => 1440, // 同步过去24小时的数据
        ],
        'products' => [
            'enabled' => true,
            'interval_minutes' => 1440, // 每天同步一次
            'batch_size' => 50,
        ],
        'inventory' => [
            'enabled' => true,
            'interval_minutes' => 60,
            'batch_size' => 100,
        ],
        'after_sales' => [
            'enabled' => true,
            'interval_minutes' => 60,
            'batch_size' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 发货配置
    |--------------------------------------------------------------------------
    | 发货相关的默认配置
    */
    'shipment' => [
        'auto_push' => false, // 是否自动推送发货信息到平台
        'push_timeout' => 60, // 推送超时时间（秒）
        'retry_times' => 3, // 失败重试次数
    ],

    /*
    |--------------------------------------------------------------------------
    | 物流配置
    |--------------------------------------------------------------------------
    | 物流公司编码映射
    */
    'logistics' => [
        'carriers' => [
            'taobao' => [
                'SF' => '顺丰速运',
                'YTO' => '圆通速递',
                'ZTO' => '中通快递',
                'YD' => '韵达快运',
                'EMS' => 'EMS',
                'JD' => '京东物流',
                'OTHER' => '其他',
            ],
            'jd' => [
                '10000007' => '顺丰速运',
                '10000013' => '圆通速递',
                '10000027' => '中通快递',
                '10000017' => '韵达快运',
                '10000005' => 'EMS',
            ],
            'pinduoduo' => [
                '1' => '顺丰速运',
                '2' => '圆通速递',
                '3' => '中通快递',
                '4' => '韵达快运',
                '5' => 'EMS',
            ],
            'amazon' => [
                'SF' => 'S.F. Express',
                'YTO' => 'YTO Express',
                'ZTO' => 'ZTO Express',
                'OTHER' => 'Other',
            ],
        ],
    ],
];
