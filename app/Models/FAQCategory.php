<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAQCategory extends Model
{
    use HasFactory;

    protected $table = 'faq_categories';

    protected $fillable = [
        'name',
        'status',
        'click_count'
    ];

    protected $casts = [
        'status' => 'integer',
        'click_count' => 'integer',
    ];

    public function ScopeActive($query)
    {
        return $query->where('status', 1);
    }
}

