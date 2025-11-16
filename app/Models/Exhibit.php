<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exhibit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exposition_id',
        'user_id',
        'title',
        'description',
        'media_type',
        'media_path',
        'thumbnail_path',
        'mime_type',
        'position',
        'layout_position',
        'size',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'position' => 'integer',
        'layout_position' => 'array',
        'size' => 'float',
    ];

    /**
     * Get the exposition that owns the exhibit.
     */
    public function exposition()
    {
        return $this->belongsTo(Exposition::class);
    }

    /**
     * Get the user that uploaded the exhibit media.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
                  