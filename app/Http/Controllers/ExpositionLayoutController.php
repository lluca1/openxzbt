<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpositionLayoutRequest;
use App\Models\Exposition;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpositionLayoutController extends Controller
{
    /**
     * Persist tiles and exhibit layout data coming from the Unity editor.
     */
    public function update(ExpositionLayoutRequest $request, Exposition $exposition): JsonResponse
    {
        if (Auth::id() !== $exposition->user_id) {
            abort(403);
        }

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
        $identifiers = $tiles->pluck('id');

        foreach ($tiles as $tileData) {
            $exposition->tiles()->updateOrCreate(
                [
                    'tile_identifier' => $tileData['id'],
                ],
                [
                    'type' => $tileData['type'],
                    'position' => $tileData['position'],
                    'rotation' => $tileData['rotation'],
                ]
            );
        }

        $exposition->tiles()
            ->whereNotIn('tile_identifier', $identifiers)
            ->delete();
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
