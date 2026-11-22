<?php

use App\Http\Controllers\AvatarController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventPhotoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RosterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventController::class, 'home'])->name('home');

Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::view('/terms', 'legal.terms')->name('terms');

Route::get('/games', [EventController::class, 'index'])->name('games.index');

Route::get('/avatars/{user}', [AvatarController::class, 'show'])->name('avatars.show');

Route::get('/games/{event}/photo', [EventPhotoController::class, 'show'])->name('events.photo');

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
    Route::post('/events/{event}/roster/{user}/confirm', [RosterController::class, 'confirm'])
        ->middleware('throttle:writes')
        ->name('events.roster.confirm');
    Route::post('/events/{event}/roster/{user}/decline', [RosterController::class, 'decline'])
        ->middleware('throttle:writes')
        ->name('events.roster.decline');
    Route::delete('/events/{event}/roster/{user}', [RosterController::class, 'remove'])
        ->middleware('throttle:writes')
        ->name('events.roster.remove');
    Route::post('/events/{event}/roster/{user}/attendance', [RosterController::class, 'attendance'])
        ->middleware('throttle:writes')
        ->name('events.roster.attendance');
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
    Route::post('/events/{event}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:comments')
        ->name('events.comments.store');
    Route::delete('/events/{event}/comments/{comment}', [CommentController::class, 'destroy'])
        ->middleware('throttle:writes')
        ->name('events.comments.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

require __DIR__.'/auth.php';
