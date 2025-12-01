<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpositionLayoutRequest;
use App\Models\Exposition;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpositionLayoutController extends Controller
{
    /**
     * Persist tiles and exhibit layout data coming from the Unity editor.
     */
    public function update(ExpositionLayoutRequest $request, Exposition $exposition): JsonResponse
    {
        Log::info('aaa');
        // if (Auth::id() !== $exposition->user_id) {
        //     abort(403);
        // }

        Log::info('Layout payload received', [
            'exposition_id' => $exposition->id,
            'payload' => $request->all(),
        ]);

        $data = $request->validated();

        DB::transaction(function () use ($exposition, $data) {
            $this->syncTiles($exposition, collect($data['tiles']));
            $this->syncExhibitLayouts($exposition, collect($data['exhibits']));

            if (array_key_exists('player_spawn', $data)) {
                $exposition->player_spawn = $data['player_spawn'];
                $exposition->save();
            }
        });

        $exposition->load(['tiles', 'exhibits']);

        return response()->json([
            'message' => 'Layout saved successfully.',
            'player_spawn' => $exposition->player_spawn,
            'tiles' => $exposition->tiles,
            'exhibits' => $exposition->exhibits,
        ]);
    }

    private function syncTiles(Exposition $exposition, Collection $tiles): void
    {
        $exposition->tiles()->delete();

        if ($tiles->isEmpty()) {
            return;
        }

        foreach ($tiles as $tileData) {
            $exposition->tiles()->create([
                'tile_identifier' => $tileData['id'],
                'type' => $tileData['type'],
                'has_exhibit' => $tileData['has_exhibit'],
                'position' => $tileData['position'],
                'rotation' => $tileData['rotation'],
            ]);
        }
    }

    private function syncExhibitLayouts(Exposition $exposition, Collection $exhibits): void
    {
        if ($exhibits->isEmpty()) {
            return;
        }

        $ordered = $exposition->exhibits()
            ->orderBy('created_at')
            ->get()
            ->values();

        foreach ($exhibits as $index => $exhibitData) {
            $exhibit = $ordered->get($index);

            if (! $exhibit) {
                continue;
            }

            $exhibit->update([
                'layout_position' => $exhibitData['position'],
                'size' => $exhibitData['size'],
            ]);
        }
    }
}
