<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoommateRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id',
        'min_price', 'max_price', 'interests', 'location',
        'gender', 'title', 'house_image', 'map'
    ];

    protected $casts = [
        'interests' => 'array',
        'map' => 'array',
    ];

    protected $appends = ['house_image_url'];

    public function getHouseImageUrlAttribute()
    {
        return $this->house_image ? url('/storage/' . $this->house_image) : null;
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
