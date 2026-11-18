<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('soho_notifications', function (Blueprint $table) {
            $table->string('priority', 20)->default('normal')->comment('优先级: high高/normal普通/low低')->after('type');
            $table->string('sender', 100)->nullable()->comment('发送人')->after('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soho_notifications', function (Blueprint $table) {
            $table->dropColumn(['priority', 'sender']);
        });
    }
};