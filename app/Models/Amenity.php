<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image'];

    public function getImageAttribute($value)
    {
        if ($value && !\Str::startsWith($value, ['http://', 'https://', 'ph-'])) {
            return url('/storage/' . $value);
        }
        return $value;
    }

}
