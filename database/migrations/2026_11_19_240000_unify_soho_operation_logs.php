<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * P2-3：SOHO 操作日志归一。
 *
 * - 数据：`soho_operation_logs`（532 行）导入 Core `operation_logs`，`module='soho'`，
 *   model_type→target_type、model_id→target_id、changes→payload；按
 *   (module, user_id, action, target_id, created_at, description) 去重，幂等。
 * - 结构：`soho_operation_logs` 由物理表改为**只读视图**（列名保持旧名，历史读 SQL 不用改），
 *   所有新写入走 `App\Soho\Models\OperationLog` → `operation_logs(module='soho')`。
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('soho_operation_logs')) {
            return;
        }

        // 已经是视图则跳过
        $type = DB::selectOne("select TABLE_TYPE as t from information_schema.TABLES where TABLE_SCHEMA = database() and TABLE_NAME = 'soho_operation_logs'");
        if ($type && strtoupper($type->t) === 'VIEW') {
            return;
        }

        $rows = DB::table('soho_operation_logs')->orderBy('id')->get();

        $inserted = 0;
        foreach ($rows as $r) {
            $exists = DB::table('operation_logs')
                ->where('module', 'soho')
                ->where('user_id', $r->user_id)
                ->where('action', $r->action)
                ->where('target_id', $r->model_id)
                ->where('created_at', $r->created_at)
                ->where('description', $r->description)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('operation_logs')->insert([
                'module' => 'soho',
                'user_id' => $r->user_id,
                'user_name' => $r->user_id
                    ? optional(DB::table('users')->where('id', $r->user_id)->first())->name
                    : null,
                'action' => $r->action,
                'target_type' => $r->model_type,
                'target_id' => $r->model_id,
                'old_values' => null,
                'new_values' => null,
                'payload' => $r->changes,
                'description' => $r->description,
                'ip_address' => $r->ip_address,
                'user_agent' => $r->user_agent,
                'tenant_id' => $r->tenant_id,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at ?? $r->created_at,
            ]);
            $inserted++;
        }

        Schema::drop('soho_operation_logs');

        DB::statement("
            CREATE VIEW soho_operation_logs AS
            SELECT id, tenant_id, user_id, action,
                   target_type AS model_type,
                   target_id   AS model_id,
                   description,
                   payload     AS changes,
                   ip_address, user_agent, created_at, updated_at
            FROM operation_logs
            WHERE module = 'soho'
        ");

        echo "迁移完成：导入 {$inserted} 行，soho_operation_logs 已改为视图\n";
    }

    public function down(): void
    {
        $type = DB::selectOne("select TABLE_TYPE as t from information_schema.TABLES where TABLE_SCHEMA = database() and TABLE_NAME = 'soho_operation_logs'");
        if (! $type || strtoupper($type->t) !== 'VIEW') {
            return;
        }

        DB::statement('DROP VIEW soho_operation_logs');

        Schema::create('soho_operation_logs', function ($table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 50)->nullable();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        foreach (DB::table('operation_logs')->where('module', 'soho')->get() as $r) {
            DB::table('soho_operation_logs')->insert([
                'tenant_id' => $r->tenant_id,
                'user_id' => $r->user_id,
                'action' => $r->action,
                'model_type' => $r->target_type,
                'model_id' => $r->target_id,
                'description' => $r->description,
                'changes' => $r->payload,
                'ip_address' => $r->ip_address,
                'user_agent' => $r->user_agent,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ]);
        }
    }
};
