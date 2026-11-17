<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 产品研发项目模块 —— 协同办公 / 聊天室 / 电话会议 / 文件中心 / 延期审批 / 待办 / 模板 / 知识库
 *
 * 全部为 PM 模块专用表，不改动其它业务模块的表结构。
 */
return new class extends Migration
{
    public function up(): void
    {
        // 项目组群聊
        if (! Schema::hasTable('project_messages')) {
        }

        // 项目组内私聊
        if (! Schema::hasTable('project_private_messages')) {
        }

        // 协同办公文档（OnlyOffice）
        if (! Schema::hasTable('collab_documents')) {
        }

        // 电话会议记录
        if (! Schema::hasTable('erp_voice_calls')) {
            Schema::create('erp_voice_calls', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id')->index();
                $table->string('room', 64)->index();
                $table->unsignedBigInteger('started_by')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->unsignedInteger('duration_seconds')->default(0);
                $table->text('participants')->nullable();
                $table->string('demo_file')->nullable();
                $table->timestamps();
            });
        }

        // 延期申请
        if (! Schema::hasTable('erp_extension_applications')) {
            Schema::create('erp_extension_applications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id')->index();
                $table->unsignedBigInteger('project_id')->index();
                $table->unsignedBigInteger('applicant_id')->index();
                $table->unsignedInteger('days')->default(1);
                $table->text('reason');
                $table->string('status', 20)->default('applying'); // applying / approved / rejected
                $table->unsignedBigInteger('approver_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->string('remark')->nullable();
                $table->timestamps();
            });
        }

        // 待办
        if (! Schema::hasTable('erp_project_todos')) {
            Schema::create('erp_project_todos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('project_id')->nullable()->index();
                $table->string('type', 20)->default('task'); // task / audit / extension / todo
                $table->unsignedBigInteger('ref_id')->nullable();
                $table->string('title');
                $table->date('due_date')->nullable();
                $table->timestamp('done_at')->nullable();
                $table->timestamps();
            });
        }

        // 部门资料文件（文员/支援上传）
        if (! Schema::hasTable('erp_department_files')) {
            Schema::create('erp_department_files', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('department_id')->nullable()->index();
                $table->string('original_name');
                $table->string('stored_path');
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->unsignedInteger('version')->default(1);
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->string('status', 20)->default('approved');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 资源文件（公共资料）
        if (! Schema::hasTable('erp_resource_files')) {
            Schema::create('erp_resource_files', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('category', 60)->nullable();
                $table->string('original_name');
                $table->string('stored_path');
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->unsignedInteger('downloads')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 项目模板
        if (! Schema::hasTable('erp_project_templates')) {
            Schema::create('erp_project_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->json('config')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 知识库
        if (! Schema::hasTable('erp_knowledge_bases')) {
            Schema::create('erp_knowledge_bases', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('description')->nullable();
                $table->unsignedBigInteger('department_id')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('erp_knowledge_docs')) {
            Schema::create('erp_knowledge_docs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('knowledge_id')->index();
                $table->string('title');
                $table->longText('content')->nullable();
                $table->unsignedInteger('version')->default(1);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'erp_knowledge_docs', 'erp_knowledge_bases', 'erp_project_templates', 'erp_resource_files', 'erp_department_files',
            'erp_project_todos', 'erp_extension_applications', 'erp_voice_calls', 'collab_documents',
            'project_private_messages', 'project_messages',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
