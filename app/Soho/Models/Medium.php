<?php

namespace App\Soho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * 媒体库模型
 * 管理系统中的媒体文件，包括图片、视频、文档等
 */
class Medium extends Model
{
    use \App\Soho\Models\Concerns\HasSohoTable;

    protected $table = 'soho_media_library';

    protected $fillable = [
        'tenant_id', 'file_name', 'file_path', 'file_type', 'file_size',
        'mime_type', 'thumbnail_path', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'sort' => 'integer',
        ];
    }

    /**
     * 获取所属租户
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * 访问器：格式化文件大小
     */
    public function getSizeTextAttribute(): string
    {
        $size = $this->file_size;
        if ($size < 1024) return $size . ' B';
        if ($size < 1048576) return round($size / 1024, 1) . ' KB';
        if ($size < 1073741824) return round($size / 1048576, 1) . ' MB';
        return round($size / 1073741824, 2) . ' GB';
    }

    /**
     * 访问器：文件 URL
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * 访问器：缩略图 URL
     */
    public function getThumbUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) return null;
        return Storage::disk('public')->url($this->thumbnail_path);
    }

    /**
     * 访问器：类型标签
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->file_type) {
            'image' => '图片',
            'video' => '视频',
            'document' => '文档',
            'audio' => '音频',
            default => '其他',
        };
    }

    /**
     * 访问器：下载 URL
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('media.download', $this);
    }
}
