<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * P3 数据收口：① 全员邮箱/用户名可登录（缺邮箱账号补 <username>@pm.local）
 *               ② SOHO 平台 SKU 在 (shop_id, platform_sku) 维度唯一化
 *
 * 幂等；不带 --apply 为演练。
 */
class P3FixUniqueness extends Command
{
    protected $signature = 'p3:fix-uniqueness {--apply : 真正写库，缺省为演练}';

    protected $description = 'P3：补全用户邮箱 + 平台SKU去重，保证唯一性约束可建立';

    private const EMAIL_DOMAIN = 'pm.local';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $this->line($apply ? '== 模式：APPLY（写库）==' : '== 模式：DRY-RUN（不写库）==');

        $this->fillEmails($apply);
        $this->uniquePlatformSku($apply);

        if (! $apply) {
            $this->warn('演练结束，未写库。加 --apply 执行。');
        }

        return self::SUCCESS;
    }

    private function fillEmails(bool $apply): void
    {
        $missing = DB::table('users')
            ->where(fn ($q) => $q->whereNull('email')->orWhere('email', ''))
            ->orderBy('id')
            ->get(['id', 'username', 'name']);

        $this->info('缺邮箱账号：' . $missing->count() . ' 个');
        $taken = DB::table('users')->whereNotNull('email')->where('email', '<>', '')->pluck('email')->map(fn ($e) => strtolower(trim($e)))->all();
        $plan = [];
        foreach ($missing as $u) {
            $base = strtolower(preg_replace('/[^a-z0-9._-]/i', '', (string) $u->username)) ?: ('user' . $u->id);
            $email = $base . '@' . self::EMAIL_DOMAIN;
            $n = 2;
            while (in_array($email, $taken, true)) {
                $email = $base . '-' . $n . '@' . self::EMAIL_DOMAIN;
                $n++;
            }
            $taken[] = $email;
            $plan[] = ['id' => $u->id, 'username' => $u->username, 'email' => $email];
        }
        foreach (array_slice($plan, 0, 6) as $r) {
            $this->line("  #{$r['id']} {$r['username']} → {$r['email']}");
        }
        if (count($plan) > 6) {
            $this->line('  … 其余 ' . (count($plan) - 6) . ' 条');
        }

        if ($apply && $plan) {
            foreach ($plan as $r) {
                DB::table('users')->where('id', $r['id'])->update(['email' => $r['email'], 'updated_at' => now()]);
            }
            $this->info('  已补邮箱 ' . count($plan) . ' 个');
        }
    }

    private function uniquePlatformSku(bool $apply): void
    {
        $groups = DB::table('soho_platform_skus')
            ->select('shop_id', 'platform_sku', DB::raw('count(*) as c'))
            ->groupBy('shop_id', 'platform_sku')
            ->havingRaw('count(*) > 1')
            ->get();

        $this->info('平台 SKU 重复组合：' . $groups->count() . ' 组');
        $changes = 0;
        foreach ($groups as $g) {
            $rows = DB::table('soho_platform_skus')
                ->where('shop_id', $g->shop_id)
                ->where('platform_sku', $g->platform_sku)
                ->orderBy('id')
                ->get(['id', 'platform_sku']);
            $this->line("  shop={$g->shop_id} sku={$g->platform_sku} 共 {$rows->count()} 行：保留 #{$rows->first()->id}，其余加 -NN 后缀");

            $used = DB::table('soho_platform_skus')->where('shop_id', $g->shop_id)->pluck('platform_sku')->all();
            $i = 2;
            foreach ($rows->slice(1) as $r) {
                do {
                    $new = $g->platform_sku . '-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                    $i++;
                } while (in_array($new, $used, true));
                $used[] = $new;
                $changes++;
                if ($apply) {
                    DB::table('soho_platform_skus')->where('id', $r->id)->update(['platform_sku' => $new, 'updated_at' => now()]);
                } else {
                    $this->line("    #{$r->id} {$r->platform_sku} → {$new}");
                }
            }
        }

        if ($apply && $changes) {
            $this->info("  已改写平台 SKU {$changes} 行");
        }
        if (! $apply) {
            $this->line("  预计改写 {$changes} 行");
        }
    }
}
