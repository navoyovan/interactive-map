<?php

// app/Http/Controllers/LikeController.php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle(Request $request, Post $post)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to like posts'
            ], 401);
        }

        $user = Auth::user();

        // Toggle like
        $wasLiked = $post->likes()->where('user_id', $user->id)->exists();

        if ($wasLiked) {
            $post->likes()->detach($user->id);
        } else {
            $post->likes()->attach($user->id);
        }

        // Manually update the likes_count column
        $post->likes_count = $post->likes()->count();
        $post->save();

        return response()->json([
            'success' => true,
            'is_liked' => !$wasLiked,
            'likes_count' => $post->likes_count,
            'message' => !$wasLiked ? 'Post liked!' : 'Post unliked!'
        ]);
    }

}