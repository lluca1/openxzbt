<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exposition extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'cover_image_path',
        'is_public',
        'preset_theme',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'preset_theme' => 'integer',
    ];

    /**
     * Get the user that owns the exposition.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the exhibits for the exposition ordered by position.
     */
    public function exhibits()
    {
        return $this->hasMany(Exhibit::class)->orderBy('position');
    }

    /**
     * Get the likes for the exposition.
     */
    public function likes()
    {
        return $this->hasMany(ExpositionLike::class);
    }

    /**
     * Get the comments for the exposition.
     */
    public function comments()
    {
        return $this->hasMany(ExpositionComment::class);
    }
}
