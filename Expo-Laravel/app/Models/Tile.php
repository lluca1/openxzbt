<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tile extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'exposition_id',
        'tile_identifier',
        'type',
        'has_exhibit',
        'position',
        'rotation',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'type' => 'integer',
        'position' => 'array',
        'rotation' => 'array',
    ];

    /**
     * Get the exposition that owns the tile.
     */
    public function exposition()
    {
        return $this->belongsTo(Exposition::class);
    }
}
