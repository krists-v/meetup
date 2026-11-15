<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::view('/terms', 'legal.terms')->name('terms');

Route::get('/games', [EventController::class, 'index'])->name('games.index');

Route::post('/events/{event}/join', [EventController::class, 'join'])
    ->middleware('throttle:writes')
    ->name('events.join');

Route::get('/events/{event}/calendar.ics', [EventController::class, 'calendar'])
    ->name('events.calendar');

Route::get('/invite/{token}', [EventController::class, 'invite'])
    ->middleware('throttle:writes')
    ->name('events.invite');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [EventController::class, 'dashboard'])->name('dashboard');

    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])
        ->middleware('throttle:events')
        ->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])
        ->middleware('throttle:writes')
        ->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])
        ->middleware('throttle:writes')
        ->name('events.destroy');
    Route::post('/events/{event}/rsvp', [EventController::class, 'rsvp'])
        ->middleware('throttle:writes')
        ->name('events.rsvp');
    Route::post('/events/{event}/repeat', [EventController::class, 'repeat'])
        ->middleware('throttle:writes')
        ->name('events.repeat');
    Route::post('/events/{event}/options/{option}/vote', [EventController::class, 'vote'])
        ->middleware('throttle:writes')
        ->name('events.options.vote');
    Route::post('/events/{event}/options/{option}/lock', [EventController::class, 'lock'])
        ->middleware('throttle:writes')
        ->name('events.options.lock');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

require __DIR__.'/auth.php';
