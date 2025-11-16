<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpositionLayoutRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('playerSpawn') && ! $this->has('player_spawn')) {
            $this->merge([
                'player_spawn' => $this->input('playerSpawn'),
            ]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'player_spawn' => ['required', 'array', 'size:3'],
            'player_spawn.*' => ['numeric'],

            'tiles' => ['required', 'array'],
            'tiles.*.id' => ['required', 'string', 'max:255'],
            'tiles.*.type' => ['required', 'integer', 'between:0,65535'],
            'tiles.*.has_exhibit' => ['required', 'boolean'],
            'tiles.*.position' => ['required', 'array', 'size:3'],
            'tiles.*.position.*' => ['numeric'],
            'tiles.*.rotation' => ['required', 'array', 'size:3'],
            'tiles.*.rotation.*' => ['numeric'],

            'exhibits' => ['nullable', 'array'],
            'exhibits.*.position' => ['required', 'array', 'size:3'],
            'exhibits.*.position.*' => ['numeric'],
            'exhibits.*.size' => ['required', 'numeric', 'min:0'],
        ];
    }
}
