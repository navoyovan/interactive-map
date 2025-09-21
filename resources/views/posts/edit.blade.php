@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-gray-100 py-8">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Post</h1>
            <p class="text-gray-600">You are making changes to "{{ $post->title }}"</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <form method="POST" action="{{ route('posts.update', $post->id) }}" enctype="multipart/form-data" class="p-8">
                @csrf
                @method('PUT')

                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <span class="flex items-center gap-2">
                            🖼️
                            Image (Optional: Upload a new file to replace the current one)
                        </span>
                    </label>
                    
                    <div class="space-y-4">
                        <input type="file" name="image" id="imageInput" accept="image/*" 
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer border border-gray-300 rounded-xl p-3">
                        
                        <div id="imagePreview" class="{{ $post->image ? '' : 'hidden' }}">
                            <div class="relative inline-block">
                                <img id="previewImg" src="{{ $post->image ? asset('storage/' . $post->image) : '' }}" class="max-w-full h-64 object-cover rounded-xl shadow-lg">
                                <button type="button" id="removeImage" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors">
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <span class="flex items-center gap-2">
                            ✏️
                            Title <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <input name="title" type="text" value="{{ old('title', $post->title) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-gray-400" 
                           placeholder="Give your post a catchy title..."
                           required>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <span class="flex items-center gap-2">
                            📝
                            Description
                        </span>
                    </label>
                    <textarea name="body" rows="5" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-gray-400 resize-none" 
                              placeholder="Tell us more about this moment...">{{ old('body', $post->body) }}</textarea>
                </div>

                @if($post->pin)
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <span class="flex items-center gap-2">
                            📌
                            Location Pin
                        </span>
                    </label>
                    
                    <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">{{ $post->pin->label ?? 'Pin #' . $post->pin->id }}</div>
                            <div class="text-sm text-gray-600">This post is attached to a specific pin</div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            🔒 Locked
                        </span>
                    </div>
                </div>
                @endif
                
                <input type="hidden" name="pin_id" value="{{ $post->pin_id }}">
                <input type="hidden" name="latitude" value="{{ $post->latitude }}">
                <input type="hidden" name="longitude" value="{{ $post->longitude }}">

                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ url()->previous() }}"
                       class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 font-medium">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Changes
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const removeImageBtn = document.getElementById('removeImage');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    // This button now clears the NEWLY selected file, or hides the old preview
    removeImageBtn.addEventListener('click', function() {
        imageInput.value = ''; // Clear the file input
        imagePreview.classList.add('hidden'); // Hide the preview container
        previewImg.src = ''; // Clear the image source
        // Note: This doesn't delete the existing image on the server.
        // To do that, you'd need a separate mechanism, like a checkbox.
    });
});
</script>
@endsection