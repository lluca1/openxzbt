<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpositionComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'exposition_id',
        'user_id',
        'body',
    ];

    public function exposition()
    {
        return $this->belongsTo(Exposition::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
