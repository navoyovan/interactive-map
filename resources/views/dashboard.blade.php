@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Posts</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        <p>{{ $moderatedPosts->count() + $unmoderatedPosts->count() }} Posts • 
                           {{ $userComments->count() }} Comments</p>
                    </div>
                    <a href="{{ route('dashboard') }}" class="text-sm px-3 py-1 bg-gray-100 border rounded hover:bg-gray-200">
                        🔄 Refresh
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-4xl mx-auto p-4">
        <!-- Approved Posts -->
        <h2 class="text-xl font-semibold mt-6 mb-4">✅ Approved Posts</h2>
        @forelse ($moderatedPosts as $post)
            <div class="p-4 bg-white rounded-lg shadow-sm border mb-4">
                <h3 class="font-semibold text-lg mb-2">{{ $post->title }}</h3>
                <p class="text-gray-700 mb-3">{{ $post->body }}</p>
                
                @if ($post->image)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $post->image) }}" 
                             alt="Post Image" 
                             class="rounded-md max-h-48 object-cover">
                    </div>
                @endif

                <div class="flex items-center text-sm text-gray-500 mb-3">
                    <span>Pin: 
                        @if ($post->pin)
                            <a href="{{ url('/?pin_id=' . $post->pin->id) }}" class="text-blue-500 underline">
                                {{ $post->pin->label ?? 'Unnamed Pin' }}
                            </a>
                        @else
                            <span class="text-gray-400">No pin linked</span>
                        @endif
                    </span>
                    <span class="ml-4 text-green-600 font-medium">• Approved</span>
                </div>

                <!-- Comments -->
                @if($post->comments && $post->comments->count())
                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Comments ({{ $post->comments->count() }})</h4>
                        @foreach($post->comments as $comment)
                            <div class="mb-2 last:mb-0">
                                <p class="text-sm text-gray-800">💬 {{ $comment->body }}</p>
                                <p class="text-xs text-gray-500">— {{ $comment->user->name }} • {{ $comment->created_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Actions -->
                <div class="mt-3 flex gap-3">
                    <a href="{{ route('posts.edit', $post) }}" class="text-blue-500 hover:underline text-sm">Edit</a>
                    <form method="POST" action="{{ route('posts.destroy', $post) }}" 
                          onsubmit="return confirm('Delete this post?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:underline text-sm">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-8 bg-white rounded-lg shadow-sm border text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
                <p class="text-gray-500">No approved posts yet.</p>
            </div>
        @endforelse

        <!-- Pending/Unmoderated Posts -->
        <h2 class="text-xl font-semibold mt-8 mb-4">🕒 Pending Approval</h2>
        @forelse ($unmoderatedPosts as $post)
            <div class="p-4 bg-yellow-50 rounded-lg shadow-sm border border-yellow-200 mb-4">
                <h3 class="font-semibold text-lg mb-2">{{ $post->title }}</h3>
                <p class="text-gray-700 mb-3">{{ $post->body }}</p>
                
                @if ($post->image)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $post->image) }}" 
                             alt="Post Image" 
                             class="rounded-md max-h-48 object-cover">
                    </div>
                @endif

                <div class="flex items-center text-sm text-gray-500 mb-3">
                    <span>Pin: 
                        @if ($post->pin)
                            <a href="{{ url('/?pin_id=' . $post->pin->id) }}" class="text-blue-500 underline">
                                {{ $post->pin->label ?? 'Unnamed Pin' }}
                            </a>
                        @else
                            <span class="text-gray-400">No pin linked</span>
                        @endif
                    </span>
                    <span class="ml-4 text-yellow-600 font-medium">• Pending Approval</span>
                </div>

                <!-- Comments -->
                @if($post->comments && $post->comments->count())
                    <div class="mt-3 p-3 bg-white rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Comments ({{ $post->comments->count() }})</h4>
                        @foreach($post->comments as $comment)
                            <div class="mb-2 last:mb-0">
                                <p class="text-sm text-gray-800">💬 {{ $comment->body }}</p>
                                <p class="text-xs text-gray-500">— {{ $comment->user->name }} • {{ $comment->created_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Actions -->
                <div class="mt-3 flex gap-3">
                    <a href="{{ route('posts.edit', $post) }}" class="text-blue-500 hover:underline text-sm">Edit</a>
                    <form method="POST" action="{{ route('posts.destroy', $post) }}" 
                          onsubmit="return confirm('Delete this post?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:underline text-sm">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-8 bg-white rounded-lg shadow-sm border text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
                <p class="text-gray-500">No posts waiting for approval.</p>
            </div>
        @endforelse

        <!-- Your Comments Section -->
        <h2 class="text-xl font-semibold mt-8 mb-4">💬 Your Comments</h2>
        @forelse ($userComments as $comment)
            <div class="p-3 bg-gray-100 rounded-lg shadow-sm mb-3">
                <p class="text-gray-800 mb-2">{{ $comment->body }}</p>
                <div class="flex justify-between items-center">
                    <p class="text-xs text-gray-600">
                        On post: 
                        @if($comment->post)
                            <a href="{{ route('posts.show', $comment->post_id) }}" class="underline text-blue-600">
                                {{ $comment->post->title }}
                            </a>
                        @else
                            <span class="text-gray-400">Deleted Post</span>
                        @endif
                        • {{ $comment->created_at->diffForHumans() }}
                    </p>
                    <form method="POST" action="{{ route('comments.destroy', $comment) }}" 
                          onsubmit="return confirm('Delete this comment?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-8 bg-white rounded-lg shadow-sm border text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                </div>
                <p class="text-gray-500 mb-4">You haven't posted any comments yet.</p>
            </div>
        @endforelse

        <!-- Create New Post Button -->
        <div class="mt-8 text-center">
            <a href="{{ route('posts.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Post
            </a>
        </div>
    </div>
</div>
@endsection