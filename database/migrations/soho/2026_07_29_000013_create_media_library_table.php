<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soho_media_library', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->nullable()
                
                
                ->comment('租户ID');
            $table->string('file_name', 255)->comment('文件名');
            $table->string('file_path', 500)->comment('文件路径');
            $table->string('file_type', 20)->comment('文件类型: image/video/document');
            $table->unsignedBigInteger('file_size')->default(0)->comment('文件大小(字节)');
            $table->string('mime_type', 100)->nullable()->comment('MIME类型');
            $table->string('thumbnail_path', 500)->nullable()->comment('缩略图路径');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamps();

            $table->index(['tenant_id', 'file_type'], 'ix_54_1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soho_media_library');
    }
};