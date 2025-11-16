<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExpositionResource;
use App\Models\Exposition;

class ExpositionController extends Controller
{
    /**
     * Return a complete exposition payload with its exhibits for the 3D client.
     */
    public function show(Exposition $exposition): ExpositionResource
    {
        $exposition->load([
            'user:id,name,email',
            'tiles',
            'exhibits' => function ($query) {
                $query->orderBy('position');
            },
        ]);

        return new ExpositionResource($exposition);
    }
}
