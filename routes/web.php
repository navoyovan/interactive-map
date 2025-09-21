<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileImageController;
use App\Http\Controllers\LikeController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
// routes/web.php
use App\Http\Controllers\GeminiController;

Route::get('/ask-gemini', [GeminiController::class, 'generateText']);
// You can also add a route that accepts a 'prompt' parameter
// Route::get('/ask-gemini/{prompt}', [GeminiController::class, 'generateText']);

// Homepage
// Route::get('/', function () { return view('denah'); })->name('home');
Route::get('/', function () {
    return view('denah', ['pin_id' => request('pin_id')]);
})->name('home');


//==========================================================================
// PIN ROUTES
//==========================================================================
Route::get('/pins', [PinController::class, 'index'])->name('pins.index');

Route::middleware('auth')->group(function () {
    Route::post('/pins', [PinController::class, 'store'])->name('pins.store');
    Route::put('/pins/{pin}', [PinController::class, 'update'])->name('pins.update');
    Route::delete('/pins/{pin}', [PinController::class, 'destroy'])->name('pins.destroy');
    Route::put('/pins/{id}/update-position', [PinController::class, 'updatePosition']);
    Route::patch('/pins/{pin}/approve', [PinController::class, 'approve'])->name('pins.approve'); 
    Route::patch('/pins/{pin}/revoke', [PinController::class, 'revoke'])->name('pins.revoke'); 


});


//==========================================================================
// POST ROUTES 
//==========================================================================

// --- Specific routes FIRST ---
Route::get('/posts/near', [PostController::class, 'nearby'])->name('posts.near'); //unused
Route::get('/posts/by-pin', [PostController::class, 'byPin'])->name('posts.byPin');
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

// --- Specific routes that require login ---
Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create')->middleware('auth');
Route::post('/posts', [PostController::class, 'store'])->name('posts.store')->middleware('auth');

// --- Wildcard routes LAST ---
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit')->middleware('auth');
Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update')->middleware('auth');
Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy')->middleware('auth');
Route::get('/posts/json', [PostController::class, 'indexJson'])->name('posts.json');



//==========================================================================
// OTHER AUTHENTICATED ROUTES
//==========================================================================
Route::middleware('auth')->group(function () {
    // Dashboard & Profile
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/image', [ProfileImageController::class, 'update'])->name('profile.image');

    // Comments
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Likes
    Route::post('/posts/{post}/like', [LikeController::class, 'toggle'])->name('posts.like.toggle');


    // Moderation actions
    Route::patch('/posts/{post}/approve', [PostController::class, 'approve'])->name('posts.approve');
    Route::patch('/posts/{post}/toggle-moderation', [PostController::class, 'toggleModeration'])->name('posts.toggleModeration');
    Route::patch('/posts/{post}/toggle-moderation', [PostController::class, 'toggleModeration'])->middleware('auth');

    
    Route::get('/api/dashboard/posts', [DashboardController::class, 'jsonPosts'])->middleware('auth');
});



require __DIR__.'/auth.php';