<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExhibitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exposition_id' => $this->exposition_id,
            'title' => $this->title,
            'description' => $this->description,
            'media_path' => $this->media_path,
            'position' => $this->layout_position ?? [0.0, 0.0, 0.0],
            'size' => $this->size,
        ];
    }
}
