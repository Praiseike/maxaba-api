<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    protected $fillable = [
        'page_slug',
        'title',
        'description',
        'keywords',
        'og_image',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}