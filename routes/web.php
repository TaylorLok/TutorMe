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



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //video chat
    Route::get('/video-calls', [VideoCallController::class, 'index'])->name('video-calls.index');
    Route::get('/video-calls/create', [VideoCallController::class, 'create'])->name('video-calls.create');
    Route::post('/video-calls', [VideoCallController::class, 'store'])->name('video-calls.store');
    Route::get('/video-calls/{videoCall}', [VideoCallController::class, 'show'])->name('video-calls.show');
    Route::patch('/video-calls/{videoCall}/status', [VideoCallController::class, 'updateStatus'])->name('video-calls.update-status');
});

require __DIR__.'/auth.php';
