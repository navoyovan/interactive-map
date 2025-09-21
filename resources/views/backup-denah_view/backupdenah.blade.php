@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Interactive Map</title>

    <!-- Leaflet -->
    <link rel="stylesheet" href="/offlined/leaflet.css" />
    <script src="/offlined/leaflet.js"></script>

    <!-- Tailwind -->
    <script src="/offlined/tailwind.js"></script>

    <style>
        html, body { margin: 0; padding: 0; height: 100%; }
        #map { position: absolute; width: 100vw; height: 100vh; z-index: 1; }
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
            overflow-y: auto;
        }
    </style>
</head>

<body>
<!-- Menu / Add Pin Controls -->
<div class="absolute top-4 right-4 z-50 flex items-center space-x-4">
    <div id="addingPinWrapper" class="hidden text-sm text-gray-800">
        🧷 Click on the map to place a pin...
        <button id="cancelPin" class="ml-2 text-red-500 hover:underline">Cancel</button>
    </div>
    <div class="relative">
        <button id="dropdownButton" class="bg-white px-4 py-2 rounded shadow-md">Menu ▼</button>
        <div id="dropdownMenu" class="mt-2 bg-white rounded shadow-md z-50 hidden">
            <a href="{{ route('login') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-200">Log In</a>
            <a href="#" id="addPin" class="block px-4 py-2 text-gray-700 hover:bg-gray-200">Add Pin</a>
        </div>
    </div>
</div>

<!-- Pin Info Sidebar -->
<div id="pinTab">
    <div class="flex justify-between items-center bg-gray-800 text-white px-3 py-2">
        <span id="pinTitle">Pin Name</span>
        <button id="closePinTab" class="bg-black-600 px-2 rounded">❌</button>
    </div>
    <div class="p-4">
        <a id="add-post-button" href="#">
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded text-sm">Add Post</button>
        </a>
        <div class="mt-4 flex gap-2">
            <button id="renamePinBtn" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">Rename</button>
            <button id="deletePinBtn" class="bg-red-500 text-white px-3 py-1 rounded text-sm">Delete</button>
        </div>
        <div class="bg-gray-200 px-3 py-1 mt-4 text-sm">
            <span id="globalCoords">X:# Y:# note: koordinat untuk developer</span>
        </div>
        <hr class="my-2">
        <div id="pinPosts" class="space-y-2 text-sm text-gray-700">
            <p class="text-gray-500 italic">Loading posts...</p>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-6 right-6 bg-gray-800 text-white px-4 py-2 rounded shadow-lg opacity-0 transition-opacity duration-300 z-[9999]">
    <span id="toastMessage">This is a toast</span>
</div>

<!-- Map Container -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<div id="map"></div>

<script>
    const map = L.map('map', { crs: L.CRS.Simple, minZoom: -2, maxZoom: 2 });
    const imgWidth = 1080, imgHeight = 1000;
    const imageBounds = [[0, 0], [imgHeight, imgWidth]];
    L.imageOverlay('/img/map_skb.png', imageBounds).addTo(map);
    map.fitBounds(imageBounds);
    map.setMaxBounds(imageBounds);

    let currentPinId = null;

    function showToast(msg, duration = 3000) {
        const toast = document.getElementById('toast');
        document.getElementById('toastMessage').textContent = msg;
        toast.classList.replace('opacity-0', 'opacity-100');
        setTimeout(() => toast.classList.replace('opacity-100', 'opacity-0'), duration);
    }

    function closePinTab() {
        document.getElementById("pinTab").style.right = "-24rem";
    }

    function openPinTab() {
        document.getElementById("pinTab").style.right = "0";
    }

    function showPinTab(name, lat, lng, id = null) {
        currentPinId = id;
        const label = name?.trim() || `Pin at ${lat.toFixed(1)}, ${lng.toFixed(1)}`;
        document.getElementById("pinTitle").textContent = label;
        document.getElementById("globalCoords").textContent = `X:${lng.toFixed(2)} Y:${lat.toFixed(2)}`;
        document.getElementById("add-post-button").href = `/posts/create?lat=${lat}&lng=${lng}`;
        openPinTab();

        const postContainer = document.getElementById("pinPosts");
        postContainer.innerHTML = `<p class="text-gray-500 italic">Loading posts...</p>`;

        fetch(`/posts/near?lat=${lat}&lng=${lng}`)
            .then(res => res.json())
            .then(posts => {
                postContainer.innerHTML = '';
                if (posts.length === 0) {
                    postContainer.innerHTML = `<p class="text-gray-500 italic">No posts yet.</p>`;
                } else {
                    posts.forEach(p => {
                        const div = document.createElement('div');
                        div.className = "border p-2 rounded bg-gray-100";
                        div.innerHTML = `
                            <strong>${p.title}</strong>
                            <p>${p.body || ''}</p>
                            ${p.image ? `<img src="/storage/${p.image}" class="mt-2 max-h-40 rounded">` : ''}
                            <small class="text-gray-500">Posted: ${new Date(p.created_at).toLocaleString()}</small>
                            <div class="mt-2 flex gap-2">
                                <button class="edit-post bg-yellow-400 px-2 py-1 text-xs rounded text-white" data-id="${p.id}">Edit</button>
                                <button class="delete-post bg-red-500 px-2 py-1 text-xs rounded text-white" data-id="${p.id}">Delete</button>
                            </div>`;
                        postContainer.appendChild(div);
                    });
                }
            }).catch(() => {
                postContainer.innerHTML = `<p class="text-red-500">Failed to load posts.</p>`;
            });
    }

    // Toggle dropdown
    document.getElementById("dropdownButton").onclick = e => {
        e.stopPropagation();
        const menu = document.getElementById("dropdownMenu");
        menu.style.display = menu.style.display === "block" ? "none" : "block";
    };

    document.addEventListener("click", e => {
        if (!e.target.closest("#dropdownButton") && !e.target.closest("#dropdownMenu")) {
            document.getElementById("dropdownMenu").style.display = "none";
        }
    });

    document.getElementById("closePinTab").onclick = closePinTab;

    // Load Pins
    fetch('/pins').then(res => res.json()).then(pins => {
        pins.forEach(pin => {
            const m = L.marker([pin.latitude, pin.longitude]).addTo(map)
                .bindTooltip(`Pin: ${pin.label || ''}`, { permanent: false });
            m.on("click", () => showPinTab(pin.label, pin.latitude, pin.longitude, pin.id));
        });
    });

    // Add Pin
    document.getElementById("addPin").onclick = () => {
        const msg = document.getElementById("addingPinWrapper");
        msg.classList.remove("hidden");
        document.getElementById("dropdownMenu").classList.add("hidden");

        function addPinEvent(e) {
            const { lat, lng } = e.latlng;
            const m = L.marker([lat, lng]).addTo(map).bindPopup(`New Pin at ${lat}, ${lng}`);
            m.on("click", () => showPinTab("New Pin", lat, lng));

            fetch('/pins', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ latitude: lat, longitude: lng, label: 'Custom Pin' })
            }).then(() => showToast("Pin added successfully!"))
              .catch(() => showToast("Error saving pin!", 5000));

            msg.classList.add("hidden");
            map.off("click", addPinEvent);
        }

        map.once("click", addPinEvent);

        document.getElementById("cancelPin").addEventListener("click", function cancelListener() {
            msg.classList.add("hidden");
            map.off("click", addPinEvent);
            this.removeEventListener("click", cancelListener);
        }, { once: true });
    };

    // Rename / Delete
    document.getElementById("renamePinBtn").onclick = () => {
        if (!currentPinId) return alert("No pin selected.");
        const newName = prompt("New pin name:");
        if (!newName) return;

        fetch(`/pins/${currentPinId}`, {
            method: "PUT",
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ label: newName })
        }).then(res => {
            if (res.ok) {
                showToast("Pin renamed.");
                document.getElementById("pinTitle").textContent = newName;
            } else showToast("Failed to rename pin.", 4000);
        });
    };

    document.getElementById("deletePinBtn").onclick = () => {
        if (!currentPinId || !confirm("Delete this pin?")) return;
        fetch(`/pins/${currentPinId}`, {
            method: "DELETE",
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        }).then(res => {
            if (res.ok) {
                showToast("Pin deleted.");
                closePinTab();
                location.reload();
            } else showToast("Failed to delete pin.", 4000);
        });
    };

    // Edit/Delete Posts
    document.getElementById("pinPosts").addEventListener("click", e => {
        const btn = e.target;
        const id = btn.dataset.id;

        if (btn.classList.contains("delete-post")) {
            if (!confirm("Delete this post?")) return;
            fetch(`/posts/${id}`, {
                method: "DELETE",
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }).then(res => {
                if (res.ok) {
                    showToast("Post deleted.");
                    btn.closest(".border").remove();
                } else showToast("Failed to delete post.", 4000);
            });
        }

        if (btn.classList.contains("edit-post")) {
            const title = prompt("New title:");
            const body = prompt("New body:");
            if (!title) return;

            fetch(`/posts/${id}`, {
                method: "PUT",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ title, body })
            }).then(res => {
                if (res.ok) showToast("Post updated.");
                else showToast("Failed to update post.", 4000);
            });
        }
    });
</script>
</body>
</html>
@endsection
