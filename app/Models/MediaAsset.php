<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'message_id',
        'file_type',
        'file_name',
        'file_size',
    ];

    protected $appends = [
        'file_url', // Assuming you want to append a URL for the file
    ];

    public function getFileUrlAttribute()
    {
        return url("storage/".$this->file_path);
    }
}
