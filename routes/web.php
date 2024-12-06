<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VideoCallController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//VIDEO CONTROLLER
Route::get('/video-call', [VideoCallController::class, 'index'])->name('video-call');
Route::post('/video-call/token', [VideoCallController::class, 'generateToken']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/sessions/{session}', [SessionController::class, 'show'])->name('sessions.show');
});

require __DIR__.'/auth.php';
