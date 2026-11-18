<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('create', [Comment::class, $event]);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $body = trim($validated['body']);

        if ($body === '') {
            return back()
                ->withErrors(['body' => 'Write a note first.'])
                ->withInput();
        }

        $event->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $body,
        ]);

        return back()->with('status', 'Note added.');
    }

    public function destroy(Event $event, Comment $comment): RedirectResponse
    {
        abort_unless($comment->event_id === $event->id, 404);

        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('status', 'Note removed.');
    }
}
