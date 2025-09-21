<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pin;
use App\Models\Post;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Storage;

class PinController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate the data from the form
        $validatedData = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'label' => 'required|string|max:255',
            'body' => 'nullable|string|max:1000', // Added max length for body
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Added mimes
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:512', // Added mimes and webp
        ]);

        // Initialize pin data
        $pinData = [
            'latitude' => $validatedData['latitude'],
            'longitude' => $validatedData['longitude'],
            'label' => $validatedData['label'],
            'body' => $validatedData['body'] ?? null, // Ensure null if not provided
            'user_id' => auth()->id(),
            // Default to unmoderated (false) for new user pins,
            // or true if created by admin/staff (as in your JS, let's keep consistent for now)
            'moderated' => (Auth::check() && (Auth::user()->role === 'admin' || Auth::user()->role === 'staff')) ? true : false,
        ];

        // 2. Handle banner upload
        if ($request->hasFile('banner')) {
            $pinData['banner'] = $request->file('banner')->store('banners', 'public');
        }

        // 3. Handle icon upload
        if ($request->hasFile('icon')) {
            $pinData['icon'] = $request->file('icon')->store('icons', 'public');
        }

        // 4. Create the pin in the database
        $pin = Pin::create($pinData); // Store the created pin instance

        // Prepare the response data, including full URLs for frontend
        return response()->json([
            'success' => true,
            'message' => 'Pin created successfully!',
            // Return the full pin object with accessible URLs for icons and banners
            'pin' => array_merge($pin->toArray(), [
                'banner_url' => $pin->banner ? Storage::url($pin->banner) : null,
                'icon_url' => $pin->icon ? Storage::url($pin->icon) : asset('storage/icons/default.png'),
            ]),
        ], 201); // 201 Created status code for successful creation
    }

    public function index()
    {
        $user = Auth::user();

        if ($user && ($user->isAdmin() || $user->isStaff())) {
            $pins = Pin::all();
        } elseif ($user) {
            $pins = Pin::where('moderated', true)
                        ->orWhere('user_id', $user->id)
                        ->get();
        } else {
            $pins = Pin::where('moderated', true)->get();
        }

        // 🔁 Transform pins to include iconUrl and banner_url
        $transformed = $pins->map(function ($pin) {
            return [
                'id' => $pin->id,
                'label' => $pin->label,
                'latitude' => $pin->latitude,
                'longitude' => $pin->longitude,
                'moderated' => $pin->moderated,
                'user_id' => $pin->user_id,
                'banner' => $pin->banner,
                'body' => $pin->body,
                'description' => $pin->body, // Add this for the tooltip description
                'iconUrl' => $pin->icon
                    ? asset('storage/' . $pin->icon)
                    : asset('storage/icons/default.png'),
                'banner_url' => $pin->banner
                    ? asset('storage/' . $pin->banner)
                    : null,
            ];
        });

        return response()->json($transformed);
    }

    public function update(Request $request, $id)
    {
        $pin = Pin::findOrFail($id);

        // PERMISSION: Only the owner of the pin or admin/staff can edit.
        $user = Auth::user();
        if ($user->id !== $pin->user_id && !($user->isAdmin() || $user->isStaff())) {
            return response()->json(['message' => 'You do not have permission to edit this pin.'], 403);
        }

        // Validate the incoming data
        $validatedData = $request->validate([
            'label' => 'required|string|max:255',
            'body' => 'nullable|string|max:1000', // Added max length
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:512', // Added mimes and webp
        ]);

        // Update pin attributes
        $pin->label = $validatedData['label'];
        $pin->body = $validatedData['body'] ?? null;

        // Handle banner upload
        if ($request->hasFile('banner')) {
            if ($pin->banner) { // Delete old banner if new one is provided
                Storage::disk('public')->delete($pin->banner);
            }
            $pin->banner = $request->file('banner')->store('banners', 'public');
        }
        // If a new banner is not provided, but we want to remove the existing one (e.g., via a "clear" checkbox)
        // You'd need an input like <input type="hidden" name="clear_banner" value="1"> in your form for this logic
        // For now, if no file is provided, existing banner remains.

        // Handle icon upload
        if ($request->hasFile('icon')) {
            if ($pin->icon) { // Delete old icon if new one is provided
                Storage::disk('public')->delete($pin->icon);
            }
            $pin->icon = $request->file('icon')->store('icons', 'public');
        }
        // Similar logic for clearing icon if not provided but exists

        // Save the updated pin
        $pin->save();

        // Prepare the response data, including full URLs for frontend
        return response()->json([
            'success' => true,
            'message' => 'Pin updated successfully!',
            'pin' => array_merge($pin->toArray(), [
                'banner_url' => $pin->banner ? Storage::url($pin->banner) : null,
                'icon_url' => $pin->icon ? Storage::url($pin->icon) : asset('storage/icons/default.png'),
            ]),
        ], 200); // 200 OK status code for successful update
    }

    public function destroy($id)
    {
        $pin = Pin::findOrFail($id);

        // PERMISSION (This was already correct): Owner, Admin, or Staff can delete.
        if (Auth::id() !== $pin->user_id && !Auth::user()->isAdmin() && !Auth::user()->isStaff()) {
            abort(403, 'You do not have permission to delete this pin.');
        }

        $pin->delete();

        return response()->json(['success' => true, 'message' => 'Pin deleted.']);
    }

    public function approve($id)
    {
        $pin = Pin::findOrFail($id);
        
        // Only admin and staff can approve pins
        if (!Auth::user()->isAdmin() && !Auth::user()->isStaff()) {
            abort(403, 'You do not have permission to approve pins.');
        }
        
        // Update the moderated status to true
        $pin->update(['moderated' => true]);
        
        return response()->json([
            'success' => true, 
            'message' => 'Pin approved successfully.',
            'pin' => $pin
        ]);
    }

    public function revoke($id)
    {
        $pin = Pin::findOrFail($id);
        
        // Only admin and staff can revoke pins
        if (!Auth::user()->isAdmin() && !Auth::user()->isStaff()) {
            abort(403, 'You do not have permission to revoke pin approval.');
        }
        
        // Update the moderated status to false
        $pin->update(['moderated' => false]);
        
        return response()->json([
            'success' => true, 
            'message' => 'Pin approval revoked successfully.',
            'pin' => $pin
        ]);
    }


    public function updatePosition(Request $request, $id)
    {
        $pin = Pin::findOrFail($id);

        // Allow only owner, admin, or staff
        $user = Auth::user();
        $isAuthorized = $user->id === $pin->user_id || in_array($user->role, ['admin', 'staff']);
        if (!$isAuthorized) {
            abort(403, 'You do not have permission to update this pin\'s position.');
        }

        // Validate new coordinates
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Update the pin
        $pin->latitude = $validated['latitude'];
        $pin->longitude = $validated['longitude'];
        $pin->save();

        // OPTIONAL: Update all related posts with this pin_id
        Post::where('pin_id', $pin->id)->update([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return response()->json([
            'message' => 'Pin position updated successfully.',
            'pin' => $pin,
        ], 200); // <-- Add explicit 200 status

    }


}