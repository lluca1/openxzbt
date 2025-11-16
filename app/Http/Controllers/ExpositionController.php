<?php

namespace App\Http\Controllers;

use App\Models\Exposition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ExpositionController extends Controller
{
    public function destroy(Exposition $exposition): RedirectResponse
    {
        // Only the owner can delete
        if (Auth::id() !== $exposition->user_id) {
            abort(403, 'Unauthorized');
        }
        
        $exposition->delete();
        return redirect()->route('home')->with('status', 'exposition-deleted');
    }
}
