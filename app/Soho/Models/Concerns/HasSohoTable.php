<?php

namespace App\Soho\Models\Concerns;

use Illuminate\Support\Facades\Schema;

trait HasSohoTable
{
    public function getTable(): string
    {
        $t = parent::getTable();

        return str_starts_with($t, 'soho_') ? $t : 'soho_'.$t;
    }

    /**
     * SOHO 保留 tenant_id 作为模块私有概念：单租户部署下统一落到默认租户，
     * 参考项目里由 Filament 面板自动注入，Bootstrap 表单不做该字段输入，故在此兜底。
     */
    public static function bootHasSohoTable(): void
    {
        static::creating(function ($model) {
            $table = $model->getTable();

            if (array_key_exists('tenant_id', $model->getAttributes())) {
                return;
            }

            try {
                if (! Schema::hasColumn($table, 'tenant_id')) {
                    return;
                }

                $tenantId = \App\Soho\Models\Tenant::query()->value('id') ?: 1;
                $model->setAttribute('tenant_id', $tenantId);
            } catch (\Throwable $e) {
                // 表结构不可用时交给数据库报错，不吞真实错误
            }
        });
    }
}