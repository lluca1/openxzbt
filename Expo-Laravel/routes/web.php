<?php

use App\Livewire\ExpositionExhibits;
use App\Livewire\ExpositionsManager;
use App\Http\Controllers\ExhibitDownloadController;
use App\Http\Controllers\ExpositionController;
use Illuminate\Support\Facades\Route;
use App\Models\Exposition;

/*
|--------------------------------------------------------------------------
| Home Page (Guest + User)
|--------------------------------------------------------------------------
|
| Loads the new home.blade.php and shows:
| - thumbnails + preface for guests
| - full access + "your museums" for logged users
|
*/

Route::get('/', function () {
    $expositions = Exposition::with('user')
        ->withCount(['exhibits', 'likes'])
        ->where('is_public', true)
        ->orderByDesc('likes_count')
        ->take(9)
        ->get();

    return view('home', compact('expositions'));
})->name('home');

// Temporary tutorial / game download page
Route::view('/what-is-here', 'what-is-here')->name('what.is.here');


/*
|--------------------------------------------------------------------------
| Dashboard (Logged in only)
|--------------------------------------------------------------------------
*/

Route::view('/dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::view('/profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


/*
|--------------------------------------------------------------------------
| Expositions 
|--------------------------------------------------------------------------
|
| Guests can preview the thumbnails in home.blade.php,
| but these pages require login:
| - /expositions
| - /expositions/{id}
|
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/expositions', ExpositionsManager::class)->name('expositions.index');
    Route::get('/expositions/{exposition}', ExpositionExhibits::class)->name('expositions.show');
    Route::delete('/expositions/{exposition}', [ExpositionController::class, 'destroy'])->name('expositions.destroy');
    Route::get('/expositions/{exposition}/exhibits/{exhibit}/download', [ExhibitDownloadController::class, 'downloadExhibit'])->name('exhibits.download');
});


/*
|--------------------------------------------------------------------------
| Authentication Routes (Breeze)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
