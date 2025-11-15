<?php

namespace App\Livewire;

use App\Models\Exposition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ExpositionExhibits extends Component
{
    use WithFileUploads;

    public Exposition $exposition;

    public $exhibits = [];

    public bool $isOwner = false;

    public bool $showThumbnailEditor = false;

    public int $likesCount = 0;

    public bool $likedByUser = false;

    public array $comments = [];

    #[Rule('nullable|string|max:1000')]
    public string $commentBody = '';

    public bool $canInteract = false;

    #[Rule('required|string|max:255')]
    public string $title = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('required|file|mimes:obj|max:51200')]
    public $modelFile;

    #[Rule('required|file|mimes:mtl|max:51200')]
    public $materialFile;

    #[Rule('nullable|array|max:10')]
    public array $textureFiles = [];

    #[Rule('nullable|image|max:4096')]
    public $thumbnail;

    public function mount(Exposition $exposition): void
    {
        $this->exposition = $exposition;
        $this->isOwner = Auth::id() === $this->exposition->user_id;
        $this->canInteract = Auth::check();

        if (! $this->isOwner && ! $this->exposition->is_public) {
            abort(403);
        }

        $this->loadExhibits();
        $this->loadEngagement();
    }

    public function render()
    {
        return view('livewire.exposition-exhibits');
    }

    public function save(): void
    {
        $userId = $this->ensureExpositionOwner();
        $this->validate();
        $this->validate([
            'textureFiles' => 'nullable|array|max:10',
            'textureFiles.*' => 'file|mimes:png,jpg,jpeg,bmp,webp|max:20480',
        ]);

        $position = ($this->exposition->exhibits()->max('position') ?? -1) + 1;

        $mimeType = $this->modelFile->getMimeType();

        $exhibit = $this->exposition->exhibits()->create([
            'user_id' => $userId,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'media_type' => '3d-model',
            'media_path' => '',
            'mime_type' => $mimeType,
            'position' => $position,
        ]);

        $folder = 'models/'.$exhibit->id;
        $filename = (string) $exhibit->id;

        Storage::disk('public')->makeDirectory($folder);

        $this->modelFile->storeAs($folder, $filename.'.obj', 'public');
        $this->materialFile->storeAs($folder, $filename.'.mtl', 'public');

        foreach ($this->textureFiles as $index => $texture) {
            $originalName = $texture->getClientOriginalName();

            if (! $originalName) {
                throw ValidationException::withMessages([
                    "textureFiles.$index" => 'Textures must retain their original filenames.',
                ]);
            }

            $texture->storeAs($folder, basename($originalName), 'public');
        }

        $exhibit->update(['media_path' => $folder]);

        $this->reset(['title', 'description', 'modelFile', 'materialFile', 'textureFiles']);

        $this->loadExhibits();
        $this->exposition->refresh();
    }

    public function toggleLike(): void
    {
        if (! $this->canInteract) {
            return;
        }

        $userId = Auth::id();

        $existing = $this->exposition->likes()->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
        } else {
            $this->exposition->likes()->create([
                'user_id' => $userId,
            ]);
        }

        $this->loadEngagement();
    }

    public function postComment(): void
    {
        $userId = $this->ensureAuthenticatedUser();

        $this->validate([
            'commentBody' => 'required|string|min:3|max:1000',
        ]);

        $this->exposition->comments()->create([
            'user_id' => $userId,
            'body' => trim($this->commentBody),
        ]);

        $this->reset('commentBody');
        $this->loadEngagement();
    }

    public function deleteComment(int $commentId): void
    {
        $userId = Auth::id();

        if (! $userId) {
            abort(403);
        }

        $comment = $this->exposition->comments()->whereKey($commentId)->first();

        if (! $comment) {
            return;
        }

        if ($comment->user_id !== $userId && $this->exposition->user_id !== $userId) {
            abort(403);
        }

        $comment->delete();
        $this->loadEngagement();
    }

    public function delete(int $exhibitId): void
    {
        $userId = $this->ensureExpositionOwner();
        $exhibit = $this->exposition->exhibits()->whereKey($exhibitId)->first();

        if (! $exhibit) {
            return;
        }

        if ($exhibit->user_id !== $userId) {
            abort(403);
        }

        if ($exhibit->media_path) {
            if (Str::endsWith($exhibit->media_path, '.obj')) {
                Storage::disk('public')->delete($exhibit->media_path);
            } else {
                Storage::disk('public')->deleteDirectory($exhibit->media_path);
            }
        }

        $exhibit->delete();

        $this->loadExhibits();
        $this->exposition->refresh();
    }

    public function setPresetTheme(int $value): void
    {
        $this->ensureExpositionOwner();

        if (! in_array($value, [-1, 0, 1, 2], true)) {
            return;
        }

        $this->exposition->preset_theme = $value;
        $this->exposition->save();
        $this->exposition->refresh();
    }

    private function loadExhibits(): void
    {
        $this->exhibits = $this->exposition->exhibits()->get();
    }

    private function loadEngagement(): void
    {
        $userId = Auth::id();

        $this->canInteract = (bool) $userId;
        $this->likesCount = $this->exposition->likes()->count();
        $this->likedByUser = $userId ? $this->exposition->likes()->where('user_id', $userId)->exists() : false;

        $comments = $this->exposition->comments()
            ->with('user:id,name')
            ->latest()
            ->limit(25)
            ->get();

        $this->comments = $comments->map(function ($comment) use ($userId) {
            $author = $comment->user;

            return [
                'id' => $comment->id,
                'body' => $comment->body,
                'user_name' => $author?->name ?? 'Anonymous curator',
                'user_handle' => $author && $author->name ? '@'.Str::slug($author->name, '_') : '@anon',
                'timestamp' => $comment->created_at->diffForHumans(),
                'can_delete' => $userId && ($comment->user_id === $userId || $this->exposition->user_id === $userId),
            ];
        })->toArray();
    }

    public function saveThumbnail(): void
    {
        $userId = $this->ensureExpositionOwner();
        $this->validate(['thumbnail' => 'nullable|image|max:4096']);

        if (! $this->thumbnail) {
            return;
        }

        // Delete old thumbnail if it exists
        if ($this->exposition->cover_image_path) {
            Storage::disk('public')->delete($this->exposition->cover_image_path);
        }

        $extension = $this->thumbnail->getClientOriginalExtension() ?: $this->thumbnail->extension();
        $filename = 'cover-'.Str::uuid().'.'.$extension;
        $path = $this->thumbnail->storeAs('expositions/'.$this->exposition->id, $filename, 'public');

        $this->exposition->update(['cover_image_path' => $path]);
        $this->exposition->refresh();

        $this->reset(['thumbnail']);
    }

    public function clearThumbnail(): void
    {
        $userId = $this->ensureExpositionOwner();

        if ($this->exposition->cover_image_path) {
            Storage::disk('public')->delete($this->exposition->cover_image_path);
            $this->exposition->update(['cover_image_path' => null]);
            $this->exposition->refresh();
        }

        $this->reset(['thumbnail']);
    }

    private function ensureExpositionOwner(): int
    {
        $userId = Auth::id();

        if (! $userId || $this->exposition->user_id !== $userId) {
            abort(403);
        }

        return $userId;
    }

    private function ensureAuthenticatedUser(): int
    {
        $userId = Auth::id();

        if (! $userId) {
            throw ValidationException::withMessages([
                'commentBody' => 'You need to sign in to comment on an exposition.',
            ]);
        }

        return $userId;
    }
}
