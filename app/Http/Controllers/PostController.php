<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Pin;



    class PostController extends Controller
    {
        
        /**
         * Display a listing of the resource.
         */
        public function index()
        {
            //
        }

        /**
         * Show the form for creating a new resource.
         */
        public function create(Request $request)
        {
            $latitude = $request->query('lat');
            $longitude = $request->query('lng');
            $pinId = $request->query('pin_id');

            $pin = null;
            if ($pinId) {
                $pin = \App\Models\Pin::find($pinId);
            }

            return view('posts.create', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'pinId' => $pinId,
                'pin' => $pin,
                'pins' => \App\Models\Pin::all(),
            ]);
        }

        /**
         * Store a newly created resource in storage.
         */


    public function store(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'pin_id' => 'nullable|exists:pins,id',
        ]);

        // Initialize image path
        $imagePath = null;

        // Store image if present
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        // Get current user
        $user = auth()->user();

        // Set moderated status
        $moderated = ($user->role === 'admin' || $user->role === 'staff');

        // Make sure coordinates are set even if missing in request but pin_id is provided
        $latitude = $request->latitude;
        $longitude = $request->longitude;

        if ((!$latitude || !$longitude) && $request->filled('pin_id')) {
            $pin = Pin::find($request->pin_id);
            if ($pin) {
                $latitude = $pin->latitude;
                $longitude = $pin->longitude;
            }
        }

        // Create post
        Post::create([
            'title' => $request->title,
            'body' => $request->body,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'image' => $imagePath,
            'user_id' => $user->id,
            'moderated' => $moderated,
            'pin_id' => $request->pin_id, // optional pin association
        ]);

        return redirect()->route('dashboard')->with('success', 'Post created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        // Make sure the authenticated user owns the post
        if (auth()->id() !== $post->user_id) {
            abort(403, 'Unauthorized');
        }

        // Load the pin relationship
        $post->load('pin');

        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {

        if ($post->user_id !== auth()->id()) {
                abort(403);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required|string',
                'image' => 'nullable|image|max:2048',
            ]); 

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('post-images', 'public');
                $validated['image'] = $path;
            }

            $post->update($validated);

            return redirect()->route('dashboard')->with('success', 'Post updated.');
    }

    public function destroy($id)
    {
        $post = \App\Models\Post::findOrFail($id);
        $user = auth()->user();

        // Only allow if user owns the post or is admin
        if ($user->id !== $post->user_id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        // Delete image from storage if exists
        if ($post->image) {
            \Storage::delete('public/' . $post->image);
        }

        $post->delete();

        return response()->json(['success' => true]);
    }

    public function nearby(Request $request) //unused
    {
        $lat = (float) $request->query('lat');
        $lng = (float) $request->query('lng');

        if (!$lat || !$lng) {
            return response()->json([]);
        }

        $userId = auth()->id();

        $posts = \App\Models\Post::with(['user', 'comments.user'])
            ->whereBetween('latitude', [$lat - 1, $lat + 1])
            ->whereBetween('longitude', [$lng - 1, $lng + 1])
            ->where('moderated', true)
            ->get();

        $userId = auth()->id();

        $posts = $posts->map(function ($post) use ($userId) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'image' => $post->image,
                'latitude' => $post->latitude,
                'longitude' => $post->longitude,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
                'moderated' => $post->moderated,
                'user' => [
                    'id' => $post->user->id ?? null,
                    'name' => $post->user->name ?? 'Unknown',
                ],
                'is_owner' => $post->user_id === $userId,
                'comments' => $post->comments
                    ->filter(fn($c) => $c->moderated)
                    ->map(fn($c) => [
                        'id' => $c->id,
                        'body' => $c->body,
                        'created_at' => $c->created_at,
                        'user' => [
                            'id' => $c->user->id ?? null,
                            'name' => $c->user->name ?? 'Unknown',
                        ],
                    ])
                    ->values()
            ];
        });
        return response()->json($posts);
    }

    public function byPin(Request $request)
    {
        $pinId = $request->query('pin_id');
        $sortBy = $request->query('sort', 'latest'); // Default to 'latest'
        
        if (!$pinId) {
            return response()->json([]);
        }

        $userId = auth()->id();
        $user = auth()->user();
        
        // Check if user is admin or staff
        $userIsAdminOrStaff = $user && ($user->isAdmin() || $user->isStaff());

        // Start building the query
        $query = \App\Models\Post::with(['user', 'comments.user', 'pin', 'likes'])
            ->where('pin_id', $pinId);

        // Apply moderation filter based on sort type and user role
        switch ($sortBy) {
            case 'moderated_only':
                if ($userIsAdminOrStaff) {
                    $query->where('moderated', true);
                } else {
                    // Non-admin users can only see moderated posts anyway
                    $query->where('moderated', true);
                }
                $query->orderBy('created_at', 'desc');
                break;
                
            case 'unmoderated_only':
                if ($userIsAdminOrStaff) {
                    // Posts that are null or false (pending approval)
                    $query->where(function($q) {
                        $q->whereNull('moderated')
                        ->orWhere('moderated', false);
                    });
                } else {
                    // Non-admin users can only see moderated posts
                    $query->where('moderated', true);
                }
                $query->orderBy('created_at', 'desc');
                break;
                
            case 'unmoderated_posts':
                if ($userIsAdminOrStaff) {
                    // Posts that are specifically false (explicitly unmoderated)
                    $query->where('moderated', false);
                } else {
                    // Non-admin users can only see moderated posts
                    $query->where('moderated', true);
                }
                $query->orderBy('created_at', 'desc');
                break;
                
            case 'all_posts':
                if ($userIsAdminOrStaff) {
                    // No moderation filter - show all posts
                } else {
                    // Non-admin users can only see moderated posts
                    $query->where('moderated', true);
                }
                $query->orderBy('created_at', 'desc');
                break;
                
            case 'latest':
                // Default behavior - non-admin users only see moderated posts
                if (!$userIsAdminOrStaff) {
                    $query->where('moderated', true);
                }
                $query->orderBy('created_at', 'desc');
                break;
                
            case 'oldest':
                if (!$userIsAdminOrStaff) {
                    $query->where('moderated', true);
                }
                $query->orderBy('created_at', 'asc');
                break;
                
            case 'most_liked':
                if (!$userIsAdminOrStaff) {
                    $query->where('moderated', true);
                }
                // Use withCount to get likes count and sort by it
                $query->withCount('likes')
                    ->orderBy('likes_count', 'desc')
                    ->orderBy('created_at', 'desc'); // Secondary sort by latest
                break;
                
            case 'most_commented':
                if (!$userIsAdminOrStaff) {
                    $query->where('moderated', true);
                }
                // Use withCount to get comments count and sort by it
                $query->withCount(['comments as comments_count' => function ($q) use ($userIsAdminOrStaff) {
                    if (!$userIsAdminOrStaff) {
                        $q->where('moderated', true); // Only count moderated comments for non-admin
                    }
                }])
                ->orderBy('comments_count', 'desc')
                ->orderBy('created_at', 'desc'); // Secondary sort by latest
                break;
                
            default:
                // Default behavior - non-admin users only see moderated posts
                if (!$userIsAdminOrStaff) {
                    $query->where('moderated', true);
                }
                $query->orderBy('created_at', 'desc');
        }

        $posts = $query->get();

        $posts = $posts->map(function ($post) use ($userId, $userIsAdminOrStaff) {
            // Get comments based on user role
            $comments = $userIsAdminOrStaff 
                ? $post->comments  // Admin/staff can see all comments
                : $post->comments->filter(fn($c) => $c->moderated); // Others only see moderated
            
            return [
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
                'image' => $post->image,
                
                'is_liked' => $post->likes->contains('id', $userId),
                'likes_count' => $post->likes->count(),
                'comments_count' => $comments->count(), // Comments count based on user role

                'latitude' => $post->latitude,
                'longitude' => $post->longitude,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
                'moderated' => $post->moderated,
                'pin_id' => $post->pin_id,
                'pin' => [
                    'id' => $post->pin->id ?? null,
                    'label' => $post->pin->label ?? null,
                    'latitude' => $post->pin->latitude ?? null,
                    'longitude' => $post->pin->longitude ?? null,
                ],
                'user' => [
                    'id' => $post->user->id ?? null,
                    'name' => $post->user->name ?? 'Unknown',
                ],
                'is_owner' => $post->user_id === $userId,
                'comments' => $comments
                    ->map(fn($c) => [
                        'id' => $c->id,
                        'body' => $c->body,
                        'created_at' => $c->created_at,
                        'moderated' => $userIsAdminOrStaff ? $c->moderated : null, // Show moderation status to admin/staff
                        'user' => [
                            'id' => $c->user->id ?? null,
                            'name' => $c->user->name ?? 'Unknown',
                        ],
                    ])
                    ->values()
            ];
        });
        
        return response()->json($posts);
    }

    public function approve($id)
    {
        $post = Post::findOrFail($id);
        $post->moderated = true;
        $post->save();

        return redirect()->back()->with('success', 'Post approved.');
    }

    // Add this method to PostController
    public function toggleModeration(Post $post)
    {
        $user = auth()->user();
        
        // Optional: Ensure only admin/staff can moderate
        if (!$user->isAdmin() && !$user->isStaff()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->moderated = !$post->moderated;
        $post->save();

        return response()->json([
            'message' => 'Post moderation status updated.',
            'moderated' => $post->moderated,
            'post_id' => $post->id,
        ]);
    }

        
    public function dashboard()
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isStaff()) {
            // Admins and staff see all posts
            $unmoderatedPosts = Post::where('moderated', false)->latest()->get();
            $moderatedPosts = Post::where('moderated', true)->latest()->get();
        } else {
            // Regular users see only their own posts
            $unmoderatedPosts = Post::where('user_id', $user->id)
                                    ->where('moderated', false)
                                    ->latest()
                                    ->get();

            $moderatedPosts = Post::where('user_id', $user->id)
                                ->where('moderated', true)
                                ->latest()
                                ->get();
        }

        return view('dashboard', compact('unmoderatedPosts', 'moderatedPosts'));
    }

}
