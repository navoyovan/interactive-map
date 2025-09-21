<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Pin;


class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
    // Get user's pins
        $pins = \App\Models\Pin::where('user_id', $user->id)->get();

        // Get user's moderated posts (you can adjust the logic as needed)
        $moderatedPosts = \App\Models\Post::where('user_id', $user->id)
            ->where('moderated', true)
            ->get();

        if ($user->isAdmin() || $user->isStaff()) {
            // admins and staff see all posts
            $unmoderatedPosts = Post::with(['user', 'pin'])->where('moderated', false)->latest()->get();
            $moderatedPosts = Post::with(['user', 'pin'])->where('moderated', true)->latest()->get();
        } else {
            // regular users see only their own posts
            $unmoderatedPosts = Post::with(['user', 'pin', 'comments.user'])
                                    ->where('user_id', $user->id)
                                    ->where('moderated', false)
                                    ->latest()
                                    ->get();

            $moderatedPosts = Post::with(['user', 'pin', 'comments.user'])
                                  ->where('user_id', $user->id)
                                  ->where('moderated', true)
                                  ->latest()
                                  ->get();
        }

        // Get user's comments with related post and pin information
        $userComments = Comment::with(['post.pin', 'user'])
                               ->where('user_id', $user->id)
                               ->latest()
                               ->get();

        return view('dashboard', compact('unmoderatedPosts', 'moderatedPosts', 'userComments'));
    }

    public function jsonPosts()
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isStaff()) {
            $posts = Post::with(['user', 'pin'])->latest()->get();
        } else {
            $posts = Post::with(['user', 'pin'])
                        ->where('user_id', $user->id)
                        ->latest()
                        ->get();
        }

        return response()->json($posts);
    }

}