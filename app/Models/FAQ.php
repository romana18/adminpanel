<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    use HasFactory;

    protected $table = 'faqs';

    protected $fillable = [
        'readable_id',
        'category_id',
        'question',
        'answer',
        'status',
    ];

    protected $casts = [
        'status' => 'integer'
    ];

    public function ScopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function faqCategory()
    {
        return $this->belongsTo(FAQCategory::class, 'category_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $lastReadableId = self::max('readable_id') ?? 100000;
            $model->readable_id = $lastReadableId + 1;
        });
    }
}
