<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'directory_id', 'path'];

    public function directory(): BelongsTo
    {
        return $this->belongsTo(Directory::class);
    }
}

