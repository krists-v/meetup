<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventPhotoController extends Controller
{
    public function show(Request $request, Event $event): StreamedResponse
    {
        abort_unless(
            filled($event->photo_path) && Storage::disk('photos')->exists($event->photo_path),
            404,
        );

        abort_unless($this->canSee($request, $event), 404);

        $shared = $event->visibility === 'public';

        return Storage::disk('photos')->response($event->photo_path, null, [
            'Cache-Control' => $shared ? 'public, max-age=86400' : 'private, no-store',
        ]);
    }

    private function canSee(Request $request, Event $event): bool
    {
        if ($event->visibility === 'public') {
            return true;
        }

        $token = $request->query('token');

        if ($event->matchesInviteToken(is_string($token) ? $token : null)) {
            return true;
        }

        $user = $request->user();

        if ($user === null) {
            return false;
        }

        return $event->user_id === $user->id
            || $event->participants()->where('users.id', $user->id)->exists();
    }
}
