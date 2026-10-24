<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = ['disk', 'path', 'original_name', 'mime', 'size', 'user_id'];

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
