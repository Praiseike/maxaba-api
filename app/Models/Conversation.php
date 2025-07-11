<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipient_id',
        'property_id',
        'last_message_at',
        'unread_count',
    ];


    protected $appends = ['last_message'];

    protected $with = [ 'user:first_name,last_name,id,uuid,profile_image', 'recipient:first_name,last_name,id,uuid,profile_image'];

    public function getLastMessageAttribute(){
        return $this->messages()->latest()->first();
    }
    
    public function messages(){
        return $this->hasMany(Message::class,'conversation_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function recipient(){
        return $this->belongsTo(User::class,'recipient_id');
    }

    public function property(){
        return $this->belongsTo(Property::class,'property_id');
    }
}
