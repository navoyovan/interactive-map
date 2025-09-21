<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    public function store(Request $request, $postId) // Or public function store(Request $request, Post $post) if using Route Model Binding
    {
        $request->validate([
            'body' => 'required|string|max:1000', // Added max length for comments
        ]);

        $comment = Comment::create([
            'post_id' => $postId, // Or $post->id if using Route Model Binding
            'user_id' => auth()->id(),
            'body' => $request->body,
            'moderated' => true, // Assuming comments are immediately moderated
        ]);

        // Eager-load user for frontend rendering
        $comment->load('user');

        // Always return JSON for AJAX requests, based on frontend expectation
        // Remove the if ($request->wantsJson()) block if you only expect JSON for this route.
        return response()->json([
            'success' => true, // <-- Add this
            'message' => 'Comment added successfully!', // <-- Add this
            'comment' => $comment->toArray(), // <-- Return the full comment object here
            // Important: Use toArray() to ensure relations like 'user' are included in the array form
        ], 201); // 201 Created status code for successful creation
    }


    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $user = auth()->user();

        // Allow deletion if user is the comment owner, or has staff/admin role
        if ($comment->user_id !== $user->id && !in_array($user->role, ['admin', 'staff'])) {
            // Return JSON error for AJAX requests
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $comment->delete();

        // Return JSON success for AJAX requests
        return response()->json(['success' => true, 'message' => 'Comment deleted.'], 200); // 200 OK
    }
}

