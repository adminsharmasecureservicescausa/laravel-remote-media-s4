<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('media.index') : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Media Library Gallery
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::get('/dashboard', function () {
        return redirect()->route('media.index');
    })->name('dashboard');
    
    // Secure Video Player View
    Route::get('/media/{media:slug}', [MediaController::class, 'show'])->name('media.show');

    // Secure Streaming Token Endpoint with Rate Limiting (60 requests/minute per IP)
    Route::get('/media/{media:slug}/stream', [MediaController::class, 'stream'])
        ->middleware('throttle:60,1')
        ->name('media.stream');

    // User Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
