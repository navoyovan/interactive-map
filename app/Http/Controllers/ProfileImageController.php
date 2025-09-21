<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileImageController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:2048'],
        ]);

        $user = Auth::user();

        // Delete old image if exists
        if ($user->image) {
            Storage::delete($user->image);
        }

        $path = $request->file('image')->store('profile-images', 'public');

        $user->image = $path;
        $user->save();

        return redirect()->back()->with('success', 'Profile image updated!');
    }
}
