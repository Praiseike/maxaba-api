<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;


class Property extends Model
{
    use HasFactory, SoftDeletes;
    const OFFER_TYPE_RENT = 'rent';
    const OFFER_TYPE_SALE = 'sale';

    protected $fillable = [
        'title',
        'user_id',
        'category_id',
        'offer_type',
        'slug',
        'offer_duration',
        'occupant_type',
        'location',
        'price',
        'offer_status',
        'description',
        'images',
        'bedrooms',
        'kitchens',
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


    public function getImagesAttribute($value)
    {
        return array_map(function ($image) {
            return url("/storage/" . $image);
        }, json_decode($value, true));
    }




    // scopes

    public function scopeAvailable($query)
    {
        return $query->where('offer_status', 'available');
    }




    // relationships

    protected static function booted()
    {
        static::creating(function ($property) {
            $property->slug = $property->generateSlug();
        });

        static::updating(function ($property) {
            // optionally re-generate slug if title/rooms/amenities change
            $property->slug = $property->generateSlug();
        });
    }

    public function generateSlug(): string
    {
        $parts = [
            $this->title,
            "{$this->bedrooms}-bed",
            "{$this->bathrooms}-bath",
            "{$this->livingrooms}-living",
            "{$this->kitchens}-kitchen",
        ];

        // Include up to 3 amenities for brevity
        if (is_array($this->amenities)) {
            $amenityPart = collect($this->amenities)
                ->take(3)
                ->map(fn($a) => Str::slug($a))
                ->implode('-');
            $parts[] = $amenityPart;
        }

        // Base slug
        $baseSlug = Str::slug(implode(' ', $parts));
        $slug = $baseSlug;
        $counter = 1;

        // Check for uniqueness
        while (
            self::where('slug', $slug)
                ->when($this->exists, fn($query) => $query->where('id', '!=', $this->id))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }


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
