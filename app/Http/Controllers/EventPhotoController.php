<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventPhotoController extends Controller
{
    public function show(Event $event): StreamedResponse
    {
        abort_unless(
            filled($event->photo_path) && Storage::disk('photos')->exists($event->photo_path),
            404,
        );

        return Storage::disk('photos')->response($event->photo_path, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
