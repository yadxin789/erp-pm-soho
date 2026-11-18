<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_sync_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id');
            $table->enum('type', ['order', 'product', 'stock', 'shipment', 'aftersale']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('params')->nullable();
            $table->json('result')->nullable();
            $table->string('error_message', 500)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('operator_id')->nullable();
            $table->timestamps();
            $table->index('shop_id');
            $table->index('type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_sync_tasks');
    }
};