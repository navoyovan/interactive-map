<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=0.50"/>
        <title>Interactive Map</title>
        
        <!-- Leaflet -->
        <link rel="stylesheet" href="/offlined/leaflet.css" />
        <script src="/offlined/leaflet.js"></script>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            html, body { margin: 0; padding: 0; height: 100%; }
            
            #map {  position: absolute;
                width: 100vw; 
                height: 100vh; 
                z-index: 1; 
                background-color: #dedede; 
                margin: 0;
                height: 100%;
                width: 100%
            }
            
            #dropdownMenu { z-index: 3000; display: none; }
            
            #pinTab {
                position: fixed;
                top: 0;
                right: -24rem;
                width: 24rem;
                height: 100vh;
                background: white;
                box-shadow: -2px 0 5px rgba(0,0,0,0.2);
                transition: right 0.3s ease-in-out;
                z-index: 2000;
            }
            
            .map-add-pin-mode {
                cursor: url('\img\markerd-add.png') 16 32, auto; 
            }
            
            .grayscale-icon img {
                filter: grayscale(100%);
            } 
            
            .tooltip {
                opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .tooltip-group:hover .tooltip {
            opacity: 1;
            visibility: visible;
        }
        .line-spacer {
            width: 20px;
            height: 2px;
            background: linear-gradient(to bottom, transparent, rgba(255,255,255,0.3), transparent);
            margin: 4px auto;
            align-self: center;
        }
        
        .custom-icon {
            cursor: pointer;
        }
        
        .pin-image {
            border-radius: 50%;
            transform-origin: center bottom; /* Scale from bottom center for natural pin effect */
        }
        
        .pin-image.scale-125 {
            transform: scale(1.25);
        }
        
        .grayscale-icon .pin-image {
            filter: grayscale(100%);
        }
        
        .pin-image {
            transition: all 0.2s ease-in-out;
        }
        
        banner-image {
            min-width: 300px; 
            aspect-ratio: 4 / 3; 
            width: 100%; 
            max-width: 500px; 
            object-fit: cover; 
        }
        
        @keyframes shake-draggable {
            0%, 100% { transform: scale(1.25) translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: scale(1.25) translateX(-2px); }
            20%, 40%, 60%, 80% { transform: scale(1.25) translateX(2px); }
        }
        
        .pin-draggable {
            animation: shake-draggable 0.6s ease-in-out infinite;
        }
        
        .custom-pin-tooltip {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            z-index: 1000 !important;
        }
        
        .custom-pin-tooltip .leaflet-tooltip-content {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .custom-pin-tooltip::before {
            border-top-color: white !important;
            border-top-width: 8px !important;
            border-left-width: 8px !important;
            border-right-width: 8px !important;
            margin-left: -8px !important;
        }
        
        .custom-pin-tooltip img {
            transition: opacity 0.2s ease-in-out;
        }
        
        .custom-pin-tooltip img[src=""] {
            display: none;
        }
        
        .custom-pin-tooltip {
            animation: tooltipFadeIn 0.15s ease-out;
        }
        
        @keyframes tooltipFadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .sorting-dropdown {
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            min-width: 140px;
        }
        
        .sorting-dropdown:hover {
            border-color: #3B82F6;
            background-color: #F8FAFC;
        }
        
        .sorting-dropdown:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            border-color: #3B82F6;
        }
        
        .sorting-container {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 1px solid #e2e8f0;
            backdrop-filter: blur(10px);
        }
        
        .posts-loading {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .loading-spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .post-item {
            animation: fadeInUp 0.4s ease-out;
            transform-origin: center bottom;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Staggered animation for multiple posts */
        .post-item:nth-child(1) { animation-delay: 0.0s; }
        .post-item:nth-child(2) { animation-delay: 0.0s; }
        .post-item:nth-child(3) { animation-delay: 0.0s; }
        .post-item:nth-child(4) { animation-delay: 0.0s; }
        .post-item:nth-child(5) { animation-delay: 0.0s; }
        
        /* Toggle button styles */
        .toggle-btn {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .toggle-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }
        
        .toggle-btn:active {
            transform: translateY(0);
        }
        
        /* Posts summary styles */
        .posts-summary {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            animation: slideInUp 0.3s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Enhanced post card styles */
        .post-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            background: #ffffff;
        }
        
        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: #d1d5db;
        }
        
        /* Like button animations */
        .like-btn {
            transition: all 0.2s ease;
        }
        
        .like-btn:hover {
            transform: scale(1.05);
        }
        
        .like-btn.liked {
            animation: heartBeat 0.6s ease-in-out;
        }
        
        @keyframes heartBeat {
            0% { transform: scale(1); }
            25% { transform: scale(1.2); }
            50% { transform: scale(1.1); }
            75% { transform: scale(1.25); }
            100% { transform: scale(1); }
        }
        
        /* Comments section styles */
        .comments-section {
            border-top: 1px solid #f3f4f6;
            background: #ffffff;
        }
        
        .comment-item {
            transition: background-color 0.2s ease;
        }
        
        .comment-item:hover {
            background-color: #f9fafb;
        }
        
        /* Error state styles */
        .error-state {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        
        /* Success state styles */
        .success-state {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }
        
        /* Responsive adjustments */
        @media (max-width: 640px) {
            .sorting-container {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .sorting-dropdown {
                width: 100%;
                min-width: auto;
            }
            
            .post-item {
                animation-delay: 0s;
            }
        }
        
        </style>

</head>

<body style="background-color: #999999;">
    
    @extends('layouts.app')
    
    @section('content')
    
    <!-- Map fullscren (below the navbar) -->
    <div class="absolute top-0 left-0 z-0 w-full h-screen">
        <div id="map" class="w-full h-full"></div>
    </div>
    
    <!-- Modal add pin -->
    <div id="createPinModal" class="fixed inset-0 z-[3000] hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-[400px]">
            <h2 class="mb-4 text-lg font-bold">Create New Pin</h2>
            
            <form id="createPinForm" method="POST" action="{{ route('pins.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="latitude" id="formLatitude">
                <input type="hidden" name="longitude" id="formLongitude">
                
                <div class="mb-2">
                    <label for="label" class="block mb-1 font-semibold">Label</label>
                    <input type="text" id="label" name="label" class="w-full p-2 border rounded" required>
                </div>
                
                <div class="mb-2">
                    <label for="body" class="block mb-1 font-semibold">Description (Body)</label>
                    <textarea id="body" name="body" rows="3" class="w-full p-2 border rounded"></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="banner" class="block mb-1 font-semibold">Banner Image</label>
                    <input type="file" id="banner" name="banner" accept="image/*" class="h-40 p-2 border rounded">
                </div>

                <div class="mb-4">
                    <label for="pin_icon" class="block mb-1 font-semibold">Custom Pin Icon (Optional)</label>
                    <input type="file" id="pin_icon" name="icon" accept="image/*" class="w-full p-2 border rounded">
                    
                    <!-- Optional preview -->
                    <img id="createIconPreview" class="mt-2 rounded" style="width: 48px; height: 48px; display: none;" />
                </div>
                
                
                <div class="flex justify-end space-x-4">
                    <button type="button" id="cancelCreatePin" class="text-gray-600">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-white bg-black rounded">Save Pin</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal edit pin -->
    <div id="editPinModal" class="fixed inset-0 z-[3000] hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-[400px]">
            <h2 class="mb-4 text-lg font-bold">Edit Pin</h2>
            
            <form id="editPinForm" method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT') {{-- Important: Use PUT method for updates --}}
                
                <div class="mb-2">
                    <label for="edit_label" class="block mb-1 font-semibold">Label</label>
                    <input type="text" id="edit_label" name="label" class="w-full p-2 border rounded" required>
                </div>
                
                <div class="mb-2">
                    <label for="edit_body" class="block mb-1 font-semibold">Description (Body)</label>
                    <textarea id="edit_body" name="body" rows="3" class="w-full p-2 border rounded"></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="edit_banner" class="block mb-1 font-semibold">New Banner Image (Optional)</label>
                    <p class="mb-1 text-xs text-gray-500">Uploading a new image will replace the old one.</p>
                    <input type="file" id="edit_banner" name="banner" accept="image/*" class="w-full p-2 border rounded">
                </div>
                
                <div class="mb-4">
                    <label for="edit_pin_icon" class="block mb-1 font-semibold">New Pin Icon (Optional)</label>
                    <p class="mb-1 text-xs text-gray-500">Uploading a new icon will replace the old one.</p>
                    <input type="file" id="edit_pin_icon" name="icon" accept="image/*" class="w-full p-2 border rounded">
                    
                    <!-- Optional preview -->
                    <img id="editIconPreview" class="mt-2 rounded" style="width: 48px; height: 48px; display: none;" />
                </div>
                
                
                <div class="flex justify-end space-x-4">
                    <button type="button" id="cancelEditPin" class="text-gray-600">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-white bg-black rounded">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal for custom icon-->
    <div id="iconModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="relative w-full max-w-md p-6 bg-black shadow-lg rounded-xl">
            <!-- Close -->
            <button onclick="closeIconModal()" class="absolute text-gray-500 top-2 right-2 hover:text-gray-800">&times;</button>
            
            <h2 class="mb-4 text-xl font-bold">Update Pin Icon</h2>
            
            <form id="pinIconForm" method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('POST')
                
                <!-- Default icon selection -->
                <label for="icon" class="block mb-1 font-medium">Choose a default icon:</label>
                <select name="icon" id="icon" class="w-full p-2 mb-4 border rounded">
                    <option value="">-- Select Default Icon --</option>
                    <option value="blue-pin.png">Blue</option>
                    <option value="red-pin.png">Red</option>
                    <option value="green-pin.png">Green</option>
                </select>
                
                <!-- Custom icon upload -->
                <label for="icon_upload" class="block mb-1 font-medium">Or upload your own icon:</label>
                <input type="file" name="icon_upload" id="icon_upload" accept="image/*" class="w-full p-2 mb-4 border rounded" />
                
                <button type="submit" class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">
                    Update Icon
                </button>
            </form>
        </div>
    </div>
    
    <!-- header sidebar  -->
    <div id="pinTab" class="fixed top-0 right-0 z-50 flex flex-col h-full bg-white shadow-lg w-80">
        <div class="flex items-center justify-between h-16 px-1 py-2 text-white bg-gray-800">
            <img id="pinIconPreview" src="" alt="Pin Icon" style="display:none; width: 40px; height: 40px;" />
            <span id="pinTitle" class="font-semibold">Pin Name</span> 
            <div class="flex gap-1 ml-2">
                <button id="togglePinDetails" class="relative flex items-center justify-center px-2 py-1 text-white transition-all duration-300 bg-gray-800 hover:bg-gray-700 hover:scale-125 rounded-3xl hover:rounded-xl ease-in-linear">
                    ⏏️</button>
                    <button id="closePinTab" class="relative flex items-center justify-center px-2 py-1 text-white transition-all duration-300 bg-gray-800 hover:bg-gray-700 hover:scale-125 rounded-3xl hover:rounded-xl ease-in-linear">
                        ❌</button>
                    </div>
                </div>
                
                <!-- yg bisa di minimize ⏏️ -->
                <div id="pinDetails" class="transition-all duration-200 ease-in-out">
                    <img id="pinBanner" src="" alt="Pin Banner" class="object-cover banner-sidebar min-h-40">
                    
                    <div class="p-4 pb-0 bg-white/25 backdrop-blur-sm">
                        <p id="pinBody">Details about the pin will go here.</p>
                    </div>
                    
                    
                    <div class="flex items-end text-xs text-white gap-x-4">
                        <span id="globalCoords">Y:# X:#</span>
                        <span> UID: <span id="pinOwnerId" class=""></span></span>
                    </div>
                    
                </div>

                {{-- <hr class="my-2"> --}}
                <div id="pinPosts" class="flex-1 px-4 pt-2 pb-8 space-y-2 overflow-y-auto text-sm text-gray-700">
                    {{-- <p class="italic text-gray-500">Loading posts...</p> pindah ke <script
                    
                    <hr class="my-2"> --}}
                    <div class="flex items-center space-x-2">
                        
                        <a id="add-post-button" href="#" class="flex flex-col items-center px-2 py-2 space-y-1 text-blue-600 transition-colors bg-white rounded hover:bg-gray-100">
                            <span>📝</span>
                            <span class="text-xs">Add Post</span>
                        </a>
                        
                        <button id="editPinBtn" class="flex flex-col items-center hidden px-2 py-2 space-y-1 text-blue-600 transition-colors bg-white rounded hover:bg-gray-100">
                            <span>✏️</span><br>
                            <span class="text-xs">Edit</span>
                        </button>
                        
                        <button id="deletePinBtn" class="flex flex-col items-center hidden px-2 py-2 space-y-1 text-red-500 transition-colors bg-white rounded hover:bg-gray-100">
                            <span>🗑️</span><br>
                            <span class="text-xs">Delete</span>
                        </button>
                        
                        <button id="approvePinBtn" class="flex flex-col items-center px-2 py-2 space-y-1 text-yellow-600 transition-colors bg-white rounded hover:bg-gray-100" style="display: none;">
                    <span>✔️</span><br>
                    <span class="text-xs">Approve</span>
                </button>
                
                <button id="revokePinBtn" class="flex flex-col items-center px-2 py-2 space-y-1 text-red-600 transition-colors bg-white rounded hover:bg-gray-100" style="display: none;">
                    <span>❌</span><br>
                    <span class="text-xs">Revoke</span>
                </button>
                
            </div>
        </div>
        
        
        
        <!-- Floating Button -->
        <div class="absolute top-20 left-[-4rem] flex flex-col items-center space-y-2">
            
            <div class="relative tooltip-group">
                <button id="zoomInBUTT" class="relative flex items-center justify-center w-12 h-12 text-gray-800 transition-all duration-300 shadow-lg bg-white/40 backdrop-blur-sm ring-1 ring-white/20 ring-inset hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl ease-in-linear">
                    ➕
                </button>
                <div class="absolute z-50 px-3 py-2 mr-3 text-sm font-medium text-white transform -translate-y-1/2 rounded-lg tooltip right-full top-1/2 bg-black/80 backdrop-blur-sm whitespace-nowrap drop-shadow-lg">
                    Zoom In
                </div>
            </div>
            
            <div class="relative tooltip-group">
                <button id="zoomOutBUTT" class="relative flex items-center justify-center w-12 h-12 text-gray-800 transition-all duration-300 shadow-lg bg-white/40 backdrop-blur-sm ring-1 ring-white/20 ring-inset hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl ease-in-linear">
                    ➖
                </button>
                <div class="absolute z-50 px-3 py-2 mr-3 text-sm font-medium text-white transform -translate-y-1/2 rounded-lg tooltip right-full top-1/2 bg-black/80 backdrop-blur-sm whitespace-nowrap drop-shadow-lg">
                    Zoom Out
                </div>
            </div>
            
            <div class="line-spacer"></div> <!-- Line spacer -->
            
            <div class="relative tooltip-group">
                <button id="dropdownButton" class="relative flex items-center justify-center w-12 h-12 text-gray-800 transition-all duration-300 shadow-lg bg-white/60 backdrop-blur-sm ring-1 ring-white/20 ring-inset hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl ease-in-linear">
                    <svg class="w-6 h-6" stroke="black" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <div class="absolute z-50 px-3 py-2 mr-3 text-sm font-medium text-white transform -translate-y-1/2 rounded-lg tooltip right-full top-1/2 bg-black/60 backdrop-blur-sm whitespace-nowrap drop-shadow-lg">
                    Menu
                </div>
            </div>
            
            <div id="dropdownMenu" class="z-50 flex flex-col items-center space-y-2">
                
                <div class="relative tooltip-group">
                    <button id="centerMapBtn" class="relative flex items-center justify-center w-12 h-12 text-gray-800 transition-all duration-300 shadow-lg bg-white/40 backdrop-blur-sm ring-1 ring-white/20 ring-inset hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl ease-in-linear">
                        🗺️
                    </button>
                    <div class="absolute z-50 px-3 py-2 mr-3 text-sm font-medium text-white transform -translate-y-1/2 rounded-lg tooltip right-full top-1/2 bg-black/60 backdrop-blur-sm whitespace-nowrap drop-shadow-lg">
                        Center Map
                    </div>
                </div>
                
                <div class="relative tooltip-group">
                    <a href="#" id="addPin" class="relative flex items-center justify-center w-12 h-12 text-gray-800 transition-all duration-300 shadow-lg bg-white/40 backdrop-blur-sm ring-1 ring-white/20 ring-inset hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl ease-in-linear">
                        📌
                    </a>
                    <div id="addPinLabel" class="absolute z-50 px-3 py-2 mr-3 text-sm font-medium text-white transform -translate-y-1/2 rounded-lg tooltip right-full top-1/2 bg-black/60 backdrop-blur-sm whitespace-nowrap drop-shadow-lg">
                        Add Pin
                    </div>
                </div>
                
                <!-- Line spacer -->
                <div class="line-spacer"></div>

                {{-- <div class="relative tooltip-group">
                    <button id="editPinBtn" class="relative flex items-center justify-center hidden w-12 h-12 text-gray-800 transition-all duration-300 bg-blue-500 shadow-lg backdrop-blur-sm ring-1 ring-white/20 ring-inset hover:bg-blue-700 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl ease-in-linear">
                        ✏️
                    </button>
                    <div class="absolute z-50 px-3 py-2 mr-3 text-sm font-medium text-white transform -translate-y-1/2 rounded-lg tooltip right-full top-1/2 bg-black/60 backdrop-blur-sm whitespace-nowrap drop-shadow-lg">
                        Edit Pin
                    </div>
                </div> --}}
                
                <div class="relative tooltip-group">
                    <button id="movePinBtn" class="relative flex items-center justify-center hidden w-12 h-12 text-gray-800 transition-all duration-300 shadow-lg bg-white/40 backdrop-blur-sm ring-1 ring-white/20 ring-inset hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl ease-in-linear">
                        🖐️
                    </button>
                    <div id="movePinLabel" class="absolute z-50 px-3 py-2 mr-3 text-sm font-medium text-white transform -translate-y-1/2 rounded-lg tooltip right-full top-1/2 bg-black/60 backdrop-blur-sm whitespace-nowrap drop-shadow-lg">
                        Move Pin
                    </div>
                </div>
                
                <div class="relative tooltip-group">
                    <button id="savePositionBtn" class="relative flex items-center justify-center hidden w-12 h-12 text-gray-800 transition-all duration-300 bg-green-600 shadow-lg backdrop-blur-sm ring-1 ring-white/20 ring-inset hover:bg-green-700 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl ease-in-linear">
                        💾
                    </button>
                    <div class="absolute z-50 px-3 py-2 mr-3 text-sm font-medium text-white transform -translate-y-1/2 rounded-lg tooltip right-full top-1/2 bg-black/60 backdrop-blur-sm whitespace-nowrap drop-shadow-lg">
                        Move Pin • Save
                    </div>
                </div>
                
                {{-- <div class="relative tooltip-group">
                    <button id="add-post-button" class="relative flex items-center justify-center w-12 h-12 text-gray-800 transition-all duration-300 bg-blue-500 shadow-lg backdrop-blur-sm ring-1 ring-white/20 ring-inset hover:bg-blue-700 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl ease-in-linear">
                        📝
                    </button>
                    <div class="absolute z-50 px-3 py-2 mr-3 text-sm font-medium text-white transform -translate-y-1/2 rounded-lg tooltip right-full top-1/2 bg-black/60 backdrop-blur-sm whitespace-nowrap drop-shadow-lg">
                        Add Post
                    </div>
                </div>
                
                <div class="relative tooltip-group">
                    <button id="deletePinBtn" class="relative flex items-center justify-center hidden w-12 h-12 text-gray-800 transition-all duration-300 bg-red-500 shadow-lg backdrop-blur-sm ring-1 ring-white/20 ring-inset hover:bg-red-700 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl ease-in-linear">
                        🗑️
                    </button>
                    <div class="absolute z-50 px-3 py-2 mr-3 text-sm font-medium text-white transform -translate-y-1/2 rounded-lg tooltip right-full top-1/2 bg-black/60 backdrop-blur-sm whitespace-nowrap drop-shadow-lg">
                        Delete Pin
                    </div>
                </div> --}}
                
                {{-- <div class="line-spacer"></div>  --}}
                <div class="relative tooltip-group">
                    <button id="lastPinBtn" class="relative flex items-center justify-center w-12 h-12 text-gray-800 transition-all duration-300 shadow-lg bg-white/40 backdrop-blur-sm ring-1 ring-white/20 ring-inset hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl ease-in-linear">
                        ↪️
                    </button>
                    <div class="absolute z-50 px-3 py-2 mr-3 text-sm font-medium text-white transform -translate-y-1/2 rounded-lg tooltip right-full top-1/2 bg-black/60 backdrop-blur-sm whitespace-nowrap drop-shadow-lg">
                        Last Pin
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
    
    
    <div id="toast" class="absolute bottom-6 left-6 z-[9999] opacity-0 transition-opacity duration-300 pointer-events-auto
    bg-gray-800 text-white px-4 py-2 rounded shadow-lg">
    <span id="toastMessage">This is a toast</span>
</div>


    <!-- Menu ▼ | Controls | ... | -->
    <div class="absolute z-50 flex items-center space-x-4 -translate-x-1/2 top-10 left-1/2">
        <div id="addingPinWrapper" class="flex items-center hidden px-4 py-2 bg-white rounded-md shadow-md ">
            🧷 Click on the map to place a pin...
            {{-- <button id="cancelPin" class="ml-2 text-red-500 hover:underline">Cancel</button> --}}
        </div>
        
    </div> 
    
    
    
    <!-- CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
</body>

<script>
    
    // Toast Message success, error, & status 
    @if (session('success'))
    document.addEventListener('DOMContentLoaded', function () {
        showToast('{{ session('success') }}', 3000); // success messages for 3 seconds
        });
        @endif

        @if (session('error'))
        document.addEventListener('DOMContentLoaded', function () {
            showToast('{{ session('error') }}', 6000); // errors for longer
        });
        @endif
        
        @if (session('status'))
        document.addEventListener('DOMContentLoaded', function () {
            showToast('{{ session('status') }}', 3000); // status messages for 3 seconds
        });
    @endif 
    
    let isAddPinModeActive = false;
    let currentActiveMarker = null;
    let currentActivePin = null;
    let zoomMarkerPivot = null; 
    let isDraggingMode = false;
    let originalPosition = null;
    let ghostMarker = null; // ghost marker (add pin)
    
    const markers = [];
    const markersById = {};
    window.requestedPinId = {{ request('pin_id') ?? 'null' }};
    
    lastPinBtn.style.display = 'none';
    
    // listener button zoom out
    document.getElementById('zoomInBUTT').addEventListener('click', function () {
        map.zoomIn();
        
        if (zoomMarkerPivot) {
            setTimeout(() => {
                centerMapWithSidebarOffset(zoomMarkerPivot.getLatLng());
            }, 250);
        }        
    });
    
    //listener button zoom in
    document.getElementById('zoomOutBUTT').addEventListener('click', function () {
        map.zoomOut();
        
        if (zoomMarkerPivot) {
            setTimeout(() => {
                centerMapWithSidebarOffset(zoomMarkerPivot.getLatLng());
            }, 250);
        }
    });
    
    // listener button menu dropdown
    const dropdownButton = document.getElementById("dropdownButton");
    const dropdownMenu = document.getElementById("dropdownMenu");
    dropdownButton.addEventListener("click", function (e) {
        e.stopPropagation();
        
        const isOpen = dropdownMenu.style.display === "block";
        
        dropdownMenu.style.display = isOpen ? "none" : "block";
        
        if (!isOpen) {
            dropdownButton.classList.remove("rounded-3xl");
            dropdownButton.classList.add("rounded-xl", "bg-white/80", "hover-bg-white/60");//add a bit more opacity when open
        } else {
            dropdownButton.classList.remove("rounded-xl", "bg-white/80", "hover-bg-white/60");
            dropdownButton.classList.add("rounded-3xl");
        }
    });
    
    // Open dropdown by default
    dropdownMenu.style.display = "block";
    dropdownButton.classList.remove("rounded-3xl");
    dropdownButton.classList.add("rounded-xl", "bg-white/80");
    //
    
    // listener center map 
    document.getElementById("centerMapBtn").addEventListener("click", () => {
        map.fitBounds(imageBounds);
        setTimeout(() => {
            map.zoomIn(+2);
            }, 300);

    });
    
    //listener button lastpin 
    document.getElementById('lastPinBtn').addEventListener('click', reopenLastPin);
    
    // listener pintab (sidebar) 🔽
    document.getElementById('togglePinDetails').addEventListener('click', function () {
        const details = document.getElementById('pinDetails');
        const isHidden = details.classList.contains('hidden');
        
        details.classList.toggle('hidden');
        this.textContent = isHidden ? '⏏️' : '⏹️';
    }); 

    //listener close pintab (sidebar) 🔽
    document.getElementById("closePinTab").addEventListener("click", closePinTab);

    // listener posts body fold
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('toggle-post-body')) {
            const wrapper = e.target.closest('p');
            const span = wrapper?.querySelector('.post-body');
            if (!span) return;
            
            const showingFull = span.textContent === span.dataset.full;
            span.textContent = showingFull ? span.dataset.short : span.dataset.full;
            e.target.textContent = showingFull ? 'Show more' : 'Show less';
        }
    });
    
    //listener post moderation toggle (ajax ✅)[toast ✅]
        document.addEventListener('click', async function (e) {
            if (e.target.classList.contains('toggle-moderation-btn')) {
                const button = e.target;
                const postId = button.dataset.postId;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Find the parent post card element
                const postCard = button.closest('.post-card');

                try {
                    const response = await fetch(`/posts/${postId}/toggle-moderation`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to toggle moderation');
                    }

                    const result = await response.json();
                    showToast(result.message || 'Moderation updated.', 3000);

                    // Update the button's state and appearance
                    button.dataset.moderated = result.moderated;

                    if (result.moderated) {
                        // --- POST IS NOW APPROVED ---
                        
                        // 1. Update Post Card to neutral color
                        if (postCard) {
                            postCard.classList.remove('bg-yellow-50', 'border-yellow-200');
                            postCard.classList.add('bg-white', 'border-gray-200');
                        }

                        // 2. Update Button to "Revoke" style (more distinct)
                        button.textContent = '⚠️ Revoke Approval';
                        button.className = 'toggle-moderation-btn ml-2 px-2 py-1 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 rounded transition-colors';

                    } else {
                        // --- POST IS NOW PENDING ---

                        // 1. Update Post Card to pending color
                        if (postCard) {
                            postCard.classList.remove('bg-white', 'border-gray-200');
                            postCard.classList.add('bg-yellow-50', 'border-yellow-200');
                        }

                        // 2. Update Button to "Approve" style
                        button.textContent = '✔️ Approve Post';
                        button.className = 'toggle-moderation-btn ml-2 px-2 py-1 text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 rounded transition-colors';
                    }

                } catch (err) {
                    console.error(err);
                    showToast('Error toggling moderation.', 6000);
                }
            }
        });
            document.addEventListener('DOMContentLoaded', function() {
                
                // Get the form element once, globally (or within DOMContentLoaded)
                const createPinForm = document.getElementById("createPinForm");
                const createPinModal = document.getElementById("createPinModal");
                
                
                // 1. Listener for the "Add Pin" button to toggle the mode
                // This listener correctly initiates the add pin mode.
                document.getElementById("addPin").addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleAddPinMode();
                });
                
                
                // 2. Listener for the "Cancel" button in the CREATE modal
                // This listener should only hide the modal and optionally reset the form.
                document.getElementById("cancelCreatePin").addEventListener("click", () => {
                    createPinModal.classList.add("hidden"); // Hide the modal
                    createPinForm.reset(); // Reset form fields
                    document.getElementById("createIconPreview").style.display = 'none'; // Clear icon preview
    });
    
    
    if (createPinForm) { // Ensure the form element exists before trying to add listener
        createPinForm.addEventListener("submit", async function(e) {
            e.preventDefault(); // <<< THIS CRITICAL LINE WILL NOW EXECUTE WHEN THE FORM IS SUBMITTED
            
            const formData = new FormData(this);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showNotification(data.message || 'Pin created successfully!', 'success');
                    createPinModal.classList.add("hidden"); // Hide the modal
                    
                    const newPin = data.pin;
                    
                    // Create and add the new marker to the map
                    const initialOpacity = newPin.moderated ? 1.0 : 0.7;
                    const markerOptions = {
                        opacity: initialOpacity,
                        icon: L.divIcon({
                            className: `custom-icon`,
                            html: `<img src="${newPin.icon_url || '/storage/icons/default.png'}"
                                        class="transition-transform duration-200 ease-in-out pin-image hover:scale-110"
                                        style="width: 40px; height: 40px; filter: ${newPin.moderated ? '' : 'grayscale(100%)'};">`,
                                        iconSize: [40, 40],
                                        iconAnchor: [20, 40],
                                        popupAnchor: [0, -35],
                                    })
                                };
                                
                                const newMarker = L.marker([newPin.latitude, newPin.longitude], markerOptions).addTo(map);
                                
                                newMarker.markerElement = newMarker.getElement();
                                newMarker.pinImage = newMarker.markerElement.querySelector('.pin-image');
                                
                                markersById[newPin.id] = newMarker;
                                
                                newMarker.on("click", () => {
                                    if (isDraggingModeActive) {
                                        togglePinDragMode();
                        }
                        if (currentActiveMarker && currentActiveMarker !== newMarker) {
                            const prevMarkerElement = currentActiveMarker.getElement();
                            const prevPinImage = prevMarkerElement.querySelector('.pin-image');
                            prevPinImage.classList.remove('scale-125');
                            currentActiveMarker.isSelected = false;
                            currentActiveMarker.dragging.disable();
                        }
                        newMarker.isSelected = true;
                        newMarker.pinImage.classList.add('scale-125');
                        
                        currentActiveMarker = newMarker;
                        currentActivePin = newPin;
                        zoomMarkerPivot = newMarker;
                        resetModerationButtons();
                        // Assuming updateLastPinButton and centerMapWithSidebarOffset exist
                        updateLastPinButton(newPin);
                        showPinTab(newPin.label || 'Pin', newPin.latitude, newPin.longitude, newPin);
                        centerMapWithSidebarOffset(newMarker.getLatLng());
                    });

                    this.reset(); // Clear form fields
                    document.getElementById("createIconPreview").style.display = 'none'; // Clear icon preview

                } else {
                    const errorMessage = data.message || 'Failed to create pin.';
                    showNotification(errorMessage, 'error');

                    if (data.errors) {
                        console.error('Validation errors:', data.errors);
                        showNotification('Please check the form for errors.', 'alert');
                    }
                }
            } catch (error) {
                console.error('Network or parsing error:', error);
                showNotification('An unexpected error occurred. Please try again.', 'error');
            }
        });
    }
});
// listener button move pin  (ajax ✅)
document.getElementById("movePinBtn")?.addEventListener('click', togglePinDragMode);
document.getElementById("savePositionBtn")?.addEventListener('click', savePinPosition);

document.addEventListener('DOMContentLoaded', function() {

    // Listener for button edit pin (populate modal)
    // This listener remains the same, it just populates the form and shows the modal.
    document.getElementById("editPinBtn").addEventListener("click", () => {
        if (!currentPinId) {
            showNotification("No pin selected. Please click on a pin first to edit.", 'alert');
            return;
        }

        // Populate the edit form with the current pin's data
        document.getElementById("edit_label").value = document.getElementById("pinTitle").textContent;
        document.getElementById("edit_body").value = document.getElementById("pinBody").textContent;
        
        // Set up the preview for new icon upload
        const editPinIconInput = document.getElementById("edit_pin_icon");
        const editIconPreview = document.getElementById("editIconPreview");
        
        // Remove any existing listener to prevent duplicates
        editPinIconInput.removeEventListener("change", handleEditIconPreviewChange);
        // Add the listener for icon preview
        editPinIconInput.addEventListener("change", handleEditIconPreviewChange);
        
        // Set the form's action to the correct update URL
        const editForm = document.getElementById("editPinForm");
        editForm.action = `/pins/${currentPinId}`; // The form's @method('PUT') will handle the rest
        
        // Show the edit modal
        document.getElementById("editPinModal").classList.remove("hidden");
    });
    
    // Helper function for edit icon preview (defined outside the click listener)
    function handleEditIconPreviewChange(e) {
        const file = e.target.files[0];
        const preview = document.getElementById("editIconPreview");
        if (file) {
            const reader = new FileReader();
            reader.onload = function (evt) {
                preview.src = evt.target.result;
                preview.style.display = "inline-block";
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = "none";
        }
    }
    
    // Listener close edit pin modal
    document.getElementById("cancelEditPin").addEventListener("click", () => {
        document.getElementById("editPinModal").classList.add("hidden");
        // Optionally reset the form fields here if you want
        document.getElementById("editPinForm").reset();
        document.getElementById("editIconPreview").style.display = 'none'; // Clear preview
    });
    

    // Find the edit form element once
    const editPinForm = document.getElementById("editPinForm");
    const editPinModal = document.getElementById("editPinModal"); // The modal element
    
    // Listener for edit pin form submission (AJAX) - THIS BLOCK IS NOW UN-NESTED AND CORRECTLY PLACED
    if (editPinForm) { // Ensure the form exists
        editPinForm.addEventListener("submit", async function(e) {
            e.preventDefault(); // Prevent default form submission
            
            const formData = new FormData(this); // FIXED: Removed 'new' duplicate
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Note: Laravel expects _method=PUT or _method=PATCH for PUT/PATCH requests with FormData
            // If your form already has @method('PUT'), FormData will automatically include a hidden field.
            // The fetch method should remain 'POST' when using FormData for PUT/PATCH.
            
            try {
                const response = await fetch(this.action, { // Use this.action (e.g., /pins/123)
                    method: 'POST', // Always POST with FormData for PUT/PATCH in Laravel
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showNotification(data.message || 'Pin updated successfully!', 'success');
                    editPinModal.classList.add("hidden"); // Hide the modal
                    
                    // Update the corresponding marker on the map and its details
                    const updatedPin = data.pin; // Assuming Laravel returns the updated pin object
                    const markerToUpdate = markersById[updatedPin.id];
                    
                    if (markerToUpdate) {
                        // Update the marker's position if it changed
                        markerToUpdate.setLatLng([updatedPin.latitude, updatedPin.longitude]);
                        
                        // Update the marker's icon if the icon changed or moderated status changed
                        const newIconUrl = updatedPin.icon_url || '/storage/icons/default.png';
                        // Directly set filter style based on moderation status
                        const newGrayscaleFilter = updatedPin.moderated ? '' : 'grayscale(100%)';
                        
                        if (markerToUpdate.pinImage) { // Directly update the img element
                            markerToUpdate.pinImage.src = newIconUrl;
                            markerToUpdate.pinImage.style.filter = newGrayscaleFilter; // Apply grayscale directly
                        } else {
                            // Recreate the icon if pinImage reference isn't available (less ideal)
                            // This part ensures a new icon is created if the original one wasn't properly referenced.
                            markerToUpdate.setIcon(L.divIcon({
                                className: `custom-icon`,
                                html: `<img src="${newIconUrl}"
                                class="transition-transform duration-200 ease-in-out pin-image hover:scale-110"
                                style="width: 40px; height: 40px; filter: ${newGrayscaleFilter};">`,
                                iconSize: [40, 40],
                                iconAnchor: [20, 40],
                                popupAnchor: [0, -35],
                            }));
                            // Re-store reference after recreating icon
                            markerToUpdate.pinImage = markerToUpdate.getElement().querySelector('.pin-image');
                        }
                        
                        // Update marker opacity
                        markerToUpdate.setOpacity(updatedPin.moderated ? 1.0 : 0.7);
                        
                        // Update sidebar content if the edited pin is currently displayed
                        if (currentActivePin && currentActivePin.id === updatedPin.id) {
                            currentActivePin = updatedPin; // Update the reference for the active pin
                            document.getElementById("pinTitle").textContent = updatedPin.label || 'Unnamed Pin';
                            document.getElementById("pinBody").textContent = updatedPin.body || 'No description provided.';
                            if (updatedPin.banner_url) { // Use banner_url from backend
                                document.getElementById("pinBanner").src = updatedPin.banner_url;
                                document.getElementById("pinBanner").style.display = 'block';
                            } else {
                                document.getElementById("pinBanner").style.display = 'none';
                            }
                            if (updatedPin.icon_url) { // Use icon_url from backend
                                document.getElementById("pinIconPreview").src = updatedPin.icon_url;
                                document.getElementById("pinIconPreview").style.display = 'inline-block';
                            } else {
                                // Show default if no custom icon (ensure it matches frontend path)
                                document.getElementById("pinIconPreview").src = '/storage/icons/default.png';
                                document.getElementById("pinIconPreview").style.display = 'inline-block';
                            }
                            // Refresh the posts for this pin, as its moderation status might have changed
                            loadPostsForPin(updatedPin.latitude, updatedPin.longitude, updatedPin.id);
                        }
                    }
                    this.reset(); // Clear form fields
                    document.getElementById("editIconPreview").style.display = 'none'; // Clear icon preview
                    
                } else {
                    // Handle validation errors or other server-side errors
                    const errorMessage = data.message || 'Failed to update pin.';
                    showNotification(errorMessage, 'error');
                    
                    if (data.errors) {
                        console.error('Validation errors:', data.errors);
                        // You might want to loop through data.errors and display specific messages
                        showNotification('Please check the form for errors.', 'alert');
                    }
                }
            } catch (error) {
                console.error('Network or parsing error:', error);
                showNotification('An unexpected error occurred. Please try again.', 'error');
            } finally {
                // Optional: hideLoadingSpinner();
            }
        });
    }
});

// Listener for delete pin (AJAX ✅)
document.getElementById("deletePinBtn").addEventListener("click", async () => { // Made async for await
    if (!currentPinId) {
        showNotification("No pin selected. Please select a pin first to delete.", 'alert');
        return;
    }

    if (!confirm("Are you sure you want to delete this pin? This action cannot be undone.")) {
        return;
    }
    
try {
    const response = await fetch(`/pins/${currentPinId}`, {
        method: "DELETE",
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json' // Request JSON response
        }
    });
    
    const data = await response.json(); // Always attempt to parse JSON for errors
    
    if (response.ok) { // HTTP status 200-299 indicates success
        showNotification(data.message || "Pin deleted successfully!", 'success');
        closePinTab(); // Close the sidebar
        
        // Dynamically remove the pin marker from the map
        const markerToDelete = markersById[currentPinId];
        if (markerToDelete) {
            markerToDelete.remove(); // Remove the Leaflet marker from the map
            delete markersById[currentPinId]; // Remove from your JS lookup object
        }
        
        // Reset current active pin states
        currentActivePin = null;
        currentActiveMarker = null;
        
    } else {
        // Handle server-side errors (e.g., 403 Forbidden, 404 Not Found, 500 Internal Error)
        const errorMessage = data.message || `Failed to delete pin. Status: ${response.status}`;
        showNotification(errorMessage, 'error');
        console.error('Error deleting pin:', data);
    }
} catch (error) {
    console.error('Network or parsing error:', error);
    showNotification('An unexpected error occurred while deleting the pin. Please try again.', 'error');
}
});
    
    // Listener for pin approve (ajax ✅)[toast ✅] [visual bug ✅]
    document.addEventListener('click', function(e) {
        if (e.target.closest('#approvePinBtn')) {
            e.preventDefault();
            
            const button = e.target.closest('#approvePinBtn');
            const pinId = button.getAttribute('data-pin-id');
            
            if (!pinId) {
                console.error('Pin ID not found for approval');
                showNotification('Error: Pin ID not found for approval.', 'error');
                return;
            }

            button.disabled = true;
            button.style.opacity = '0.6';
            
            fetch(`/pins/${pinId}/approve`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (currentActivePin && currentActivePin.id == pinId) {
                        currentActivePin.moderated = true;
                    }
                    
                    const targetMarker = markersById[pinId];
                    if (targetMarker) {
                        targetMarker.setOpacity(1.0); // Set to full opacity
                        
                        const markerElement = targetMarker.getElement();
                        if (markerElement) {
                            markerElement.classList.remove('grayscale-icon');
                        }
                        
                        if (targetMarker.pinImage) {
                            targetMarker.pinImage.style.filter = '';
                        }
                    }
                    
                    button.style.display = 'none';
                    const revokePinBtn = document.getElementById('revokePinBtn');
                    if (revokePinBtn) {
                        revokePinBtn.style.display = 'inline-block';
                        revokePinBtn.setAttribute('data-pin-id', pinId);
                        revokePinBtn.disabled = false;
                        revokePinBtn.style.opacity = '1';
                        revokePinBtn.classList.remove('text-green-600');
                        revokePinBtn.classList.add('text-red-600', 'hover:bg-gray-100');
                    }
                    
                    showNotification('Pin approved successfully!', 'success');
                } else {
                    throw new Error(data.message || 'Failed to approve pin.');
                }
            })
            .catch(error => {
                console.error('Error approving pin:', error);
                button.disabled = false;
                button.style.opacity = '1';
                showNotification(`Failed to approve pin: ${error.message}`, 'error');
            });
        }
    });
    
    // Listener for revoke pin button (ajax ✅)[toast ✅] [visual bug ✅]
    document.addEventListener('click', function(e) {
        if (e.target.closest('#revokePinBtn')) {
            e.preventDefault();
            
            const button = e.target.closest('#revokePinBtn');
            const pinId = button.getAttribute('data-pin-id');
            
            if (!pinId) {
                console.error('Pin ID not found for revocation');
                showNotification('Error: Pin ID not found for revocation.', 'error');
                return;
            }
            
            if (!confirm('Are you sure you want to revoke approval for this pin? It will become hidden from public view.')) {
                button.disabled = false;
                button.style.opacity = '1';
                return;
            }
            
            button.disabled = true;
            button.style.opacity = '0.6';
            
            fetch(`/pins/${pinId}/revoke`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (currentActivePin && currentActivePin.id == pinId) {
                        currentActivePin.moderated = false;
                    }
                    
                    const targetMarker = markersById[pinId];
                    if (targetMarker) {
                        targetMarker.setOpacity(0.7); // Set to lower opacity
                        
                        // Directly add grayscale style
                        if (targetMarker.pinImage) { // Check if pinImage reference exists
                            targetMarker.pinImage.style.filter = 'grayscale(100%)'; // ADD GRAYSCALE style
                        }
                    }
                    
                    button.style.display = 'none';
                    const approvePinBtn = document.getElementById('approvePinBtn');
                    if (approvePinBtn) {
                        approvePinBtn.style.display = 'inline-block';
                        approvePinBtn.setAttribute('data-pin-id', pinId);
                        approvePinBtn.innerHTML = '<span>✔️</span><br><span class="text-xs">Approve</span>';
                        approvePinBtn.classList.remove('text-red-600');
                        approvePinBtn.classList.add('text-yellow-600', 'hover:bg-gray-100');
                        approvePinBtn.disabled = false;
                        approvePinBtn.style.opacity = '1';
                    }
                    
                    showNotification('Pin approval revoked successfully!', 'success');
                } else {
                    throw new Error(data.message || 'Failed to revoke pin approval.');
                }
            })
            .catch(error => {
                console.error('Error revoking pin:', error);
                button.disabled = false;
                button.style.opacity = '1';
                showNotification(`Failed to revoke pin approval: ${error.message}`, 'error');
            });
        }
    });

    
    //listener delet post button (ajax ✅)[toast ✅]
    document.addEventListener('click', async function (e) {
        const deleteBtn = e.target.closest('.delete-post-btn');
        if (!deleteBtn) return;
        
        const postId = deleteBtn.dataset.postId;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (!confirm('Are you sure you want to delete this post?')) return;
        
        try {
            const response = await fetch(`/posts/${postId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) throw new Error('Failed to delete post.');
            
            const postElement = document.querySelector(`.post-card[data-post-id="${postId}"]`);
            if (postElement) postElement.remove();
            
            showToast('Post deleted successfully.');
        } catch (err) {
            console.error(err);
            showToast('Failed to delete post.', 5000);
        }
    });
    
    //listener like toggle (ajax ✅)[toast ✅]  
    document.addEventListener('click', async function (e) {
        const likeBtn = e.target.closest('.like-btn');
        if (!likeBtn || !likeBtn.dataset.postId) return;
        
        const postId = likeBtn.dataset.postId;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        
        try {
            const res = await fetch(`/posts/${postId}/like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
            });
            
            if (!res.ok) throw new Error('Request failed');
            
            const data = await res.json();
            
            if (!data.success) {
                showToast(data.message || 'Failed to like post.', 'error');
                return;
            }
            
            // Toggle like state
            likeBtn.classList.toggle('liked', data.is_liked);
            likeBtn.classList.toggle('text-red-500', data.is_liked);
            likeBtn.classList.toggle('text-gray-500', !data.is_liked);
            
            // Update icon
            const heartIcon = likeBtn.querySelector('.heart-icon');
            if (heartIcon) heartIcon.textContent = data.is_liked ? '❤️' : '🤍';
            
            // Update count
            const parentDiv = likeBtn.parentElement;
            const likesCount = parentDiv.querySelector('.likes-count');
            const likesText = parentDiv.querySelector('.likes-text');

            if (likesCount) likesCount.textContent = data.likes_count;
            if (likesText) likesText.textContent = data.likes_count === 1 ? 'like' : 'likes';
            
            // 🎉 Toast success
            showToast(data.message, 'success');
            
        } catch (err) {
            console.error('Like toggle failed:', err);
            showToast('Something went wrong. Please try again.', 'error');
        }
    });

    // Delegated listener for comment deletion (AJAX ✅)
    document.addEventListener('submit', async function (e) {
        const commentForm = e.target.closest('.comment-form');
        const deleteForm = e.target.closest('form[action*="/comments/"][method="POST"]');
        
        // --- Handle Comment SUBMISSION ---
        if (commentForm) {
            e.preventDefault(); // Prevent default form submission
            
            const postId = commentForm.dataset.postId;
            const bodyInput = commentForm.querySelector('textarea[name="body"]');
            const body = bodyInput.value.trim();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (!body) {
                showNotification('Comment cannot be empty.', 'alert');
                return;
            }
            
            try {
                const response = await fetch(commentForm.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ body: body })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showNotification(data.message || 'Comment added successfully!', 'success');
                    
                    const commentsList = document.getElementById(`comments-list-${postId}`);
                    if (commentsList) {
                        // Assuming your backend returns the new comment object at data.comment
                        const newCommentHtml = renderComment(data.comment, postId);
                        commentsList.insertAdjacentHTML('beforeend', newCommentHtml);
                        bodyInput.value = ''; // Clear the textarea
                        
                        // Update comment count
                        const commentsCountSpan = commentsList.closest('.comments-section')?.querySelector('.text-xs.font-medium.text-gray-500');
                        if (commentsCountSpan) {
                            const currentCountMatch = commentsCountSpan.textContent.match(/\d+/);
                            let currentCount = currentCountMatch ? parseInt(currentCountMatch[0], 10) : 0;
                            currentCount++;
                            commentsCountSpan.textContent = `${currentCount} ${currentCount === 1 ? 'Comment' : 'Comments'}`;
                        }
                    }
                } else {
                    const errorMessage = data.message || 'Failed to add comment.';
                    showNotification(errorMessage, 'error');
                }
            } catch (error) {
                console.error('Network or parsing error:', error);
                showNotification('An unexpected error occurred. Please try again.', 'error');
            }
        }
        
        // --- Handle Comment DELETION ---
        if (deleteForm && deleteForm.querySelector('input[name="_method"][value="DELETE"]')) {
            e.preventDefault(); // Prevent default form submission
            
            if (!confirm('Are you sure you want to delete this comment?')) {
                return;
            }
            
            const csrfToken = deleteForm.querySelector('input[name="_token"]').value;
            
            try {
                const response = await fetch(deleteForm.action, {
                    method: 'POST', // Still POST because of FormData + _method spoofing
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: new FormData(deleteForm) // FormData handles the _method field
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showNotification(data.message || 'Comment deleted successfully!', 'success');

                    const commentItem = deleteForm.closest('li');
                    if (commentItem) {
                        // Update comment count before removing the item
                        const commentsCountSpan = commentItem.closest('.comments-section')?.querySelector('.text-xs.font-medium.text-gray-500');
                        if (commentsCountSpan) {
                            const currentCountMatch = commentsCountSpan.textContent.match(/\d+/);
                            let currentCount = currentCountMatch ? parseInt(currentCountMatch[0], 10) : 0;
                            if(currentCount > 0) currentCount--;
                            commentsCountSpan.textContent = `${currentCount} ${currentCount === 1 ? 'Comment' : 'Comments'}`;
                        }
                        commentItem.remove();
                    }
                } else {
                    const errorMessage = data.message || 'Failed to delete comment.';
                    showNotification(errorMessage, 'error');
                }
            } catch (error) {
                console.error('Network or parsing error:', error);
                showNotification('An unexpected error occurred while deleting the comment.', 'error');
            }
        }
    });
    
    
    
    
    // map setup
    window.authUserId = {{ auth()->id() ?? 'null' }};
    window.authUserRole = "{{ auth()->user()->role ?? 'guest' }}";
    
    const currentUserId = @json(auth()->id());
    const imgWidth = 1000, imgHeight = 1000;
    const imageBounds = [[0, 0], [imgHeight, imgWidth]];
    
    const map = L.map('map', {
        zoomControl: false,  // removes the default +/- buttons
        crs: L.CRS.Simple,
        minZoom: -10,
        maxZoom: 10,
        maxBounds: null,
        maxBoundsViscosity: 1.0
    });
    
    L.imageOverlay('/img/map_skb.png', imageBounds).addTo(map);
    map.fitBounds(imageBounds);
    
    const initialZoom = map.getZoom(); 
    map.setMinZoom(initialZoom);
    
    map.zoomIn(+2);
    
    let currentPinId = null;
    
    // pins setup
    fetch('/pins')
    .then(res => res.json())
    .then(pins => {
        pins.forEach(pin => {
            // Determine initial opacity and grayscale class based on pin.moderated
            const initialOpacity = pin.moderated ? 1.0 : 0.5;
            const initialGrayscaleClass = pin.moderated ? '' : 'grayscale-icon';

            const markerOptions = {
                opacity: initialOpacity, // Leaflet marker opacity
                icon: L.divIcon({
                    className: `custom-icon ${initialGrayscaleClass}`, // Add grayscale class here
                    html: `<img src="${pin.iconUrl || '/storage/icons/default.png'}"
                    class="transition-transform duration-200 ease-in-out pin-image hover:scale-110"
                    style="width: 40px; height: 40px;">`,
                    iconSize: [40, 40], // Using constants for this is a good idea
                    iconAnchor: [20, 40],
                    popupAnchor: [0, -35],
                })
            };
            
            // Create enhanced tooltip content with banner
            const createTooltipContent = (pin) => {
                const bannerHtml = pin.banner_url ? 
                `<div class="mb-2">
                    <img src="${pin.banner_url}" 
                    alt="${pin.label || 'Pin banner'}"
                    class="object-cover w-full h-16 rounded-md"
                            style="max-width: 160px; aspect-ratio: 4/3;"
                            onerror="this.style.display='none'">
                            </div>` : '';
                            
                            return `
                            <div class="p-3 bg-white rounded-lg shadow-lg min-w-40 max-w-48">
                                ${bannerHtml}
                                <div class="mb-1 text-sm font-semibold text-gray-800">
                                    ${pin.label || 'Untitled Pin'}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ${pin.description ? pin.description.substring(0, 23) + (pin.description.length > 23 ? '...' : '') : 'No description'}
                                        </div>
                        ${pin.moderated ? '' : '<div class="mt-1 text-xs font-medium text-orange-500">⚠️ Pending moderation</div>'}
                        </div>
                        `;
                    };

                    // populate the marker with the pin data
                    const marker = L.marker([pin.latitude, pin.longitude], markerOptions).addTo(map)
                    .bindTooltip(createTooltipContent(pin), { 
                        permanent: false, 
                        opacity: 1,
                        direction: 'top',
                        offset: [0, -60],
                        className: 'custom-pin-tooltip',
                        interactive: false 
                    });

                    const markerElement = marker.getElement();
                    const pinImage = markerElement.querySelector('.pin-image');
                    
            // hover effects
            markerElement.addEventListener('mouseenter', () => {
                pinImage.classList.add('scale-110');
            });
            
            markerElement.addEventListener('mouseleave', () => {
                // remove hover scale if not selected
                if (!marker.isSelected) {
                    pinImage.classList.remove('scale-110');
                }
            });
            
            // open sidebar when pin is clicked
            marker.on("click", () => {
                if (isMoveModeActive && currentActiveMarker && currentActiveMarker !== marker) {
                togglePinDragMode();
                // isMoveModeActive will be false.
            }
            if (currentActiveMarker && currentActiveMarker !== marker) {
                if (currentActiveMarker.pinImage) {
                    currentActiveMarker.pinImage.classList.remove('scale-125', 'pin-draggable');
                    
                    const previousPinData = currentActivePin; 
                    
                    if (previousPinData && !previousPinData.moderated) {
                        currentActiveMarker.pinImage.style.filter = 'grayscale(100%)';
                    } else {
                        currentActiveMarker.pinImage.style.filter = '';
                    }
                }
                currentActiveMarker.isSelected = false; 
                currentActiveMarker.dragging.disable(); 
            } else if (currentActiveMarker === marker) {
            }
            
            marker.isSelected = true; 
            const pinImage = marker.pinImage; 
            if (pinImage) {
                pinImage.classList.add('scale-125'); 
            }
            currentActiveMarker = marker; 
            currentActivePin = pin;      
            zoomMarkerPivot = marker;   
            resetModerationButtons(); 
            showPinTab(pin.label || 'Pin', pin.latitude, pin.longitude, pin);
            centerMapWithSidebarOffset(marker.getLatLng());
            
            const lastPinBtn = document.getElementById('lastPinBtn');
            if (lastPinBtn) {
                lastPinBtn.style.display = 'none';
            }
        });
        
        marker.on('dragstart', function(e) {
            isDraggingMode = true;
            originalPosition = e.target.getLatLng();
            
                const markerElement = e.target.getElement();
                const pinImage = markerElement.querySelector('.pin-image');
                pinImage.classList.remove('pin-draggable');
                
                pinImage.style.filter = 'brightness(1.2) drop-shadow(0 0 10px rgba(59, 130, 246, 0.5))';
            });
            
            marker.on('drag', function(e) {
                const currentLatLng = e.target.getLatLng();
                updateSidebarCoordinates(currentLatLng.lat, currentLatLng.lng);
            });
            
            marker.on('dragend', function(e) {
                const newLatLng = e.target.getLatLng();
                
                updateSidebarCoordinates(newLatLng.lat, newLatLng.lng);
                
                const markerElement = e.target.getElement();
                const pinImage = markerElement.querySelector('.pin-image');
                pinImage.style.filter = 'brightness(1.1)';
                
                if (currentActiveMarker && currentActiveMarker.dragging.enabled()) {
                    pinImage.classList.add('pin-draggable');
                }
                
                setTimeout(() => {
                    isDraggingMode = false;
                }, 100);
            });
            
            marker.pinImage = pinImage;
            // const marker = L.marker([pin.latitude, pin.longitude], markerOptions).addTo(map);
            markersById[pin.id] = marker;
        });
        // after all markers are created
        if (window.requestedPinId && markersById[window.requestedPinId]) {
            const marker = markersById[window.requestedPinId];
            
            // simulate click
            setTimeout(() => {
                marker.fire('click');
            }, 900);
        }
        
    })
    .catch(err => console.error("Failed to load pins:", err));
    
    // if (requestedPinId !== null) {
        //     const targetId = parseInt(requestedPinId, 10);/
        
        //     const pin = pins.find(p => p.id === targetId);
        //     const marker = markersById[targetId];
        
        //     if (pin && marker) {
            //         currentActivePin = pin;
            //         currentActiveMarker = marker;
            //         reopenLastPin();
            //     } else {
                //         console.warn('Pin or marker not found for ID:', targetId);
                //     }
                // }
                
    function updateLastPinButton(pinData) {
        const lastPinBtn = document.getElementById('lastPinBtn');
    }
    
    function reopenLastPin() {
        if (currentActivePin && currentActiveMarker) {
            
            const markerElement = currentActiveMarker.getElement();
            const pinImage = markerElement.querySelector('.pin-image');
            
            //  visual 
            currentActiveMarker.isSelected = true;
            pinImage.classList.add('scale-125');
            
            showPinTab(
                currentActivePin.label || 'Pin', 
                currentActivePin.latitude, 
                currentActivePin.longitude, 
                currentActivePin
                );
                
                centerMapWithSidebarOffset(currentActiveMarker.getLatLng());
            }
            zoomMarkerPivot = currentActiveMarker;
        }
        
        function centerMapWithSidebarOffset(latlng) {
            const mapWidth = map.getSize().x;
            const sidebarWidth = document.getElementById("pinTab").offsetWidth || 100; 
            
        const offsetX = sidebarWidth * (-0.5); // value to center the map correctly
        
        const targetPoint = map.project(latlng, map.getZoom()).subtract([offsetX, 0]);
        const offsetLatLng = map.unproject(targetPoint, map.getZoom());
        
        map.setView(offsetLatLng, map.getZoom(), {
            animate: true,
            pan: { duration: 1}
        });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // render list comment
    function renderComment(c, postId) {
        const canDelete = c.user?.id === authUserId || ['admin', 'staff'].includes(authUserRole);
        
        const fullBody = c.body;
        const shortBody = fullBody.length > 150 ? fullBody.substring(0, 150) + '...' : fullBody;
        const isLong = fullBody.length > 150;
        
        const bodyHtml = isLong
        ? `<span class="comment-body" data-full="${escapeHtml(fullBody)}" data-short="${escapeHtml(shortBody)}">${escapeHtml(shortBody)}</span> <button class="text-xs text-blue-600 toggle-comment hover:underline">Show more</button>`
        : `<span class="comment-body">${escapeHtml(fullBody)}</span>`;
        
        return `
        <li>
            <strong>${c.user?.name ?? 'Unknown'}</strong>: 
            ${bodyHtml}
            <small class="block text-gray-500">
                (UID: ${c.user?.id ?? 'N/A'}) • 
                ${new Date(c.created_at).toLocaleString(undefined, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    })}
                    ${canDelete ? `
                        <form method="POST" action="/comments/${c.id}" onsubmit="return confirm('Delete this comment?')" class="inline">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').getAttribute('content')}">
                            <button type="submit" class="text-xs text-red-500 hover:underline">• Delete</button>
                            </form>
                            ` : ''}
                            </small>
                            </li>
                            `;
                        }
                        
                        function loadPostsForPin(latitude, longitude, pinId, sortBy) {
                            
                            function createSortingDropdown(container, latitude, longitude, pinId, currentSort = 'latest') {
                                
                                const existingDropdown = container.querySelector('.sorting-dropdown');
                                if (existingDropdown) {
                                    
                                    existingDropdown.value = currentSort;
                                    return;
                                }
                                
                                const sortingContainer = document.createElement("div");
                                sortingContainer.className = "sorting-container flex items-center justify-between bg-gray-50 p-3 rounded-lg border";
                                
                                const label = document.createElement("label");
                                label.className = "text-sm font-medium text-gray-700";
                                label.textContent = "Sort by:";
                                
                                const dropdown = document.createElement("select");
            dropdown.className = "sorting-dropdown ml-2 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white";
            
            //public
            const options = [
                { value: 'latest', text: 'Latest 🆕' },
                { value: 'oldest', text: 'Oldest ⏳' },
                { value: 'most_liked', text: 'Most ❤️' },
                { value: 'most_commented', text: 'Most 💬' }
                // opt untuk unmoderated dalam dropdown //sama button approve revoke foreach posrt
            ];
            
            //mod
            const userIsAdminOrStaff = window.authUserRole === 'admin' || window.authUserRole === 'staff';
            if (userIsAdminOrStaff) {
                options.push(
                    { value: 'moderated_only', text: 'mod_approved' },
                    { value: 'unmoderated_only', text: 'mod_pending' },
                    );
                }
                
                options.forEach(option => {
                    const optionElement = document.createElement("option");
                    optionElement.value = option.value;
                    optionElement.textContent = option.text;
                    optionElement.selected = option.value === currentSort;
                    dropdown.appendChild(optionElement);
                });
                
                dropdown.addEventListener('change', (e) => {
                    if (currentActivePin) {
                        loadPostsForPin(currentActivePin.latitude, currentActivePin.longitude, currentActivePin.id, e.target.value);
                    }
                });
                
                sortingContainer.appendChild(label);
                sortingContainer.appendChild(dropdown);
                container.appendChild(sortingContainer);
            }

            // helper function to add loading state
            function showLoadingState(container) {
                const loadingDiv = document.createElement("div");
                loadingDiv.className = "posts-loading flex items-center justify-center p-4 text-gray-500";
                loadingDiv.innerHTML = `
                <div class="w-4 h-4 mr-2 border-b-2 border-blue-500 rounded-full loading-spinner"></div>
                <span class="text-sm">Loading posts...</span>
                `;
                return loadingDiv;
            }
            
            

            // helper: create post element
            function createPostElement(post) {
                const postDiv = document.createElement("div");
                
                let cardClasses = "post-card post-item border rounded-lg p-3 mb-3 shadow-sm";
                if (post.moderated) {
                    cardClasses += " bg-white border-gray-200"; // Approved posts are white
                } else {
                    cardClasses += " bg-yellow-50 border-yellow-200"; // Unmoderated/pending posts are muted yellow
                }
                postDiv.className = cardClasses; // Apply the determined classes
                postDiv.setAttribute("data-post-id", post.id);
                
                const commentsPerPage = 2;
                const commentsContainerId = `comments-container-${post.id}`;
                
                const commentListItems = post.comments?.slice(0, commentsPerPage).map(c => renderComment(c, post.id)).join('') || '';
                
                let loadMoreButton = '';
                const remainingCount = (post.comments?.length || 0) - commentsPerPage;
                if (remainingCount > 0) {
                    loadMoreButton = `
                    <button type="button" class="mt-1 text-sm text-blue-600 hover:underline load-more-comments-btn" data-post-id="${post.id}" id="comment-toggle-${post.id}">
                        Show ${Math.min(2, remainingCount)} more comments
                        </button>
                        `;
                        window._commentChunks = window._commentChunks || {};
                        window._commentChunks[post.id] = { comments: post.comments.slice(commentsPerPage), index: 0 };
                    }
                    
                    const commentsHTML = `
                    <div class="pt-2 bg-transparent border-t comments-section" id="${commentsContainerId}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500 comments-count-text">
                                ${post.comments?.length || 0} ${post.comments?.length === 1 ? 'Comment' : 'Comments'}
                                </span>
                                </div>
                                <ul class="space-y-1 comments-list" data-post-id="${post.id}" id="comments-list-${post.id}">
                                    ${commentListItems}
                                    </ul>
                                    ${loadMoreButton}
                                    </div>
                                    `;
                                    
                                    const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
                                    const commentFormHTML = `
                                    <form method="POST" action="/posts/${post.id}/comments" class="mt-2 comment-form" data-post-id="${post.id}">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <textarea name="body" rows="2" class="w-full p-1 text-sm border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required placeholder="Write a comment..."></textarea>
                                        <button type="submit" class="px-2 py-1 mt-1 text-xs text-white transition-colors bg-blue-500 rounded hover:bg-blue-600">Comment</button>
                                        </form>
                                        `;
                                        
                                        // Post body logic
                                        const full = post.body || '';
                                        const short = full.length > 300 ? full.substring(0, 300) + '...' : full;
                                        const isLong = full.length > 300;
                                        const postBody = isLong
                                        ? `
                                        <p class="hidden mb-3 text-sm leading-relaxed text-gray-700 dont-delete-this-is-a-workaround">
                                            <span class="post-body" data-full="${full}" data-short="${short}">${short}</span>
                        <button class="ml-1 text-xs text-blue-600 toggle-post-body hover:underline">Show more</button>
                        </p>
                        <p class="mb-3 text-sm leading-relaxed text-gray-700">
                            <span class="post-body" data-full="${full}" data-short="${short}">${short}</span>
                            <button class="ml-1 text-xs text-blue-600 toggle-post-body hover:underline">Show more</button>
                            </p>
                            `
                            : `<p class="mb-3 text-sm leading-relaxed text-gray-700">${full}</p>`;
                            
                            // Build the complete post HTML
                            postDiv.innerHTML = `
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="text-sm font-semibold text-gray-800">${post.title || 'Untitled'}</h4>
                                <span class="text-xs text-gray-500">
                                    ${new Date(post.created_at).toLocaleString(undefined, {
                                        year: 'numeric',
                                        month: 'short',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        hour12: true
                                    })}
                                    </span>
                                    </div>
                                    
                                    ${post.image ? `
                                        <div class="mb-3">
                                            <img src="${post.image.startsWith('/storage/') ? post.image : `/storage/${post.image}`}" 
                                            alt="Post image" 
                            class="object-cover w-full mt-2 rounded-md">
                            </div>
                            ` : ''}
                            
                            <div class="flex items-center mb-2 space-x-2 text-xs">
                                <span class="text-gray-400">(UID: ${post.user?.id ?? 'N/A'})</span>
                    <span class="font-medium text-gray-700">${post.user?.name ?? 'Unknown'}</span>
                    ${post.is_owner ? '<span class="font-medium text-yellow-600">(You)</span>' : ''}
                    ${(authUserRole === 'admin' || authUserRole === 'staff') ? `
                    
                    <button 
                    class="toggle-moderation-btn ml-2 px-2 py-1 text-xs font-semibold 
                    ${post.moderated ? 'text-red-700 bg-white hover:bg-gray-100' : 'text-yellow-700 bg-yellow-100 bg-white hover:bg-yellow-200'}
                    border rounded transition-colors"
                    data-post-id="${post.id}" 
                    data-moderated="${post.moderated}"
                                    title="${post.moderated ? 'Click to revoke' : 'Click to approve'}"
                                    >
                                    ${post.moderated ? '⚠️ Revoke approval' : '⚠️ Pending approval'}
                                    </button>
                                    
                                    ` : ''}
                                    </div>
                                    
                                    ${postBody}
                                    <div data-post-id="${post.id}" class="flex items-center justify-between pt-2 mb-2 border-t">
                                        <div class="flex items-center space-x-2">
                                            <button 
                                            class="like-btn flex items-center space-x-1 px-2 py-1 rounded hover:bg-gray-100 transition-colors ${post.is_liked ? 'text-red-500 liked' : 'text-gray-500'}" 
                                            data-post-id="${post.id}"
                                            title="${post.is_liked ? 'Unlike' : 'Like'} this post"
                                            >
                                            <span class="heart-icon">${post.is_liked ? '❤️' : '🤍'}</span>
                                            <span class="text-xs">Like</span>
                                            </button>
                                            <div class="flex items-center space-x-1 text-xs">
                                                <span class="font-medium text-gray-600 likes-count">${post.likes_count || 0}</span>
                                                <span class="text-gray-500 likes-text">${(post.likes_count || 0) === 1 ? 'like' : 'likes'}</span>
                                                </div>
                                                
                                                ${post.is_owner ? `
                                                    <a href="/posts/${post.id}/edit" 
                                                    class="flex items-center px-2 py-1 space-x-1 text-yellow-600 transition-colors rounded like-btn hover:bg-gray-100 edit-post"
                                                    title="Edit this post">
                                                    <span>✏️</span>
                                                    <span class="text-xs">Edit</span>
                                                    </a>
                                                    ` : ''}
                                                    
                                                    ${(post.is_owner || authUserRole === 'admin' || authUserRole === 'staff') ? `
                                                    <button 
                                                    class="flex items-center px-2 py-1 space-x-1 text-xs text-red-500 transition-colors rounded like-btn delete-post-btn hover:bg-gray-100"
                                                    data-post-id="${post.id}"
                                                    title="Delete this post"
                                                    >
                                                    <span>🗑️</span>
                                                    <span>Delete</span>
                                                    </button>
                                                    ` : ''}
                                                    
                                                    
                                                    
                                                    
                                                    </div>
                                                    </div>
                                                    
                                                    ${commentsHTML}
                                                    ${commentFormHTML}
                                                    `;
                                                    
                                                    // Event listeners
                                                    setupPostEventListeners(postDiv, post.id);
                                                    
                                                    return postDiv;
                                                }

        // Helper: Setup all event listeners for a post
        function setupPostEventListeners(postDiv, postId) {
            // Toggle post body
            const togglePostBtn = postDiv.querySelector(".toggle-post-body");
            const bodySpan = postDiv.querySelector(".post-body");
            if (togglePostBtn && bodySpan) {
                togglePostBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    const showingFull = bodySpan.textContent === bodySpan.dataset.full;
                    bodySpan.textContent = showingFull ? bodySpan.dataset.short : bodySpan.dataset.full;
                    togglePostBtn.textContent = showingFull ? 'Show more' : 'Show less';
                });
            }
            
            // Toggle comment bodies
            postDiv.querySelectorAll('.toggle-comment').forEach(btn => {
                const commentSpan = btn.previousElementSibling;
                if (commentSpan && commentSpan.dataset.full && commentSpan.dataset.short) {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const showingFull = commentSpan.textContent === commentSpan.dataset.full;
                        commentSpan.textContent = showingFull ? commentSpan.dataset.short : commentSpan.dataset.full;
                        btn.textContent = showingFull ? 'Show more' : 'Show less';
                    });
                }
            });
            
            // Load more comments
            const loadMoreBtn = postDiv.querySelector('.load-more-comments-btn');
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    loadMoreCommentsForPost(postId);
                });
            }
            
            // Like button
            const likeBtn = postDiv.querySelector('.like-btn');
            if (likeBtn) {
                likeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (typeof toggleLike === 'function') {
                        toggleLike(postId);
                    } else {
                        console.warn('toggleLike function not found');
                    }
                });
            }
        }
        
        function loadMoreCommentsForPost(postId) {
            const chunk = window._commentChunks?.[postId];
            if (!chunk) return;
            
            const container = document.getElementById(`comments-list-${postId}`);
            const btn = document.getElementById(`comment-toggle-${postId}`);
            
            if (!container || !btn) return;
            
            const count = 2;
            const start = chunk.index;
            const end = start + count;
            const currentChunk = chunk.comments.slice(start, end);
            
            // add comment
            currentChunk.forEach(c => {
                container.insertAdjacentHTML('beforeend', renderComment(c, postId));
            });
            
            // comment body fold
            container.querySelectorAll('.toggle-comment').forEach(toggleBtn => {
                if (!toggleBtn.hasAttribute('data-listener-added')) {
                    const commentSpan = toggleBtn.previousElementSibling;
                    if (commentSpan && commentSpan.dataset.full && commentSpan.dataset.short) {
                        toggleBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            const showingFull = commentSpan.textContent === commentSpan.dataset.full;
                            commentSpan.textContent = showingFull ? commentSpan.dataset.short : commentSpan.dataset.full;
                            toggleBtn.textContent = showingFull ? 'Show more' : 'Show less';
                        });
                        toggleBtn.setAttribute('data-listener-added', 'true');
                    }
                }
            });
            
            chunk.index = end;
            
            // comment fold
            if (chunk.index >= chunk.comments.length) {
                btn.remove();
            } else {
                const remaining = chunk.comments.length - chunk.index;
                btn.textContent = `Show ${Math.min(count, remaining)} more comments`;
            }
        }
        
        // Main execution
        const postsContainer = document.getElementById("pinPosts");
        if (!postsContainer) {
            console.error('Posts container not found');
            return;
        }
        
        // Create or update sorting dropdown
        createSortingDropdown(postsContainer, latitude, longitude, pinId, sortBy || 'latest');
        
        // Find the posts content area (after sorting dropdown)
        let postsContentArea = postsContainer.querySelector('.posts-content');
        if (!postsContentArea) {
            postsContentArea = document.createElement("div");
            postsContentArea.className = "posts-content";
            postsContainer.appendChild(postsContentArea);
        }
        
        // Show loading state
        const loadingElement = showLoadingState(postsContentArea);
        postsContentArea.innerHTML = "";
        postsContentArea.appendChild(loadingElement);
        
        // Fetch posts
        fetch(`/posts/by-pin?pin_id=${pinId}${sortBy ? `&sort=${sortBy}` : ''}`)
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            return res.json();
        })
        .then(posts => {
            postsContentArea.innerHTML = "";
            
            if (!Array.isArray(posts) || posts.length === 0) {
                const emptyMessage = getEmptyMessage(sortBy);
                postsContentArea.innerHTML = `<p class='p-4 italic text-center text-gray-500'>${emptyMessage}</p>`;
                return;
            }
            
            // Show first 2 posts initially
            const initialPosts = posts.slice(0, 2);
            initialPosts.forEach(post => {
                postsContentArea.appendChild(createPostElement(post));
            });
            
            // Handle remaining posts (collapsible)
            const hiddenPosts = posts.slice(2);
            if (hiddenPosts.length > 0) {
                const hiddenContainer = document.createElement("div");
                hiddenContainer.classList.add("hidden");
                hiddenContainer.id = `hidden-posts-${pinId}`;
                
                hiddenPosts.forEach(post => {
                    hiddenContainer.appendChild(createPostElement(post));
                });
                
                postsContentArea.appendChild(hiddenContainer);
                
                // Toggle button for hidden posts
                const toggleBtn = document.createElement("button");
                toggleBtn.className = "toggle-btn w-full text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 py-2 px-3 rounded-md transition-all mt-2 border border-blue-200";
                toggleBtn.textContent = `Show ${hiddenPosts.length} more posts`;
                
                toggleBtn.addEventListener('click', () => {
                    const isHidden = hiddenContainer.classList.contains("hidden");
                    hiddenContainer.classList.toggle("hidden");
                    toggleBtn.textContent = isHidden 
                    ? `Hide ${hiddenPosts.length} posts`
                    : `Show ${hiddenPosts.length} more posts`;
                    
                    if (!isHidden) {
                        toggleBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
                
                postsContentArea.appendChild(toggleBtn);
            }
            
            // Summary
            const summary = document.createElement("div");
            summary.className = "posts-summary mt-4 p-2 bg-gray-50 rounded text-xs text-gray-600 text-center";
            summary.innerHTML = `<span>${posts.length} ${posts.length === 1 ? 'post' : 'posts'} found at this location</span>`;
            postsContentArea.appendChild(summary);
        })
        .catch(error => {
            console.error('Error loading posts:', error);
            postsContentArea.innerHTML = `
            <div class="p-4 text-center rounded-lg error-state">
                <p class='mb-2 text-red-500'>Failed to load posts: ${error.message}</p>
                <button 
                class="text-sm text-blue-600 underline transition-colors hover:text-blue-800 retry-btn"
                data-latitude="${latitude}" 
                data-longitude="${longitude}" 
                data-pin-id="${pinId}"
                data-sort-by="${sortBy || ''}"
                >
                Try again
                </button>
                </div>
                `;
                
                // Add retry functionality
                const retryBtn = postsContentArea.querySelector('.retry-btn');
                if (retryBtn) {
                    retryBtn.addEventListener('click', () => {
                        loadPostsForPin(
                            parseFloat(retryBtn.dataset.latitude),
                            parseFloat(retryBtn.dataset.longitude),
                            retryBtn.dataset.pinId,
                            retryBtn.dataset.sortBy || undefined
                            );
                        });
                    }
                });
            }
            
            function getEmptyMessage(sortBy) {
                switch(sortBy) {
                    case 'moderated_only':
                        return 'No approved posts yet for this pin.';
                        case 'unmoderated_only':
                return 'No pending posts for this pin.';
                case 'unmoderated_posts':
                    return 'No unmoderated posts for this pin.';
            case 'all_posts':
                return 'No posts yet for this pin.';
                default:
                    return 'No posts yet for this pin.';
                }
            }
            
            async function toggleLike(postId) {
                try {
                    const response = await fetch(`/posts/${postId}/like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                updateLikeButton(postId, data.is_liked, data.likes_count);
            } else {
                if (response.status === 401) {
                    showToast('Please log in to like posts');
                } else {
                    showToast(data.message || 'Error occurred');
                }
            }
        } catch (error) {
            console.error('Error toggling like:', error);
            showToast('An error occurred. Please try again.');
        }
    }
    
    function updateLikeButton(postId, isLiked, likesCount) {
        const postElement = document.querySelector(`[data-post-id="${postId}"]`);
        if (!postElement) return;
        
        const likeBtn = postElement.querySelector('.like-btn');
        const likesCountEl = postElement.querySelector('.likes-count');
        const heartIcon = postElement.querySelector('.heart-icon');
        const likesTextEl = postElement.querySelector('.likes-text');
        
        if (likeBtn && heartIcon) {
            if (isLiked) {
                likeBtn.classList.add('text-red-500');
                likeBtn.classList.remove('text-gray-500');
                heartIcon.textContent = '❤️';
                likeBtn.title = 'Unlike this post';
            } else {
                likeBtn.classList.add('text-gray-500');
                likeBtn.classList.remove('text-red-500');
                heartIcon.textContent = '🤍';
                likeBtn.title = 'Like this post';
            }
        }
        
        if (likesCountEl) {
            likesCountEl.textContent = likesCount;
        }
        
        if (likesTextEl) {
            likesTextEl.textContent = likesCount === 1 ? 'like' : 'likes';
        }
    }
    
    function showPinTab(pinName, lat, lng, pinData = null) {
        
        const bannerImage = document.getElementById("pinBanner");
        const pinBody = document.getElementById("pinBody");
        
        const editBtn = document.getElementById("editPinBtn");
        const deleteBtn = document.getElementById("deletePinBtn");
        const addPostButton = document.getElementById("add-post-button");
        
        const movePinBtn = document.getElementById("movePinBtn");
        const savePositionBtn = document.getElementById("savePositionBtn");
        
        const approvePinBtn = document.getElementById("approvePinBtn");
        const revokePinBtn = document.getElementById("revokePinBtn");
        
        if (pinData) {
            
            currentPinId = pinData.id;
            
            // --- Show/Hide Buttons based on permissions ---
            const user = window.authUserId;
            const userIsOwner = user && user === pinData.user_id;
            const userIsAdminOrStaff = window.authUserRole === 'admin' || window.authUserRole === 'staff';
            
            // Show Edit button ONLY for the owner
            editBtn.style.display = userIsOwner ? 'inline-block' : 'none';
            
            // Show Delete button for Owner, Admin, or Staff
            deleteBtn.style.display = (userIsOwner || userIsAdminOrStaff) ? 'inline-block' : 'none';
            
            movePinBtn.style.display = (userIsOwner || userIsAdminOrStaff) ? 'inline-block' : 'none';
            savePositionBtn.style.display = (userIsOwner || userIsAdminOrStaff) ? 'inline-block' : 'none';

            savePositionBtn.style.display = 'none'; // Hide by default, only show when in move mode
            
            if (userIsAdminOrStaff) {
                if (pinData.moderated) {
                    // Pin is approved - show revoke button
                    approvePinBtn.style.display = 'none';
                    revokePinBtn.style.display = 'inline-block';
                    revokePinBtn.setAttribute('data-pin-id', pinData.id);
                } else {
                    // Pin is not approved - show approve button
                    approvePinBtn.style.display = 'inline-block';
                    revokePinBtn.style.display = 'none';
                    approvePinBtn.setAttribute('data-pin-id', pinData.id);
                }
            } else {
                // User is not admin/staff - hide both buttons
                approvePinBtn.style.display = 'none';
                revokePinBtn.style.display = 'none';
            }
            
            // --- Populate UI ---
            if (pinData.banner) {
                bannerImage.src = `/storage/${pinData.banner}`;
                bannerImage.style.display = 'block';
            } else {
                bannerImage.style.display = 'none';
            }
            
            document.getElementById("pinTitle").textContent = pinData.label || 'Unnamed Pin';
            pinBody.textContent = pinData.body || 'No description provided.';
            document.getElementById("pinOwnerId").textContent = pinData.user_id;
            
            // custom icon in sidebar
            if (pinData.iconUrl) {
                const iconPreview = document.getElementById("pinIconPreview");
                if (iconPreview) {
                    iconPreview.src = pinData.iconUrl;
                    iconPreview.style.display = 'inline-block';
                }
            }
            
        } else {
            // Hide all buttons for a new, unsaved pin
            editBtn.style.display = 'none';
            deleteBtn.style.display = 'none';
            approvePinBtn.style.display = 'none'; 
            revokePinBtn.style.display = 'none';
            
        }
        
        const latitude = Number(lat);
        const longitude = Number(lng);
        
        document.getElementById("globalCoords").innerHTML = `Y: ${latitude.toFixed(2)} X: ${longitude.toFixed(2)}`;
        document.getElementById('add-post-button').href = `/posts/create?lat=${latitude.toFixed(2)}&lng=${longitude.toFixed(2)}&pin_id=${currentPinId ?? ''}`;
        
        openPinTab(); 
        loadPostsForPin(latitude, longitude, currentPinId); 
    }
    
    function showToast(message, duration = 3000) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        if (!toast || !toastMessage) return;
        
        toastMessage.textContent = message;
        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');
        
        setTimeout(() => {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0');
        }, duration);
    }
    
    // marker scale (unused)
    function getIconForZoom(zoomLevel, baseSize = 40) {
        const scale = zoomLevel / 13; // 13 = base zoom level you use normally
        const size = Math.max(20, baseSize * scale); // Minimum icon size
        return [size, size];
    }
    
    function updateSidebarCoordinates(lat, lng) {
        const globalCoords = document.getElementById("globalCoords");
        if (globalCoords) {
            globalCoords.innerHTML = `Y: ${lat.toFixed(2)} X: ${lng.toFixed(2)}`;
        }
        
        // Update the add-post button URL with new coordinates
        const addPostBtn = document.getElementById('add-post-button');
        if (addPostBtn && currentPinId) {
            addPostBtn.href = `/posts/create?lat=${lat.toFixed(2)}&lng=${lng.toFixed(2)}&pin_id=${currentPinId}`;
        }
    }
    
    async function savePinPosition() {
        if (!currentActiveMarker || !currentActivePin) {
            showNotification('No pin selected', 'error');
            return;
        }
        
        const currentLatLng = currentActiveMarker.getLatLng();
        
        try {
            const response = await fetch(`/pins/${currentActivePin.id}/update-position`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    latitude: currentLatLng.lat,
                    longitude: currentLatLng.lng
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Update the pin object with new coordinates
                currentActivePin.latitude = currentLatLng.lat;
                currentActivePin.longitude = currentLatLng.lng;
                
                showNotification(`Pin moved successfully! ${data.updated_posts_count} posts updated.`, 'success');
                
                // Update original position so we don't revert on next drag
                originalPosition = currentLatLng;
                
            } else {
                // Error - revert marker position
                currentActiveMarker.setLatLng(originalPosition);
                updateSidebarCoordinates(originalPosition.lat, originalPosition.lng);
                showNotification(data.error || 'Failed to update pin position', 'error');
            }
        } catch (error) {
            console.error('Error updating pin position:', error);
            // Revert marker position on error
            currentActiveMarker.setLatLng(originalPosition);
            updateSidebarCoordinates(originalPosition.lat, originalPosition.lng);
            showNotification('Network error. Pin position reverted.', 'error');
        }
    }
    
    
    let isMoveModeActive = false; 
    
    function togglePinDragMode() {
        // Only proceed if a pin is currently active OR we're already in move mode and trying to cancel.
        if (!currentActiveMarker && !isMoveModeActive) {
            showNotification("Please select a pin first to enable move mode.", 'alert');
            return;
        }

        const movePinBtn = document.getElementById("movePinBtn");
        const savePositionBtn = document.getElementById("savePositionBtn");
        const movePinLabel = document.getElementById("movePinLabel"); // Assumed to exist
        
        const markerElement = currentActiveMarker.getElement(); // Get the current active marker's root div
        const pinImage = currentActiveMarker.pinImage;       // Use the stored img reference
        
        isMoveModeActive = !isMoveModeActive; // Toggle the global state
        
        if (isMoveModeActive) { // Entering "move mode"
        currentActiveMarker.dragging.enable(); // Enable dragging for this specific marker
        originalPosition = currentActiveMarker.getLatLng(); // Store original position
        
        // Visual indicator that *this* pin is now draggable/in move mode
        if (pinImage) {
            pinImage.classList.add('pin-draggable'); // Start shaking animation
                pinImage.style.filter = 'brightness(1.1)'; // Apply highlight
            }
            
            // Update button appearances
            movePinBtn.textContent = "✖️"; // Change to "Cancel" icon
            movePinLabel.textContent = "Move Pin • Cancel";
            savePositionBtn.style.display = 'inline-block'; // Show Save button
            movePinBtn.classList.remove('bg-white/40', 'hover:bg-white/80', 'rounded-3xl');
            movePinBtn.classList.add('bg-red-500', 'hover:bg-red-600', 'rounded-xl'); // Change button style to indicate active/cancel state
            
        } else { // Exiting "move mode"
        currentActiveMarker.dragging.disable(); // Disable dragging for this marker
        
        // Remove visual indicators from the pin
        if (pinImage) {
            pinImage.classList.remove('pin-draggable'); // Stop shaking
            // Reset filter only if it's not being explicitly set by moderation status (grayscale)
            // If the pin is selected, it maintains scale-125, so no full filter reset
            if (!currentActivePin.moderated) { // Check its moderated status
                pinImage.style.filter = 'grayscale(100%)'; // Restore grayscale if unmoderated
            } else {
                pinImage.style.filter = ''; // Clear filter if moderated
            }
        }
        
        // Reset button appearances to default "Move Pin"
        movePinBtn.textContent = "🖐️"; // Reset to "Move" icon
        movePinLabel.textContent = "Move Pin"; // Reset label
        savePositionBtn.style.display = 'none'; // Hide Save button
        movePinBtn.classList.remove('bg-red-500', 'hover:bg-red-600', 'rounded-xl'); // Reset button style
        movePinBtn.classList.add('bg-white/40', 'hover:bg-white/80', 'rounded-3xl');
        
        if (originalPosition && currentActiveMarker.getLatLng() && !currentActiveMarker.getLatLng().equals(originalPosition)) {
            currentActiveMarker.setLatLng(originalPosition); // Revert marker position
            updateSidebarCoordinates(originalPosition.lat, originalPosition.lng); // Update sidebar coordinates
            showNotification("Pin movement cancelled, position reverted.", 'info');
        }
        originalPosition = null; // Clear original position
    }
}


function clearPinSelection() {
    if (currentActiveMarker) {
        if (currentActiveMarker.pinImage) { // Use stored reference
            currentActiveMarker.pinImage.classList.remove('scale-125', 'pin-draggable');
            currentActiveMarker.pinImage.style.filter = ''; // Remove any direct filter
        }
        currentActiveMarker.isSelected = false; // Mark as unselected
        currentActiveMarker.dragging.disable(); // Ensure dragging is disabled if it was enabled
    }
    
    isMoveModeActive = false; // Reset the global move mode flag

    const movePinBtn = document.getElementById("movePinBtn");
    const savePositionBtn = document.getElementById("savePositionBtn");
    const movePinLabel = document.getElementById("movePinLabel");
    
    if (movePinBtn && savePositionBtn && movePinLabel) {
        movePinBtn.textContent = "🖐️"; // Reset button text/icon
        movePinLabel.textContent = "Move Pin"; // Reset label
        savePositionBtn.style.display = 'none'; // Hide save button
        movePinBtn.classList.remove('bg-red-500', 'hover:bg-red-600', 'rounded-xl'); // Reset button styling
        movePinBtn.classList.add('bg-white/40', 'hover:bg-white/80', 'rounded-3xl');
        movePinBtn.style.display = 'none'; // Hide the move button from the floating menu
    }
    
    zoomMarkerPivot = null;
    isDraggingMode = false; // This flag is for "drag event currently in progress", keep.

}

function onMapMouseMove(e) {
    const ghostIcon = L.icon({
        iconUrl: '/storage/icons/default.png',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -35]
    });
    
    if (!ghostMarker) {
        ghostMarker = L.marker(e.latlng, {
            icon: ghostIcon,
            opacity: 0.7,
            interactive: false
        }).addTo(map);
    } else {
        ghostMarker.setLatLng(e.latlng);
    }
}

function closePinTab() {
    document.getElementById("pinTab").style.right = "-24rem"; // Use your constant
    console.log("Closing pin tab");

    if (isMoveModeActive) {
            togglePinDragMode(); 
        }
    
    // Make lastPinBtn visible only when the sidebar is closed AND a pin was previously active
        const lastPinBtn = document.getElementById('lastPinBtn');
        if (lastPinBtn) { // Ensure button exists
            if (currentActivePin) { // Check if there's a pin still selected
            lastPinBtn.style.display = 'block'; // Show if a pin is active
        } else {
            lastPinBtn.style.display = 'none'; // Hide if no pin is active
        }
    }
    
    // Hide move/save buttons from floating menu when sidebar is closed
    const movePinBtn = document.getElementById("movePinBtn");
    const savePositionBtn = document.getElementById("savePositionBtn");
    if (movePinBtn) movePinBtn.style.display = 'none';
    if (savePositionBtn) savePositionBtn.style.display = 'none';
    
    // Recalculate toast position after sidebar closes
    // showToast(document.getElementById('toastMessage').textContent, 100);
}

function openPinTab() {
    lastPinBtn.style.display = 'none';
    console.log("Opening pin tab");
    document.getElementById("pinTab").style.right = "0";
}

function showCreatePinModal(lat, lng) {
    // Find the form and reset it in case it was used before
    const form = document.getElementById('createPinForm');
    form.reset();
    
    // Set the hidden latitude and longitude fields
    document.getElementById('formLatitude').value = lat;
    document.getElementById('formLongitude').value = lng;
    
    document.getElementById("pin_icon").addEventListener("change", function (e) {
        const file = e.target.files[0];
        const preview = document.getElementById("createIconPreview");

        if (file) {
            const reader = new FileReader();
            reader.onload = function (evt) {
                preview.src = evt.target.result;
                preview.style.display = "inline-block";
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = "none";
        }
    })
    
    
    // Make the modal visible
    document.getElementById('createPinModal').classList.remove('hidden');
}
        
function toggleAddPinMode() {
    const addPinBtn = document.getElementById("addPin");
    const addPinLabel = document.getElementById("addPinLabel");
    
    if (isAddPinModeActive) {
        // Exit add pin mode
        exitAddPinMode();
    } else {
        // Enter add pin mode
        enterAddPinMode();
    }
}

function enterAddPinMode() {
    isAddPinModeActive = true;
    
    const addPinBtn = document.getElementById("addPin");
    const addPinLabel = document.getElementById("addPinLabel");
    
    // Show the adding pin wrapper
    document.getElementById('addingPinWrapper').classList.remove('hidden');
    document.getElementById('map').classList.add('map-add-pin-mode');
    
    // Update button appearance to active state
    addPinBtn.textContent = "✖️"; // Or use "📍" for a different pin icon
    addPinLabel.textContent = "Add Pin • Cancel";
    addPinBtn.classList.remove('bg-white/40', 'hover:bg-white/80', 'rounded-3xl');
    addPinBtn.classList.add('bg-red-500', 'hover:bg-red-600', 'rounded-xl');
    
    // Show ghost marker on mouse move
        map.on('mousemove', onMapMouseMove);
        
        // Wait for the user to click on the map
        map.on('click', onMapClickToAddPin);
    }
    
    function exitAddPinMode() {
        isAddPinModeActive = false;
        
        const addPinBtn = document.getElementById("addPin");
        const addPinLabel = document.getElementById("addPinLabel");
        
        // Hide the adding pin wrapper
        document.getElementById('addingPinWrapper').classList.add('hidden');
        document.getElementById('map').classList.remove('map-add-pin-mode');
        
        // Reset button appearance to inactive state
        addPinBtn.textContent = "📌";
        addPinLabel.textContent = "Add Pin";
        addPinBtn.classList.remove('bg-red-500', 'hover:bg-red-600', 'rounded-xl');
        addPinBtn.classList.add('bg-white/40', 'hover:bg-white/80', 'rounded-3xl');
        
        // Remove event listeners
        map.off('mousemove', onMapMouseMove);
        map.off('click', onMapClickToAddPin);
        
        // Remove ghost marker if it exists
        if (ghostMarker) {
            ghostMarker.remove();
            ghostMarker = null;
        }
    }
    
    function onMapClickToAddPin(e) {
        showCreatePinModal(e.latlng.lat, e.latlng.lng);
        exitAddPinMode();
    }
    
    function resetModerationButtons() {
        const approvePinBtn = document.getElementById("approvePinBtn");
        const revokePinBtn = document.getElementById("revokePinBtn");
        
        // Reset approve button
        if (approvePinBtn) {
            approvePinBtn.disabled = false;
            approvePinBtn.style.opacity = '1';
            approvePinBtn.innerHTML = '<span>✔️</span><br><span class="text-xs">Approve</span>';
            approvePinBtn.classList.remove('text-green-600');
            approvePinBtn.classList.add('text-yellow-600', 'hover:bg-gray-100');
        }
        
        // Reset revoke button
        if (revokePinBtn) {
            revokePinBtn.disabled = false;
            revokePinBtn.style.opacity = '1';
            revokePinBtn.innerHTML = '<span>❌</span><br><span class="text-xs">Revoke</span>';
            revokePinBtn.classList.remove('text-green-600');
            revokePinBtn.classList.add('text-red-600', 'hover:bg-gray-100');
        }
    }
    
    // document.addEventListener('keydown', function(e) {
        //     if (e.key === 'Escape' && isAddPinModeActive) {
            //         exitAddPinMode();
            //     }
            // });
            
            // toast demo
            function showNotification(message, type = 'info') {
                console.log(`${type.toUpperCase()}: ${message}`);
                // You can implement toast notifications, alerts, or any other UI feedback here
                showToast(message); // Temporary - replace with your notification system
            }
            
        </script>

</html>
@endsection
//copirek yovan g darmawan 2025
