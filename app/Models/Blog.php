<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'readable_id',
        'category_id',
        'writer',
        'title',
        'description',
        'image',
        'publish_date',
        'status',
        'is_draft',
        'draft_data',
        'click_count',
        'is_published',
    ];

    protected $casts = [
        'draft_data' => 'array',
        'status' => 'integer',
        'is_draft' => 'integer',
        'click_count' => 'integer',
        'is_published' => 'boolean'
    ];

    public function getImageFullPathAttribute(): string
    {
        $image = $this->image ?? null;
        $path = asset('assets/admin/img/1920x400/img2.jpg');

        if (!is_null($image) && Storage::disk('public')->exists('blog/' . $image)) {
            $path = asset('storage/app/public/blog/' . $image);
        }
        return $path;
    }

    public function getDraftImageFullPathAttribute(): string
    {
        $image = $this->draft_data['image'] ?? null;
        $path = asset('assets/admin/img/1920x400/img2.jpg');

        if (!is_null($image) && Storage::disk('public')->exists('blog/' . $image)) {
            $path = asset('storage/app/public/blog/' . $image);
        }
        return $path;
    }

    public function ScopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function blogCategory()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $lastReadableId = self::max('readable_id') ?? 100000;
            $model->readable_id = $lastReadableId + 1;
            $slug = $model->title. '-'. $lastReadableId + 1;
            $model->slug = Str::slug($slug);
        });
    }
}
