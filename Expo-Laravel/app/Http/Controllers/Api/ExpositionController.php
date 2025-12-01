<?php

namespace App\Http\Controllers\Api;

use App\Models\Exposition;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExpositionResource;

class ExpositionController extends Controller
{
    /**
     * Return a complete exposition payload with its exhibits for the 3D client.
     */
    public function show(Exposition $exposition): ExpositionResource
    {
        Log::info('Showing exposition', ['exposition_id' => $exposition->id]);
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
