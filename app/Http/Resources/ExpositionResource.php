<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ExpositionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'preset_theme' => $this->preset_theme,
            'spawnpoint' => $this->player_spawn,
            
            'curator' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'email' => $this->user?->email,
                ];
            }),
            
            'exhibits' => ExhibitResource::collection($this->whenLoaded('exhibits')),
            
            'tiles' => TileResource::collection($this->whenLoaded('tiles')),
            
            'floor_texture' => $this->floor_texture ? Storage::url($this->floor_texture) : null,
            'ceiling_texture' => $this->ceiling_texture ? Storage::url($this->ceiling_texture) : null,
            'wall_texture' => $this->wall_texture ? Storage::url($this->wall_texture) : null,
            'ambient_track' => $this->ambient_track ? Storage::url($this->ambient_track) : null,
            
            'meta' => [
                'exhibits_count' => $this->whenLoaded('exhibits', fn () => $this->exhibits->count()),
            ],
        ];
    }
}
