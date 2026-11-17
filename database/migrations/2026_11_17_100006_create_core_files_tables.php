<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core：files + file_versions（PM 文件审核流为主，吸收 erp/soho 的部门文件与媒体库字段）
 * 依据：01-目标数据库字典 §2.7，PM files/file_versions/department_files 实际列
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('module', 20)->nullable()->index();
            // 归属：部门 / 任务 / 知识库 / 其他业务单据
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->unsignedBigInteger('task_id')->nullable()->index();
            $table->unsignedBigInteger('uploader_id')->index();
            $table->unsignedBigInteger('auditor_id')->nullable()->index();
            $table->string('name', 255);
            $table->string('original_name', 255)->nullable();
            $table->string('disk', 32)->default('local');
            $table->string('path', 500);
            $table->unsignedBigInteger('size')->default(0);
            $table->string('mime_type', 150)->nullable();
            $table->string('category', 100)->nullable();
            // private / department / public
            $table->string('visibility', 20)->default('department');
            $table->unsignedInteger('version')->default(1);
            // approved / pending / rejected（erp/soho 无审核概念，默认 approved）
            $table->string('status', 20)->default('approved')->index();
            $table->timestamp('audit_time')->nullable();
            $table->text('audit_reason')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('file_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id')->nullable()->index();
            // PM 的版本可挂在任务上（尚未成文件时）
            $table->unsignedBigInteger('task_id')->nullable()->index();
            $table->unsignedInteger('version')->default(1);
            $table->string('disk', 32)->default('local');
            $table->string('path', 500)->nullable();
            $table->unsignedBigInteger('uploader_id')->index();
            $table->unsignedBigInteger('auditor_id')->nullable();
            // pending / approved / rejected
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('audit_time')->nullable();
            $table->text('audit_reason')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamps();

            $table->foreign('file_id')->references('id')->on('files')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_versions');
        Schema::dropIfExists('files');
    }
};
