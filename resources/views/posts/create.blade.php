@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-gray-100 py-8">
    <div class="max-w-3xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Create New Post</h1>
            <p class="text-gray-600"></p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="p-8">
                @csrf

                <!-- Image Upload Section -->
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <span class="flex items-center gap-2">
                            🖼️
                            Image
                        </span>
                    </label>
                    
                    <!-- Simplified Image Upload -->
                    <div class="space-y-4">
                        <input type="file" name="image" id="imageInput" accept="image/*" 
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer border border-gray-300 rounded-xl p-3">
                        
                        <!-- Image Preview -->
                        <div id="imagePreview" class="hidden">
                            <div class="relative inline-block">
                                <img id="previewImg" class="max-w-full h-64 object-cover rounded-xl shadow-lg">
                                <button type="button" id="removeImage" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors">
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Title Input -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <span class="flex items-center gap-2">
                            ✏️
                            Title <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <input name="title" type="text" value="{{ old('title') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-gray-400" 
                           placeholder="Give your post a catchy title..."
                           required>
                </div>

                <!-- Body Textarea -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <span class="flex items-center gap-2">
                            📝
                            Description
                        </span>
                    </label>
                    <textarea name="body" rows="5" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-gray-400 resize-none" 
                              placeholder="Tell us more about this moment...">{{ old('body') }}</textarea>
                </div>

                @php
                    $selectedPin = $pins->firstWhere('id', $pinId);
                @endphp

                <!-- Pin Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <span class="flex items-center gap-2">
                            📌
                            Location Pin
                        </span>
                    </label>
                    
                    @if ($selectedPin)
                        <!-- Read-only Pin Display -->
                        <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">{{ $selectedPin->label ?? 'Pin #' . $selectedPin->id }}</div>
                                <div class="text-sm text-gray-600">This post is attached to a specific pin</div>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                🔒 Locked
                            </span>
                        </div>
                        <input type="hidden" name="pin_id" value="{{ $selectedPin->id }}">
                    @else
                        <!-- Pin Selection Dropdown -->
                        <select name="pin_id" id="pin_id" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white">
                            <option value="">🌍 Use current coordinates instead</option>
                            @foreach ($pins as $pinOption)
                                <option value="{{ $pinOption->id }}" {{ old('pin_id') == $pinOption->id ? 'selected' : '' }}>
                                    📍 {{ $pinOption->label ?? 'Pin #' . $pinOption->id }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- Coordinates Display -->
                @if (request()->has('lat') && request()->has('lng'))
                    <div class="hidden mb-8">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                Coordinates
                            </span>
                        </label>
                        <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="font-mono text-sm font-semibold text-gray-900">
                                    Lat: {{ request('lat') }}, Lng: {{ request('lng') }}
                                </div>
                                <div class="text-sm text-gray-600">Geographic coordinates for this post</div>
                            </div>
                        </div>
                        <input type="hidden" name="latitude" value="{{ request('lat') }}">
                        <input type="hidden" name="longitude" value="{{ request('lng') }}">
                    </div>
                @endif

                <!-- Submit Button -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <button type="button" onclick="history.back()" 
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 font-medium">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Publish Post
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

    // Handle URL parameters for pin selection
    const urlParams = new URLSearchParams(window.location.search); 
    const pinId = urlParams.get('pin_id');

    if (pinId) {
        const pinSelect = document.getElementById('pin_id');
        if (pinSelect) {
            pinSelect.value = pinId;
        }
    }

    // Simple image preview functionality
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file.');
                this.value = '';
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must be less than 10MB.');
                this.value = '';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove image functionality
    removeImageBtn.addEventListener('click', function() {
        imageInput.value = '';
        imagePreview.classList.add('hidden');
        previewImg.src = '';
    });
});
</script>
@endsection