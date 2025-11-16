<?php

/** @var \Tests\TestCase $this */

use App\Livewire\ExpositionExhibits;
use App\Models\Exposition;
use App\Models\User;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('allows an authenticated user to like and unlike an exposition', function () {
    $user = User::factory()->create();
    $exposition = Exposition::factory()->create(['is_public' => true]);

    actingAs($user);

    $component = Livewire::test(ExpositionExhibits::class, ['exposition' => $exposition]);

    $component->call('toggleLike');

    expect($exposition->likes()->where('user_id', $user->id)->exists())->toBeTrue();

    $component->call('toggleLike');

    expect($exposition->likes()->where('user_id', $user->id)->exists())->toBeFalse();
});

it('allows a curator to post and delete comments', function () {
    $user = User::factory()->create();
    $exposition = Exposition::factory()->create(['is_public' => true]);

    actingAs($user);

    Livewire::test(ExpositionExhibits::class, ['exposition' => $exposition])
        ->set('commentBody', 'Stunning work!')
        ->call('postComment');

    assertDatabaseHas('exposition_comments', [
        'exposition_id' => $exposition->id,
        'user_id' => $user->id,
        'body' => 'Stunning work!',
    ]);

    $commentId = $exposition->comments()->first()->id;

    Livewire::test(ExpositionExhibits::class, ['exposition' => $exposition])
        ->call('deleteComment', $commentId);

    assertDatabaseMissing('exposition_comments', [
        'id' => $commentId,
    ]);
});
