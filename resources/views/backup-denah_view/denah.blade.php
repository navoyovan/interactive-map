
@extends('layouts.app')

@section('content')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.50"/>
    <title>Interactive Map</title>

    <!-- Leaflet -->
    <link rel="stylesheet" href="/offlined/leaflet.css" />
    <script src="/offlined/leaflet.js"></script>

    <!-- Tailwind (offline) -->    {{-- pindah ke app.blade.php --}}
    <!--script src="/offlined/tailwind.js"></script-->

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body { margin: 0; padding: 0; height: 100%; }
        #map { position: absolute; width: 100vw; height: 100vh; z-index: 1; background-color: #999999; }
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
        cursor: url('\img\markerd-add.png') 16 32, auto; /* The numbers are the "hotspot" of the cursor (half width, full height) */ 
        }

        .grayscale-icon img {
            filter: grayscale(100%);
            opacity: 0.7;
        }

        .kustom-button {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3rem;              /* w-12 */
        height: 3rem;             /* h-12 */
        background-color: rgba(255, 255, 255, 0.7); /* bg-white/70 */
        backdrop-filter: blur(4px); /* backdrop-blur-sm */
        color: #1f2937;           /* text-gray-800 */
        border-radius: 1.5rem;    /* rounded-3xl */

        }

        .kustom-button:hover {
        background-color: rgba(255, 255, 255, 0.8); /* hover:bg-white/80 */
        transform: scale(1.1);                      /* hover:scale-110 */
        border-radius: 0.75rem;                     /* hover:rounded-xl */
        }

        .kustom-border {
            box-shadow: 
                inset 0 0 0 1px rgba(255, 255, 255, 0.2),  /* Inner border */
                0 4px 6px -1px rgba(0, 0, 0, 0.1),         /* Soft shadow */
                0 2px 4px -1px rgba(0, 0, 0, 0.06);        /* Additional shadow layer */
            
            transition: all 300ms ease-in-out;
        }

        .kustom-border:hover {
            box-shadow: 
                inset 0 0 0 2px rgba(255, 255, 255, 0.4),  /* Stronger inner border */
                0 10px 15px -3px rgba(0, 0, 0, 0.1),       /* Elevated shadow */
                0 4px 6px -2px rgba(0, 0, 0, 0.05),        /* Soft secondary shadow */
                0 0 0 3px rgba(255, 255, 255, 0.1);        /* Outer glow ring */
        }

        .prevent-svg-move-left {
            position: relative;
            items: center;
            justify-content: center;
        }
    </style>

</head>

<body>

    <div class="relative min-h-screen"> {{-- wrapper that ensures content space --}}
    
    {{-- Map main content 
    --}}
    <div id="map" class="w-full h-50vh"></div>

    <!-- Modal add pin 
    -->
    <div id="createPinModal" class="fixed inset-0 z-[3000] hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-[400px]">
            <h2 class="mb-4 text-lg font-bold">Create New Pin</h2>
            
            {{-- This form sends all data, including files, to our controller --}}
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
                    <input type="file" id="banner" name="banner" accept="image/*" class="w-full p-2 border rounded">
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

    <!-- Modal edit pin 
    -->
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
                    <p class="text-xs text-gray-500 mb-1">Uploading a new image will replace the old one.</p>
                    <input type="file" id="edit_banner" name="banner" accept="image/*" class="w-full p-2 border rounded">
                </div>

                <div class="mb-4">
                    <label for="edit_pin_icon" class="block mb-1 font-semibold">New Pin Icon (Optional)</label>
                    <p class="text-xs text-gray-500 mb-1">Uploading a new icon will replace the old one.</p>
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

    <!-- Modal for custom icon
     -->
    <div id="iconModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-black rounded-xl shadow-lg p-6 w-full max-w-md relative">
            <!-- Close -->
            <button onclick="closeIconModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">&times;</button>

            <h2 class="text-xl font-bold mb-4">Update Pin Icon</h2>

            <form id="pinIconForm" method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('POST')

                <!-- Default icon selection -->
                <label for="icon" class="block mb-1 font-medium">Choose a default icon:</label>
                <select name="icon" id="icon" class="w-full border rounded p-2 mb-4">
                    <option value="">-- Select Default Icon --</option>
                    <option value="blue-pin.png">Blue</option>
                    <option value="red-pin.png">Red</option>
                    <option value="green-pin.png">Green</option>
                </select>

                <!-- Custom icon upload -->
                <label for="icon_upload" class="block mb-1 font-medium">Or upload your own icon:</label>
                <input type="file" name="icon_upload" id="icon_upload" accept="image/*" class="w-full border rounded p-2 mb-4" />

                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Update Icon
                </button>
            </form>
        </div>
    </div>

    <!-- Pin Info Sidebar 
    -->
    <div id="pinTab" class="fixed top-0 right-0 z-50 flex flex-col h-full bg-white shadow-lg w-80">
        <div class="flex items-center justify-between h-16 px-1 py-2 text-white bg-gray-800">
            <img id="pinIconPreview" src="" alt="Pin Icon" style="display:none; width: 40px; height: 40px;" />
            <span id="pinTitle" class="font-semibold">Pin Name</span> 

            <div class="flex flex-col items-end text-xs text-gray-800">
                <span id="globalCoords">Y:# X:#</span>
                <span>Owner ID: <span id="pinOwnerId"></span></span>
            </div>

            <div class="flex gap-1 ml-2">
                {{-- <button id="togglePinDetails" class="px-2 py-1 text-white bg-gray-800 rounded hover:bg-gray-700">🔽⏹️</button> --}}
                <button id="togglePinDetails" class="px-2 py-1 relative flex items-center justify-center bg-gray-800 text-white hover:bg-gray-700 hover:scale-125 rounded-3xl hover:rounded-xl transition-all duration-300 ease-in-linear">
                    ⏏️</button>
                <button id="closePinTab" class="px-2 py-1 relative flex items-center justify-center bg-gray-800 text-white hover:bg-gray-700 hover:scale-125 rounded-3xl hover:rounded-xl transition-all duration-300 ease-in-linear">
                    ❌</button>
            </div>
        </div>

        <!-- Collapsible content -->
        <div id="pinDetails" class="transition-all duration-200 ease-in-out">
            <img id="pinBanner" src="" alt="Pin Banner" class="object-cover w-full h-40 hidden">

            <div class="p-4 bg-white/25 backdrop-blur-sm">
                <p id="pinBody">Details about the pin will go here.</p>

                <div class="flex gap-2 mt-4">
                    <a id="add-post-button" href="#">
                        <button class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">Add Post</button>
                    </a>
                    <button id="editPinBtn" class="px-3 py-1 text-sm text-white bg-yellow-500 rounded hidden">Edit</button>
                    <button id="deletePinBtn" class="px-3 py-1 text-sm text-white bg-red-500 rounded hidden">Delete</button>
                    {{-- <button id="changeIconBtn" class="px-3 py-1 text-sm text-white bg-indigo-500 rounded">Change Pin Icon</button> --}}
                    {{-- </button> --}}

                </div>
            </div>
        </div>

        {{-- <hr class="my-2"> --}}
        <div id="pinPosts" class="flex-1 px-4 pb-8 pt-4 space-y-2 overflow-y-auto text-sm text-gray-700">
            <p class="italic text-gray-500">Loading posts...</p>
            <hr class="my-2">
        </div>
        <hr class="my-2">
    
        <!-- Floating Button -->
        <div class="absolute top-20 left-[-4rem] flex flex-col items-center space-y-2">
                
            <button id="zoomInBUTT" class="relative flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm text-gray-800 ring-1 ring-white/20 ring-inset shadow-lg hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl transition-all duration-300 ease-in-linear">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zoom-in-icon lucide-zoom-in"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/><line x1="11" x2="11" y1="8" y2="14"/><line x1="8" x2="14" y1="11" y2="11"/></svg>
                </button>
            <button id="zoomOutBUTT" class="relative flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm text-gray-800 ring-1 ring-white/20 ring-inset shadow-lg hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl transition-all duration-300 ease-in-linear">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-zoom-out-icon lucide-zoom-out"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/><line x1="8" x2="14" y1="11" y2="11"/></svg>
                                </button>
            <button id="dropdownButton" class="relative flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm text-gray-800 ring-1 ring-white/20 ring-inset shadow-lg hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl transition-all duration-300 ease-in-linear">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-menu-icon lucide-menu"><path d="M4 12h16"/><path d="M4 18h16"/><path d="M4 6h16"/></svg>
                </button>
            {{-- ☰ --}}

            <div id="dropdownMenu" class="z-50 flex flex-col items-center space-y-2">

                <a href="#" id="addPin" class="relative flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm text-gray-800 ring-1 ring-white/20 ring-inset shadow-lg hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl transition-all duration-300 ease-in-linear  ">
                    
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin-plus-icon lucide-map-pin-plus"><path d="M19.914 11.105A7.298 7.298 0 0 0 20 10a8 8 0 0 0-16 0c0 4.993 5.539 10.193 7.399 11.799a1 1 0 0 0 1.202 0 32 32 0 0 0 .824-.738"/><circle cx="12" cy="10" r="3"/><path d="M16 18h6"/><path d="M19 15v6"/></svg>
                    </a>
                {{-- ➕ --}}
                <button id="centerMapBtn" class="relative flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm text-gray-800 ring-1 ring-white/20 ring-inset shadow-lg hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl transition-all duration-300 ease-in-linear">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-locate-icon lucide-locate"><line x1="2" x2="5" y1="12" y2="12"/><line x1="19" x2="22" y1="12" y2="12"/><line x1="12" x2="12" y1="2" y2="5"/><line x1="12" x2="12" y1="19" y2="22"/><circle cx="12" cy="12" r="7"/></svg>
                    </button>
                {{-- 🎯 --}}
                <button id="lastPinBtn" class="relative flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm text-gray-800 ring-1 ring-white/20 ring-inset shadow-lg hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl transition-all duration-300 ease-in-linear">
                  🎯
                </button>
                <button id="placeholder" class="relative flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm text-gray-800 ring-1 ring-white/20 ring-inset shadow-lg hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl transition-all duration-300 ease-in-linear">
                    ❌</button>
                    <button id="placeholder" class="relative flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm text-gray-800 ring-1 ring-white/20 ring-inset shadow-lg hover:bg-white/80 hover:scale-110 hover:ring-2 hover:ring-white/40 hover:shadow-xl rounded-3xl hover:rounded-xl transition-all duration-300 ease-in-linear">
                    ❌</button>
            </div>
        </div>


    </div>

    <!-- Menu ▼ | Add Pin Controls | ... | 
    -->
    <div class="absolute z-50 flex items-center space-x-4 top-10 left-1/2 -translate-x-1/2">
        <div id="addingPinWrapper" class="hidden flex items-center bg-white px-4 py-2 rounded-md shadow-md  ">
            🧷 Click on the map to place a pin...
            <button id="cancelPin" class="ml-2 text-red-500 hover:underline">Cancel</button>
        </div>

        {{-- <div class="relative">
            <button id="dropdownButton" class="px-4 py-2 bg-white rounded shadow-md">▼</button>
            <div id="dropdownMenu" class="z-50 hidden mt-2 bg-white rounded shadow-md">

                
                <a href="#" id="addPin" class="block px-4 py-2 text-gray-700 hover:bg-gray-200">Add</a>
            </div>
        </div> --}}
    </div> 

    <!-- Toast Message 
    -->
    <div id="toast" class="fixed bottom-6 right-6 bg-gray-800 text-white px-4 py-2 rounded shadow-lg opacity-0 transition-opacity duration-3000 z-[9999]">
        <span id="toastMessage">This is a toast</span>
    </div>

    <!-- Map and CSRF 
    -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

</body>

<script>
    // listener zoom control 
    //
        let currentActiveMarker = null;
        let currentActivePin = null;
        let zoomMarkerPivot = null; // This will hold the marker for zooming
        lastPinBtn.style.display = 'none';

        document.getElementById('zoomInBUTT').addEventListener('click', function () {
            map.zoomIn();
            
            if (zoomMarkerPivot) {
                setTimeout(() => {
                    centerMapWithSidebarOffset(zoomMarkerPivot.getLatLng());
                }, 250);
            }        
        });

        document.getElementById('zoomOutBUTT').addEventListener('click', function () {
            map.zoomOut();
            
            if (zoomMarkerPivot) {
                setTimeout(() => {
                    centerMapWithSidebarOffset(zoomMarkerPivot.getLatLng());
                }, 250);
            }
        });

        function updateLastPinButton(pinData) {
            const lastPinBtn = document.getElementById('lastPinBtn');
            // const pinIconPreview = document.getElementById('pinIconPreview');
            
                // if (pinData && pinData.iconUrl) {
                //     // pinIconPreview.src = pinData.iconUrl;
                //     
                // }
        }

        function reopenLastPin() {
            if (currentActivePin && currentActiveMarker) {
                showPinTab(
                    currentActivePin.label || 'Pin', 
                    currentActivePin.latitude, 
                    currentActivePin.longitude, 
                    currentActivePin
                );
                
                centerMapWithSidebarOffset(currentActiveMarker.getLatLng());
            }
            zoomMarkerPivot = currentActivePin
            zoomMarkerPivot = currentActiveMarker;

        }

        // Wire up the button
        document.getElementById('lastPinBtn').addEventListener('click', reopenLastPin);
        

    // Toast Message success, error, & status 
    // 
        @if (session('success'))
            // We defer this slightly to ensure the main script has loaded the showToast function
            document.addEventListener('DOMContentLoaded', function () {
                showToast('{{ session('success') }}', 3000); // Show success messages for 3 seconds
            });
        @endif

        @if (session('error'))
            document.addEventListener('DOMContentLoaded', function () {
                showToast('{{ session('error') }}', 6000); // Show errors for longer
            });
        @endif
        
        @if (session('status'))
            document.addEventListener('DOMContentLoaded', function () {
                showToast('{{ session('status') }}', 3000); // Show status messages for 3 seconds
            });
        @endif 
    // ===================================================

    // Listener for the toggle collapse button (pintab sidebar) 🔽
    //
        document.getElementById('togglePinDetails').addEventListener('click', function () {
            const details = document.getElementById('pinDetails');
            const isHidden = details.classList.contains('hidden');

            details.classList.toggle('hidden');
            this.textContent = isHidden ? '⏏️' : '⏹️';
        }); 
    // ===================================================

        // Listener for the Edit Pin button
        //
            document.getElementById("editPinBtn").addEventListener("click", () => {
                if (!currentPinId) return alert("No pin selected.");

                // Populate the edit form with the current pin's data
                document.getElementById("edit_label").value = document.getElementById("pinTitle").textContent;
                document.getElementById("edit_body").value = document.getElementById("pinBody").textContent;

                
                document.getElementById("edit_pin_icon").addEventListener("change", function (e) {
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
                });

                // Set the form's action to the correct update URL
                const editForm = document.getElementById("editPinForm");
                editForm.action = `/pins/${currentPinId}`; // The form's @method('PUT') will handle the rest

                // Show the edit modal
                document.getElementById("editPinModal").classList.remove("hidden");
            });

            // Listener to close the new edit modal
            document.getElementById("cancelEditPin").addEventListener("click", () => {
                document.getElementById("editPinModal").classList.add("hidden");
            });
        // ===============================================

        function centerMapWithSidebarOffset(latlng) {
            const mapWidth = map.getSize().x;
            const sidebarWidth = document.getElementById("pinTab").offsetWidth || 100; 

            const offsetX = sidebarWidth * (-0.5); // Adjust this value to center the map correctly

            const targetPoint = map.project(latlng, map.getZoom()).subtract([offsetX, 0]);
            const offsetLatLng = map.unproject(targetPoint, map.getZoom());

            map.setView(offsetLatLng, map.getZoom(), {
                animate: true,
                pan: { duration: 1}
            });
        }



    // fetch('/posts/json')


    // Map setup
    //
        window.authUserId = {{ auth()->id() ?? 'null' }};
        window.authUserRole = "{{ auth()->user()->role ?? 'guest' }}";

        const currentUserId = @json(auth()->id());

        // ✅ Define this before using it!
        const imgWidth = 1000, imgHeight = 1000;
        const imageBounds = [[0, 0], [imgHeight, imgWidth]];

        // ✅ Now it's safe to use imageBounds
        const map = L.map('map', {
            zoomControl: false,  // This removes the default +/- buttons
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

        

    // ===================================================
    
    //custom defined rendercomment
        function renderComment(c, postId) {
            const canDelete = c.user?.id === authUserId || ['admin', 'staff'].includes(authUserRole);

            const fullBody = c.body;
            const shortBody = fullBody.length > 150 ? fullBody.substring(0, 150) + '...' : fullBody;
            const isLong = fullBody.length > 150;

            const bodyHtml = isLong
                ? `<span class="comment-body" data-full="${escapeHtml(fullBody)}" data-short="${escapeHtml(shortBody)}">${escapeHtml(shortBody)}</span> <button class="toggle-comment text-blue-600 text-xs hover:underline">Show more</button>`
                : `<span class="comment-body">${escapeHtml(fullBody)}</span>`;

            return `
                <li>
                    <strong>${c.user?.name ?? 'Unknown'}</strong>: 
                    ${bodyHtml}
                    <small class="block text-gray-500">
                        (User ID: ${c.user?.id ?? 'N/A'}) • 
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
    // ===================================================

    // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    // ===================================================



    // Posts and comments handling
    //
        let currentPinId = null;
        function createPostElement(p){
            const div = document.createElement('div');
            div.className = "backdrop-blur-sm mb-3";

            // Build the comments list kalo ada
            //
                let commentsHTML = '';
                const commentsPerPage = 2;

                if (p.comments && p.comments.length > 0) {
                    const visibleComments = p.comments.slice(0, commentsPerPage);
                    const remainingComments = p.comments.slice(commentsPerPage);

                    commentsHTML += `
                        <h4 class="mt-3 font-semibold">Comments:</h4>
                        <ul class="ml-4 list-disc space-y-1" data-post-id="${p.id}" id="comments-list-${p.id}">
                            ${visibleComments.map(c => renderComment(c, p.id)).join('')}
                        </ul>
                    `;

                    if (remainingComments.length > 0) {
                        commentsHTML += `
                            <button type="button" class="text-sm text-blue-600 hover:underline mt-1" 
                                onclick="loadMoreComments(${p.id})" id="comment-toggle-${p.id}">
                                Show more comments
                            </button>
                        `;
                    }

                    // Save remaining comments for "Show more"
                    window._commentChunks = window._commentChunks || {};
                    window._commentChunks[p.id] = {
                        comments: remainingComments,
                        index: 0
                    };
                }
            // ===========================================

            // Comment form (shown to all logged-in users) ---
                commentsHTML += `
                    <form method="POST" action="/posts/${p.id}/comments" class="mt-2">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').getAttribute('content')}">
                        <textarea name="body" rows="2" class="w-full border p-1 rounded" required placeholder="Write a comment..."></textarea>
                        <button type="submit" class="mt-1 px-2 py-1 bg-blue-500 text-white text-xs rounded">Comment</button>
                    </form>
                `;
            // ===========================================

            // Main post structure (title, body, image, comment) ---
            //
                div.innerHTML = `
                    <strong>${p.title}</strong>
                    ${(() => {
                        const full = p.body || '';
                        const short = full.length > 300 ? full.substring(0, 300) + '...' : full;
                        const isLong = full.length > 300;

                        if (!isLong) {
                            return `<p>${full}</p>`;
                        }

                        return `
                            <p>
                                <span class="post-body" data-full="${full}" data-short="${short}">${short}</span>
                                <button class="toggle-post-body text-blue-600 text-xs hover:underline">Show more</button>
                            </p>
                        `;
                    })()}


                    ${p.image ? `<img src="/storage/${p.image}" class="mt-2 rounded max-h-40">` : ''}

                    <small class="block mt-1 text-gray-500">
                        Posted:
                        ${new Date(p.created_at).toLocaleString(undefined, {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        })}<br>
                        by: ${p.user?.name ?? 'Unknown'} (User ID: ${p.user?.id ?? 'N/A'})
                    </small>

                    ${p.is_owner ? `
                        <div class="flex gap-2 mt-2">
                            <a href="/posts/${p.id}/edit" class="px-2 py-1 text-xs text-white bg-yellow-400 rounded edit-post">Edit</a>
                            <form method="POST" action="/posts/${p.id}" onsubmit="return confirm('Are you sure?')" style="display:inline;">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').getAttribute('content')}">
                                <button type="submit" class="px-2 py-1 text-xs text-white bg-red-500 rounded delete-post">Delete</button>
                            </form>
                        </div>
                    ` : ''}

                    ${commentsHTML}
                `;
            // ===================================================
            // Enable "Show more/less" toggle for comment bodies
            div.querySelectorAll('.toggle-comment').forEach(btn => {
                const span = btn.previousElementSibling;
                if (!span || !span.dataset.full || !span.dataset.short) return;

                btn.addEventListener('click', () => {
                    const showingFull = span.textContent === span.dataset.full;
                    span.textContent = showingFull ? span.dataset.short : span.dataset.full;
                    btn.textContent = showingFull ? 'Show more' : 'Show less';
                });
            });


            return div;
        }
    // ===================================================

        function loadMoreComments(postId) {
            const chunk = window._commentChunks[postId];
            if (!chunk) return;

            const commentsList = document.getElementById(`comments-list-${postId}`);
            const slice = chunk.comments.slice(chunk.index, chunk.index + 2);

            slice.forEach(c => {
                commentsList.insertAdjacentHTML('beforeend', renderComment(c, postId));
            });

            chunk.index += 2;

            // Hide the "Show more" button if there are no more comments
            if (chunk.index >= chunk.comments.length) {
                const toggleBtn = document.getElementById(`comment-toggle-${postId}`);
                if (toggleBtn) toggleBtn.remove();
            }
        }


    // Listener for toggling post body visibility
    //
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('toggle-post-body')) {
                const span = e.target.previousElementSibling;
                const showingFull = span.textContent === span.dataset.full;

                span.textContent = showingFull ? span.dataset.short : span.dataset.full;
                e.target.textContent = showingFull ? 'Show more' : 'Show less';
            }
        });
    // ===================================================



    // Load posts for the pintab (sidebar)
    //
        function loadPostsForPin(latitude, longitude) 
        {
            function loadMoreComments(comments, container, start = 0, count = 4) {
                const end = start + count;
                const currentChunk = comments.slice(start, end);

                currentChunk.forEach(c => {
                    const li = document.createElement("li");
                    li.innerHTML = `...`; // your comment content here
                    container.appendChild(li);
                });

                if (end < comments.length) {
                    const btn = document.createElement("button");
                    btn.textContent = "Show more comments";
                    btn.className = "text-sm text-blue-600 hover:underline mt-1";
                    btn.onclick = () => {
                        btn.remove(); // remove old button
                        loadMoreComments(comments, container, end, count);
                    };
                    container.appendChild(btn);
                }
            }
            const div = document.createElement("div");

            const postsContainer = document.getElementById("pinPosts");
            postsContainer.innerHTML = "<p class='italic text-gray-500'>Loading posts...</p>";

            fetch(`/posts/near?lat=${latitude}&lng=${longitude}`)
                .then(res => res.json())
                .then(posts => {
                
                    postsContainer.innerHTML = '';

                    if (posts.length === 0) {
                        postsContainer.innerHTML = "<p class='italic text-gray-500'>No posts yet.</p>";
                        return;
                    }


                    // fold posts into the container
                    posts.slice(0, 2).forEach(post => {
                        postsContainer.appendChild(createPostElement(post));
                    });

                    // Handle extra posts (collapsible)
                    const hiddenPosts = posts.slice(2);
                    if (hiddenPosts.length > 0) {
                        const hiddenContainer = document.createElement("div");
                        hiddenContainer.classList.add("hidden");

                        hiddenPosts.forEach(post => {
                            hiddenContainer.appendChild(createPostElement(post));
                        });

                        postsContainer.appendChild(hiddenContainer);

                        const toggleBtn = document.createElement("button");
                        toggleBtn.className = "text-sm text-blue-600 hover:underline mt-2";
                        toggleBtn.textContent = "Show more posts";
                        toggleBtn.onclick = () => {
                            hiddenContainer.classList.toggle("hidden");
                            toggleBtn.textContent = hiddenContainer.classList.contains("hidden")
                                ? "Show more posts"
                                : "Hide posts";
                        };

                        postsContainer.appendChild(toggleBtn);
                    }

                })
                .catch(() => {
                    postsContainer.innerHTML = "<p class='text-red-500'>Failed to load posts.</p>";
                });
        }
    // ===================================================

    // Show pintab with details
    //
        function showPinTab(pinName, lat, lng, pinData = null) {
            const bannerImage = document.getElementById("pinBanner");
            const pinBody = document.getElementById("pinBody");
            const editBtn = document.getElementById("editPinBtn");
            const deleteBtn = document.getElementById("deletePinBtn");

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

                // 
                // Optional: Show the custom icon in sidebar
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
                // ... (rest of the logic for a new pin)
            }

            // The rest of the function remains the same...
            const latitude = Number(lat);
            const longitude = Number(lng);

            document.getElementById("globalCoords").innerHTML = `Y:<span class="math-inline">${latitude.toFixed(2)}</span> X:<span class="math-inline">${longitude.toFixed(2)}</span>`;
            document.getElementById('add-post-button').href = `/posts/create?lat=${latitude.toFixed(2)}&lng=${longitude.toFixed(2)}&pin_id=${currentPinId ?? ''}`;
            
            openPinTab(); 
            loadPostsForPin(latitude, longitude); 
        }
    // ===================================================

    // Toast function
    //
        function showToast(message, duration = 3000) 
        {
            const toast = document.getElementById('toast');
            document.getElementById('toastMessage').textContent = message;
            toast.classList.remove('opacity-0');
            toast.classList.add('opacity-100');
            setTimeout(() => {
                toast.classList.remove('opacity-100');
                toast.classList.add('opacity-0');
            }, duration);
        }
    // ===================================================

    // Handle menu toggle (dropdown)
    //
        const dropdownButton = document.getElementById("dropdownButton");
        const dropdownMenu = document.getElementById("dropdownMenu");

        // Open dropdown by default
        dropdownMenu.style.display = "block";
        dropdownButton.classList.remove("rounded-3xl");
        dropdownButton.classList.add("rounded-xl", "bg-white/90");

        dropdownButton.addEventListener("click", function (e) {
            e.stopPropagation();

            const isOpen = dropdownMenu.style.display === "block";

            dropdownMenu.style.display = isOpen ? "none" : "block";

            if (!isOpen) {
                dropdownButton.classList.remove("rounded-3xl");
                dropdownButton.classList.add("rounded-xl", "bg-white/90");
            } else {
                dropdownButton.classList.remove("rounded-xl", "bg-white/90");
                dropdownButton.classList.add("rounded-3xl");
            }
        });

        // document.addEventListener("click", function (e) {
        //     if (!e.target.closest("#dropdownButton") && !e.target.closest("#dropdownMenu")) {
        //         dropdownMenu.style.display = "none";

        //         dropdownButton.classList.remove("rounded-xl", "bg-white");
        //         dropdownButton.classList.add("rounded-3xl");
        //     }
        // });

        // Center map 
        document.getElementById("centerMapBtn").addEventListener("click", () => {
            map.fitBounds(imageBounds);
});



    // ===================================================


    // Load saved pins from DB
    //
        const allMarkers = []; // So we can access markers later

        function getIconForZoom(zoomLevel, baseSize = 40) {
            const scale = zoomLevel / 13; // 13 = base zoom level you use normally
            const size = Math.max(20, baseSize * scale); // Minimum icon size
            return [size, size];
        }

        fetch('/pins')
            .then(res => res.json())
            .then(pins => 
            {
                pins.forEach(pin => 
                {
                    // unmoderated pins are less visible
                    const markerOptions = {
                        opacity: pin.moderated ? 1.0 : 0.6,
                        icon: L.divIcon({
                            className: `custom-icon ${pin.moderated ? '' : 'grayscale-icon'}`,
                            html: `<img src="${pin.iconUrl || '/storage/icons/default.png'}" style="width: 40px; height: 40px;">`,
                            iconSize: [40, 40],
                            iconAnchor: [20, 40],
                            popupAnchor: [0, -35],
                        })


                    };

                    // populate the marker with the pin data
                    const marker = L.marker([pin.latitude, pin.longitude], markerOptions).addTo(map)
                        .bindTooltip(`Pin: ${pin.label || ''}`, { permanent: false, opacity: 0.8 });

                    // open sidebar when pin is clicked
                    marker.on("click", () => {
                        currentActiveMarker = marker; 
                        currentActivePin = pin; 
                        zoomMarkerPivot = marker; // Store the marker for zooming later

                        updateLastPinButton(pin);


                        showPinTab(pin.label || 'Pin', pin.latitude, pin.longitude, pin); 
                        centerMapWithSidebarOffset(marker.getLatLng());
                    });

                    // marker.on('click', function () {
                
                    // openPinTab(); 
                        
                    // });


                });
            })
            .catch(err => console.error("Failed to load pins:", err));

    // ===================================================

    // Ghost Marker Ketika Add pin 
        let ghostMarker = null; // This will hold our temporary marker

        // This function is called when the user moves the mouse on the map
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


    // ===================================================

    // Button Listeners
    //
        // document.getElementById("addPin").addEventListener("click", function(e) {
        //     e.preventDefault();
        //     enterAddPinMode();
        // });

        document.getElementById("cancelPin").addEventListener("click", function() {
            exitAddPinMode();
        });

        function closePinTab() {
            lastPinBtn.style.display = 'block';
            console.log("Closing pin tab");
            document.getElementById("pinTab").style.right = "-24rem";
            zoomMarkerPivot = null; // Reset the zoom marker pivot
        }
        
            // } else {
            //     

        function openPinTab() {
            lastPinBtn.style.display = 'none';
            console.log("Opening pin tab");
            document.getElementById("pinTab").style.right = "0";
        }

        // Bind close function to button click
        document.getElementById("closePinTab").addEventListener("click", closePinTab);
    // ===================================================

    // Add new pin (modal)
    //
            // Helper function to show the modal with the correct coordinates
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


        document.getElementById("addPin").addEventListener("click", function(e) {
            e.preventDefault();
            enterAddPinMode();
        });

        document.getElementById("cancelCreatePin").addEventListener("click", function () {
            document.getElementById("createPinModal").classList.add("hidden");
        });


        function enterAddPinMode() {
            document.getElementById('addingPinWrapper').classList.remove('hidden');
            document.getElementById('map').classList.add('map-add-pin-mode');
            
            addPin.classList.remove("rounded-3xl");
            addPin.classList.add("rounded-xl", "bg-white/90");

            // Show ghost marker on mouse move
            map.on('mousemove', onMapMouseMove);

            // Wait for the user to click on the map
            map.on('click', onMapClickToAddPin);
        }

        function exitAddPinMode() {
            document.getElementById('addingPinWrapper').classList.add('hidden');
            document.getElementById('map').classList.remove('map-add-pin-mode');
            
            addPin.classList.remove("rounded-xl", "bg-white/90");
            addPin.classList.add("rounded-3xl");

            map.off('mousemove', onMapMouseMove);
            map.off('click', onMapClickToAddPin);

            if (ghostMarker) {
                ghostMarker.remove();
                ghostMarker = null;
            }
        }

        function onMapClickToAddPin(e) {
            exitAddPinMode();
            showCreatePinModal(e.latlng.lat, e.latlng.lng);
        }

    // ===================================================

    // custom pin icon upload
    //
        // function openIconModal(pinId) {
        //     const form = document.getElementById('pinIconForm');
        //     form.action = `/pins/${pinId}/update-icon`; // dynamically update form action
        //     document.getElementById('iconModal').classList.remove('hidden');
        // }

        // function closeIconModal() {
        //     document.getElementById('iconModal').classList.add('hidden');
        // }
    //

    // Edit pin
    //
        document.getElementById("deletePinBtn").addEventListener("click", () => {
            if (!currentPinId) return alert("No pin selected.");

            if (!confirm("Are you sure you want to delete this pin?")) return;

            fetch(`/pins/${currentPinId}`, 
            {
                method: "DELETE",
                headers:    {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
            }).
            then(res => {
                if (res.ok) {
                showToast("Pin deleted.");
                closePinTab();
                location.reload(); // or remove marker from map
                } 
                else 
                {
                    showToast("Failed to delete pin.", 4000);
                }
            });
        });
    // ===================================================

</script>

</html>
@endsection