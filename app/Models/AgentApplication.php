<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'id_path',
        'proof_of_address_path',
        'status',
        'rejection_reason',
    ];

    protected $appends = ['documents'];


    public function getDocumentsAttribute(){
        return [
            'id' => url('/storage/'.$this->id_path),
            'proof_of_address' => url('/storage/'.$this->proof_of_address_path),
        ];
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
}
