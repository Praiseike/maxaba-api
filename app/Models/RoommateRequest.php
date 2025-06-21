<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoommateRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','category_id',
        'min_price','max_price','interests','location',
        'gender',
    ];

    protected $casts = [
        'interests' => 'array',
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }
}
