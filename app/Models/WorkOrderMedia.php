<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WorkOrderMedia extends Model
{
    protected $table = 'work_order_media';

    protected $fillable = [
        'work_order_id',
        'uploaded_by',
        'type',
        'media_type',
        'path',
        'original_name',
        'mime_type',
        'size',
        'caption',
    ];

    protected $appends = ['url', 'is_image', 'is_video'];
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getIsImageAttribute(): bool
    {
        return $this->media_type === 'image';
    }

    public function getIsVideoAttribute(): bool
    {
        return $this->media_type === 'video';
    }
}
