<?php

namespace App\Livewire;

use App\Models\Exposition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ExpositionsManager extends Component
{
    use WithFileUploads;

    /**
     * Displayable list of expositions.
     */
    public $expositions = [];

    #[Rule('required|string|max:255')]
    public string $title = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('boolean')]
    public bool $is_public = true;

    #[Rule('integer|in:-1,0,1,2')]
    public int $preset_theme = -1;

    #[Rule('nullable|image|max:4096')]
    public $thumbnail;

    #[Rule('nullable|image|mimes:jpg,jpeg,png,bmp,webp,avif|max:8192')]
    public $floor_texture;

    #[Rule('nullable|image|mimes:jpg,jpeg,png,bmp,webp,avif|max:8192')]
    public $ceiling_texture;

    #[Rule('nullable|image|mimes:jpg,jpeg,png,bmp,webp,avif|max:8192')]
    public $wall_texture;

    #[Rule('nullable|file|mimes:mp3,wav,ogg,flac,m4a|max:30720')]
    public $ambient_track;

    public function mount(): void
    {
        $this->loadExpositions();
    }

    public function render()
    {
        return view('livewire.expositions-manager');
    }

    public function save(): void
    {
        $this->validate();

        $userId = Auth::id();

        if (! $userId) {
            $this->addError('title', 'You must be logged in to create an exposition.');
            return;
        }

        $exposition = Exposition::create([
            'user_id' => $userId,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'is_public' => $this->is_public,
            'preset_theme' => $this->preset_theme,
        ]);

        if ($this->thumbnail) {
            $extension = $this->thumbnail->getClientOriginalExtension() ?: $this->thumbnail->extension();
            $filename = 'cover-'.Str::uuid().'.'.$extension;
            $path = $this->thumbnail->storeAs('expositions/'.$exposition->id, $filename, 'public');
            $exposition->update(['cover_image_path' => $path]);
        }

        $updates = [];

        if ($this->floor_texture) {
            $updates['floor_texture'] = $this->storeExpoAsset($this->floor_texture, $exposition->id, 'floor-texture', 'textures');
        }

        if ($this->ceiling_texture) {
            $updates['ceiling_texture'] = $this->storeExpoAsset($this->ceiling_texture, $exposition->id, 'ceiling-texture', 'textures');
        }

        if ($this->wall_texture) {
            $updates['wall_texture'] = $this->storeExpoAsset($this->wall_texture, $exposition->id, 'wall-texture', 'textures');
        }

        if ($this->ambient_track) {
            $updates['ambient_track'] = $this->storeExpoAsset($this->ambient_track, $exposition->id, 'ambient-track', 'audio');
        }

        if ($updates) {
            $exposition->update($updates);
        }

        $this->reset([
            'title',
            'description',
            'is_public',
            'preset_theme',
            'thumbnail',
            'floor_texture',
            'ceiling_texture',
            'wall_texture',
            'ambient_track',
        ]);
        $this->is_public = true;
        $this->preset_theme = -1;

        $this->loadExpositions();
    }

    public function delete(int $expositionId): void
    {
        $userId = Auth::id();

        if (! $userId) {
            abort(403);
        }

        $exposition = Exposition::whereKey($expositionId)->first();

        if (! $exposition) {
            return;
        }

        if ($exposition->user_id !== $userId) {
            abort(403);
        }

        $exposition->delete();

        $this->loadExpositions();
    }

    private function loadExpositions(): void
    {
        $userId = Auth::id();

        $this->expositions = Exposition::query()
            ->when($userId, function ($query) use ($userId) {
                $query->where(function ($query) use ($userId) {
                    $query->where('is_public', true)
                        ->orWhere('user_id', $userId);
                });
            }, function ($query) {
                $query->where('is_public', true);
            })
            ->latest()
            ->with(['user:id,name'])
            ->withCount(['exhibits', 'likes'])
            ->get();
    }

    private function storeExpoAsset($file, int $expositionId, string $prefix, string $directory): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $filename = $prefix.'-'.Str::uuid().'.'.$extension;

        return $file->storeAs($directory.'/'.$expositionId, $filename, 'public');
    }
}
