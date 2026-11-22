<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Event;
use App\Support\AvatarStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'email']));
        $favorites = array_values($request->validated('favorite_sports', []));
        $user->favorite_sports = $favorites;

        if ($request->exists('sport_levels')) {
            $incoming = $request->validated('sport_levels') ?? [];
            $levels = [];

            foreach ($favorites as $sport) {
                $level = $incoming[$sport] ?? null;

                if (is_string($level) && array_key_exists($level, Event::LEVELS)) {
                    $levels[$sport] = $level;
                }
            }

            $user->sport_levels = $levels === [] ? null : $levels;
        }

        if ($request->hasFile('avatar')) {
            $user->avatar_path = app(AvatarStore::class)->store($request->file('avatar'), $user);
        } elseif ($request->boolean('remove_avatar')) {
            $user->deleteAvatarFile();
            $user->avatar_path = null;
        }

        if ($request->exists('game_reminders')) {
            $user->game_reminders = $request->boolean('game_reminders');
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $user->deleteAvatarFile();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
