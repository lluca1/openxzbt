<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'curator' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name,
                    'email' => $this->user?->email,
                ];
            }),
            'exhibits' => ExhibitResource::collection($this->whenLoaded('exhibits')),
            'tiles' => TileResource::collection($this->whenLoaded('tiles')),
            'meta' => [
                'exhibits_count' => $this->whenLoaded('exhibits', fn () => $this->exhibits->count()),
            ],
        ];
    }
}
