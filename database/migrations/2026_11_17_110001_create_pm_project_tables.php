<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PM 模块：项目 / 任务 / 协作文档 / 项目留言（B-03 前半）
 * 字段与语义对齐原 PM 项目（projects / tasks / collab_documents / messages / project_logs），
 * 主键、状态字符串保持原名，便于后续整表迁移比对。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('description', 255)->nullable();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 60)->unique();
            $table->string('version', 30)->default('V1.0');
            $table->string('product_name', 150)->nullable();
            $table->string('product_model', 150)->nullable();
            $table->unsignedBigInteger('creator_id')->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('not_started')->index();
            $table->decimal('progress', 5, 2)->default(0);
            $table->string('paused_reason', 500)->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resumed_at')->nullable();
            $table->integer('total_paused_days')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->string('name', 200);
            $table->unsignedBigInteger('executor_id')->nullable()->index();
            $table->unsignedBigInteger('auditor_id')->nullable()->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedBigInteger('task_type_id')->nullable();
            $table->boolean('is_overdue')->default(false);
            $table->integer('extension_days')->default(0);
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::create('collab_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->string('name', 200);
            $table->string('extension', 20)->default('docx');
            $table->string('storage_path', 255)->nullable();
            $table->boolean('is_temp_demo')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->string('doc_key', 80)->nullable()->index();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::create('project_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('receiver_id')->nullable()->index();
            $table->boolean('is_private')->default(false);
            $table->text('content')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('file_name', 200)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::create('project_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 120);
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_logs');
        Schema::dropIfExists('project_messages');
        Schema::dropIfExists('collab_documents');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('task_types');
    }
};
