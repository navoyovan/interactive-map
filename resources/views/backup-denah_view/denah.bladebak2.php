@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Interactive Map</title>

    <link rel="stylesheet" href="/offlined/leaflet.css" />
    <script src="/offlined/leaflet.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body { margin: 0; padding: 0; height: 100%; }
        #map { position: absolute; width: 100vw; height: 100vh; z-index: 1; background-color: #64605F; }
        #pinTab {
            position: fixed; top: 0; right: -24rem; width: 24rem; height: 100vh;
            background: white; box-shadow: -2px 0 5px rgba(0,0,0,0.2);
            transition: right 0.3s ease-in-out; z-index: 2000;
        }
    </style>
</head>

<body>
    <div class="relative min-h-screen">
        <div id="map" class="w-full h-50vh"></div>

        <div class="absolute z-50 flex items-center space-x-4 top-20 left-10">
            <div id="addingPinWrapper" class="hidden p-2 text-sm text-gray-800 bg-white rounded shadow">
                Click on the map to place a pin...
                <button id="cancelPin" class="ml-2 text-red-500 hover:underline">Cancel</button>
            </div>

            <div class="relative">
                <button id="dropdownButton" class="px-4 py-2 bg-white rounded shadow-md">▼</button>
                <div id="dropdownMenu" class="absolute z-[1500] hidden mt-2 bg-white rounded shadow-md">
                    <a href="#" id="addPinButton" class="block px-4 py-2 text-gray-700 hover:bg-gray-200">Add Pin</a>
                </div>
            </div>
        </div>

        <div id="pinTab" class="fixed top-0 right-0 z-50 flex flex-col h-full bg-white shadow-lg w-96">
            <div class="flex items-center justify-between h-16 px-3 py-2 text-white bg-gray-800">
                <span id="pinTitle" class="font-bold">Pin Details</span>
                <button id="closePinTab" class="px-2 rounded hover:bg-gray-700">❌</button>
            </div>

            <div class="p-4 border-b">
                <div id="pinDetailsContainer" class="mb-4 text-sm text-gray-600 space-y-1">
                    <p>Select a pin to see details.</p>
                </div>
                <a id="add-post-button" href="#" class="hidden w-full px-4 py-2 text-sm font-semibold text-center text-white bg-blue-600 rounded hover:bg-blue-700">Add Post to this Pin</a>
                <div id="pinControls" class="flex gap-2 mt-4" style="display: none;">
                    <button id="renamePinBtn" class="px-3 py-1 text-sm text-white bg-yellow-500 rounded">Rename</button>
                    <button id="deletePinBtn" class="px-3 py-1 text-sm text-white bg-red-500 rounded">Delete</button>
                </div>
            </div>

            <div id="pinPosts" class="flex-1 p-4 space-y-3 overflow-y-auto">
                <p class="italic text-gray-500">Select a pin to load posts.</p>
            </div>
        </div>
    </div>

    <div id="createPinModal" class="fixed inset-0 z-[3000] hidden bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-[400px]">
            <h2 class="mb-4 text-lg font-bold">Create New Pin</h2>
            <form id="createPinForm" method="POST" action="{{ route('pins.store') }}">
                @csrf
                <input type="hidden" name="latitude" id="formLatitude">
                <input type="hidden" name="longitude" id="formLongitude">
                <div class="mb-4">
                    <label for="label" class="block mb-1">Pin Label</label>
                    <input type="text" id="label" name="label" class="w-full border rounded p-2" required>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" id="cancelCreatePin" class="text-gray-600">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-white bg-black rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="fixed bottom-6 right-6 bg-gray-800 text-white px-4 py-2 rounded shadow-lg opacity-0 transition-opacity duration-300 z-[9999]">
        <span id="toastMessage"></span>
    </div>

</body>
</html>


<script>
// --- CONFIGURATION ---
window.App = {
    user: @json(Auth::user() ? ['id' => Auth::id(), 'isAdmin' => Auth::user()->isAdmin(), 'isStaff' => Auth::user()->isStaff()] : null),
    csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    routes: {
        pins_index: '{{ route("pins.index") }}',
        pins_update_template: '{{ route("pins.update", ["pin" => "PIN_ID"]) }}',
        pins_destroy_template: '{{ route("pins.destroy", ["pin" => "PIN_ID"]) }}',
        posts_near: '{{ route("posts.near") }}',
        posts_create: '#',
        posts_destroy_template: '{{ route("posts.destroy", ["post" => "POST_ID"]) }}',
        posts_edit_template: '{{ route("posts.edit", ["post" => "POST_ID"]) }}',
        comments_store_template: '{{ route("comments.store", ["post" => "POST_ID"]) }}',
        comments_destroy_template: '{{ route("comments.destroy", ["comment" => "COMMENT_ID"]) }}',
    }
};

document.addEventListener('DOMContentLoaded', function () {

    const map = L.map('map', { crs: L.CRS.Simple, minZoom: -2, maxZoom: 2 });
    const imageBounds = [[0, 0], [1000, 1080]];
    L.imageOverlay('/img/map_skb.png', imageBounds).addTo(map);
    map.fitBounds(imageBounds);
    map.setMaxBounds(imageBounds);

    const pinTab = document.getElementById('pinTab');
    const pinTitle = document.getElementById('pinTitle');
    const pinDetailsContainer = document.getElementById('pinDetailsContainer');
    const pinControls = document.getElementById('pinControls');
    const pinPostsContainer = document.getElementById('pinPosts');
    const addPostButton = document.getElementById('add-post-button');
    const createPinModal = document.getElementById('createPinModal');
    const createPinForm = document.getElementById('createPinForm');

    let currentPin = null;
    const markers = {};

    const showToast = (message, duration = 3000) => {
        const toast = document.getElementById('toast');
        document.getElementById('toastMessage').textContent = message;
        toast.classList.replace('opacity-0', 'opacity-100');
        setTimeout(() => toast.classList.replace('opacity-100', 'opacity-0'), duration);
    };

    const openPinTab = () => pinTab.style.right = '0';
    const closePinTab = () => {
        pinTab.style.right = '-24rem';
        currentPin = null;
    };

    const showCreatePinModal = (lat, lng) => {
        createPinForm.reset();
        document.getElementById('formLatitude').value = lat;
        document.getElementById('formLongitude').value = lng;
        createPinModal.classList.remove('hidden');
    };
    const hideCreatePinModal = () => createPinModal.classList.add('hidden');

    const fetchAndDisplayPins = async () => {
        try {
            const response = await fetch(App.routes.pins_index);
            const pins = await response.json();
            Object.values(markers).forEach(marker => marker.remove());
            pins.forEach(pin => addMarkerToMap(pin));
        } catch (error) {
            console.error("Failed to load pins:", error);
            showToast("Error: Could not load pins.", 5000);
        }
    };

    const addMarkerToMap = (pin) => {
        const marker = L.marker([pin.latitude, pin.longitude])
            .addTo(map)
            .bindTooltip(`Pin: ${pin.label || ''}`, { permanent: false, opacity: 0.8 });

        marker.on("click", () => {
            currentPin = pin;
            updatePinTabUI(pin);
            openPinTab();
        });
        markers[pin.id] = marker;
    };

    const updatePinTabUI = (pin) => {
        pinTitle.textContent = pin.label || `Unnamed Pin`;
        pinDetailsContainer.innerHTML = `
            <p><strong>ID:</strong> ${pin.id}</p>
            <p><strong>Coords:</strong> ${pin.latitude.toFixed(4)}, ${pin.longitude.toFixed(4)}</p>
            <p><strong>Description:</strong> ${pin.body || 'N/A'}</p>
            <p><strong>Created:</strong> ${new Date(pin.created_at).toLocaleDateString()}</p>
        `;
        const canModifyPin = App.user && (pin.user_id === App.user.id || App.user.isAdmin || App.user.isStaff);
        pinControls.style.display = canModifyPin ? 'flex' : 'none';
        addPostButton.style.display = App.user ? 'block' : 'none';
        const createPostUrl = new URL(App.routes.posts_create, window.location.origin);
        createPostUrl.searchParams.set('pin_id', pin.id);
        addPostButton.href = createPostUrl.toString();
        loadPostsForPin(pin);
    };

    const loadPostsForPin = async (pin) => {
        pinPostsContainer.innerHTML = `<p class="italic text-gray-500">Loading posts...</p>`;
        try {
            const response = await fetch(`${App.routes.posts_near}?lat=${pin.latitude}&lng=${pin.longitude}`);
            const posts = await response.json();
            pinPostsContainer.innerHTML = '';
            if (posts.length === 0) {
                pinPostsContainer.innerHTML = `<p class="italic text-gray-500">No posts yet for this pin.</p>`;
                return;
            }
            posts.forEach(post => pinPostsContainer.appendChild(renderPostElement(post)));
        } catch (error) {
            console.error("Failed to load posts:", error);
            pinPostsContainer.innerHTML = `<p class="text-red-500">Failed to load posts.</p>`;
        }
    };

    const renderPostElement = (post) => {
        const div = document.createElement('div');
        div.className = "p-3 bg-gray-100 border rounded shadow-sm";
        div.dataset.postId = post.id;
        const canModifyPost = App.user && (post.user.id === App.user.id || App.user.isAdmin || App.user.isStaff);
        const editUrl = App.routes.posts_edit_template.replace('POST_ID', post.id);
        const deleteUrl = App.routes.posts_destroy_template.replace('POST_ID', post.id);
        let commentsHtml = '<div class="mt-3 pt-3 border-t">';
        if (post.comments && post.comments.length > 0) {
            commentsHtml += '<h5 class="mb-2 text-sm font-semibold">Comments</h5><ul class="pl-4 space-y-2">';
            post.comments.forEach(comment => {
                const canDeleteComment = App.user && (comment.user.id === App.user.id || App.user.isAdmin || App.user.isStaff);
                const commentDeleteUrl = App.routes.comments_destroy_template.replace('COMMENT_ID', comment.id);
                commentsHtml += `<li class="text-xs"><p>${comment.body}</p><small class="text-gray-500"><strong>${comment.user.name || 'Unknown'}</strong> ${canDeleteComment ? `<button data-action="delete-comment" data-url="${commentDeleteUrl}" class="ml-2 font-semibold text-red-500 hover:underline">Delete</button>` : ''}</small></li>`;
            });
            commentsHtml += '</ul>';
        }
        commentsHtml += '</div>';
        const commentFormUrl = App.routes.comments_store_template.replace('POST_ID', post.id);
        const commentFormHtml = App.user ? `<form data-action="add-comment" action="${commentFormUrl}" method="POST" class="mt-3"><textarea name="body" rows="2" class="w-full p-1 text-sm border rounded" placeholder="Add a comment..." required></textarea><button type="submit" class="px-2 py-1 mt-1 text-xs text-white bg-blue-500 rounded">Submit</button></form>` : '';
        div.innerHTML = `${post.image ? `<img src="/storage/${post.image}" class="mb-2 rounded max-h-40">` : ''}<h4 class="font-bold">${post.title}</h4><p class="text-sm">${post.body || ''}</p><small class="block mt-1 text-xs text-gray-500">By: ${post.user.name || 'Unknown'}</small>${canModifyPost ? `<div class="flex gap-2 mt-2"><a href="${editUrl}" class="px-2 py-1 text-xs text-white bg-yellow-500 rounded">Edit Post</a><button data-action="delete-post" data-url="${deleteUrl}" class="px-2 py-1 text-xs text-white bg-red-500 rounded">Delete Post</button></div>` : ''}${commentsHtml}${commentFormHtml}`;
        return div;
    };

    // --- EVENT LISTENERS ---
    document.getElementById('dropdownButton').addEventListener('click', (e) => {
        e.stopPropagation();
        document.getElementById('dropdownMenu').classList.toggle('hidden');
    });
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#dropdownButton')) {
            document.getElementById('dropdownMenu').classList.add('hidden');
        }
    });

    document.getElementById('addPinButton').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('addingPinWrapper').classList.remove('hidden');
        document.getElementById('dropdownMenu').classList.add('hidden');
        map.once('click', (e) => {
            showCreatePinModal(e.latlng.lat, e.latlng.lng);
            document.getElementById('addingPinWrapper').classList.add('hidden');
        });
    });

    document.getElementById('cancelPin').addEventListener('click', () => {
        map.off('click');
        document.getElementById('addingPinWrapper').classList.add('hidden');
    });

    createPinForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(createPinForm);
        try {
            const response = await fetch(createPinForm.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': App.csrfToken, 'Accept': 'application/json' },
                body: formData
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Failed to create pin.');
            await fetchAndDisplayPins();
            hideCreatePinModal();
            showToast('Pin created successfully!');
        } catch (error) {
            showToast(error.message, 5000);
        }
    });
    document.getElementById('cancelCreatePin').addEventListener('click', hideCreatePinModal);

    document.getElementById('closePinTab').addEventListener('click', closePinTab);

    document.getElementById('deletePinBtn').addEventListener('click', async () => {
        if (!currentPin || !confirm('Are you sure you want to delete this pin?')) return;
        const deleteUrl = App.routes.pins_destroy_template.replace('PIN_ID', currentPin.id);
        try {
            const response = await fetch(deleteUrl, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': App.csrfToken, 'Accept': 'application/json' }});
            if (!response.ok) throw new Error('Failed to delete pin.');
            markers[currentPin.id]?.remove();
            delete markers[currentPin.id];
            closePinTab();
            showToast('Pin deleted.');
        } catch (error) {
            showToast(error.message, 5000);
        }
    });

    document.getElementById('renamePinBtn').addEventListener('click', async () => {
        if (!currentPin) return;
        const newLabel = prompt('Enter new name for the pin:', currentPin.label);
        if (!newLabel || newLabel.trim() === '') return;
        const updateUrl = App.routes.pins_update_template.replace('PIN_ID', currentPin.id);
        try {
            const response = await fetch(updateUrl, {
                method: 'PUT',
                headers: { 'X-CSRF-TOKEN': App.csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ label: newLabel })
            });
            if (!response.ok) throw new Error('Failed to rename pin.');
            currentPin.label = newLabel;
            markers[currentPin.id]?.bindTooltip(`Pin: ${newLabel}`);
            pinTitle.textContent = newLabel;
            showToast('Pin renamed.');
        } catch (error) {
            showToast(error.message, 5000);
        }
    });

    pinPostsContainer.addEventListener('click', async (e) => {
        const target = e.target;
        const action = target.dataset.action;
        if (action === 'delete-post') {
            if (!confirm('Are you sure?')) return;
            const response = await fetch(target.dataset.url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': App.csrfToken, 'Accept': 'application/json' }});
            if (response.ok) { target.closest('[data-post-id]').remove(); showToast('Post deleted.'); } 
            else { showToast('Failed to delete post.', 4000); }
        }
        if (action === 'delete-comment') {
            if (!confirm('Are you sure?')) return;
            const response = await fetch(target.dataset.url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': App.csrfToken, 'Accept': 'application/json' }});
            if (response.ok) { target.closest('li').remove(); showToast('Comment deleted.'); }
             else { showToast('Failed to delete comment.', 4000); }
        }
    });
    
    pinPostsContainer.addEventListener('submit', async (e) => {
        if (e.target.dataset.action === 'add-comment') {
            e.preventDefault();
            const form = e.target;
            const response = await fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': App.csrfToken, 'Accept': 'application/json' }, body: new FormData(form) });
            if (response.ok) { loadPostsForPin(currentPin); showToast('Comment added.'); }
            else { showToast('Failed to post comment.', 4000); }
        }
    });

    fetchAndDisplayPins();
});
</script>
@endsection