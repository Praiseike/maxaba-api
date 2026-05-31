<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name','image'];

    public function getImageAttribute($value)
    {
        if ($value && !\Str::startsWith($value, ['http://', 'https://'])) {
            return url('/storage/' . $value);
        }
        return $value;
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}
