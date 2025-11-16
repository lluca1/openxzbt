<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->tile_identifier,
            'exposition_id' => $this->exposition_id,
            'type' => $this->type,
            'position' => $this->position,
            'rotation' => $this->rotation,
        ];
    }
}
