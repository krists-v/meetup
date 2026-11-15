<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AvatarController extends Controller
{
    public function show(User $user): StreamedResponse
    {
        abort_unless(
            filled($user->avatar_path) && Storage::disk('avatars')->exists($user->avatar_path),
            404,
        );

        return Storage::disk('avatars')->response($user->avatar_path, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
