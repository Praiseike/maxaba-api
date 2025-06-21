<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'user_id',
        'category_id',
        'occupant_type',
        'location',
        'price',
        'description',
        'images',
        'bedrooms',
        'admin_id',
        'bathrooms',
        'rejection_reason',
        'livingrooms',
        'amenities',
        'published',
        'verified',
        'status',
        'other_information',
        'charges',
    ];

    protected $casts = [
        'location' => 'array',
        'images' => 'array',
        'amenities' => 'array',
        'published' => 'boolean',
        'verified' => 'boolean',
        'status' => Status::class,
        'other_information' => 'array',
        'charges' => 'array'
    ];

    protected $appends = [
        'user_image'
    ];




    // attributes
    public function getUserImageAttribute()
    {
        return $this->user?->profile_image_url;
    }


    public function getImagesAttribute($value){
        return array_map(function ($image) {
            return url("/storage/".$image);
        }, json_decode($value, true));
    }




    // relationships


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function favouritedBy()
    {
        return $this->belongsToMany(User::class, 'favourites')->withTimestamps();
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
