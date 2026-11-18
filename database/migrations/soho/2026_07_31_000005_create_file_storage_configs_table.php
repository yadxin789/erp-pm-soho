<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 文件存储配置表迁移
 * 管理系统各文件存储磁盘的配置（本地、阿里云OSS、腾讯云COS、七牛云等）
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soho_file_storage_configs', function (Blueprint $table) {
            $table->id();
            // 租户ID，多租户隔离
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            // 磁盘标识（如 local, oss, cos, qiniu）
            $table->string('disk_name', 50)->comment('磁盘标识');
            // 配置名称（展示用）
            $table->string('name', 100)->comment('配置名称');
            // 驱动类型：local/s3/oss/cos/qiniu
            $table->string('driver', 30)->default('local')->comment('驱动类型');
            // 存储配置JSON（access_key, secret_key, bucket, endpoint, region等）
            $table->json('config')->nullable()->comment('存储配置(JSON)');
            // 访问域名
            $table->string('domain', 255)->nullable()->comment('访问域名');
            // 是否默认磁盘
            $table->boolean('is_default')->default(false)->comment('是否默认磁盘');
            // 是否启用
            $table->boolean('is_active')->default(true)->comment('是否启用');
            // 最后修改人ID
            $table->foreignId('last_modified_by')
                ->nullable()
                
                
                ->comment('最后修改人ID');
            $table->timestamps();

            // 租户+磁盘标识唯一索引
            $table->unique(['tenant_id', 'disk_name'], 'ux_90_1');
            // 租户+驱动索引
            $table->index(['tenant_id', 'driver'], 'ix_90_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soho_file_storage_configs');
    }
};
