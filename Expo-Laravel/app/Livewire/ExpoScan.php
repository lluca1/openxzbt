<?php

namespace App\Livewire;

use App\Models\Exposition;
use Livewire\Component;

class ExpoScan extends Component
{
    /**
     * The search input value.
     */
    public string $query = '';

    /**
     * The sort order: 'likes' or 'recent'
     */
    public string $sortBy = 'likes';

    /**
     * Clear the search box.
     */
    public function resetSearch(): void
    {
        $this->query = '';
    }

    public function render()
    {
        $results = [];

        $q = trim($this->query);

        if ($q !== '') {
            // Start with public expos only
            $builder = Exposition::query()
                ->where('is_public', true)
                ->with('user');

            // If query is purely numeric → ID search + loose matches
            if (ctype_digit($q)) {
                $builder->where(function ($qBuilder) use ($q) {
                    $qBuilder
                        ->where('id', (int) $q)
                        ->orWhere('title', 'like', "%{$q}%")
                        ->orWhereHas('user', function ($userQ) use ($q) {
                            $userQ->where('name', 'like', "%{$q}%");
                        });
                });
            } elseif (mb_strlen($q) >= 2) {
                // Normal text search (require at least 2 chars for fuzzy)
                $builder->where(function ($qBuilder) use ($q) {
                    $qBuilder
                        ->where('title', 'like', "%{$q}%")
                        ->orWhereHas('user', function ($userQ) use ($q) {
                            $userQ->where('name', 'like', "%{$q}%");
                        });
                });
            } else {
                // Single non-numeric character → do not query
                $builder = null;
            }

            if ($builder) {
                // Always load likes + exhibits count for sorting and display
                $builder->withCount(['likes', 'exhibits']);

                // Apply sort order
                if ($this->sortBy === 'recent') {
                    $builder->orderByDesc('created_at');
                } else {
                    // Default to likes (popularity)
                    $builder->orderByDesc('likes_count');
                }

                $results = $builder->limit(10)->get();
            }
        }

        return view('livewire.expo-scan', [
            'results' => $results,
            'query'   => $this->query,
            'sortBy'  => $this->sortBy,
        ]);
    }
}
