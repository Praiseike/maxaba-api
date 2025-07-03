<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'property_id',
        'user_id',
        'content',
        'is_read',
        'read_at',
        'type', // 'text', 'image', 'file', etc.
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];
    protected $with = ['assets','property'];
    
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function assets()
    {
        return $this->hasMany(MediaAsset::class, 'message_id');
    }
    
    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
