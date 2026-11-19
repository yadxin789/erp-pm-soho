<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PM 状态字典重构：从「独立状态字典」改为「状态机 label 覆盖表」。
 *
 * 合并站早期继承了 PM 第一版迁移（name/code/type/color），但 PM 代码
 * （App\Pm\Models\Status、AdminController@statusesUpdate）早已按第二版口径
 * 使用 state_class / custom_label，导致 pm/admin/statuses 500。
 * 该表仅 PM 使用（SOHO 有自己的 soho_* 状态表，ERP 无引用），可安全重构。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('statuses');

        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('state_class')->unique()->comment('状态机完整类名');
            $table->string('custom_label')->nullable()->comment('自定义标签名，为空时使用状态机默认 label()');
            $table->timestamps();
        });

        $states = [
            \App\Pm\States\Project\InProgress::class,
            \App\Pm\States\Project\Completed::class,
            \App\Pm\States\Project\Overdue::class,
            \App\Pm\States\Project\OverdueCompleted::class,
            \App\Pm\States\Project\Suspended::class,
            \App\Pm\States\Project\NotStarted::class,
            \App\Pm\States\Task\Pending::class,
            \App\Pm\States\Task\InProgress::class,
            \App\Pm\States\Task\ForAudit::class,
            \App\Pm\States\Task\Completed::class,
            \App\Pm\States\Task\Rejected::class,
            \App\Pm\States\Task\Overdue::class,
            \App\Pm\States\Task\OverdueCompleted::class,
        ];

        foreach ($states as $stateClass) {
            DB::table('statuses')->insert([
                'state_class' => $stateClass,
                'custom_label' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');

        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type');
            $table->string('color')->default('#6c757d');
            $table->timestamps();
        });
    }
};
