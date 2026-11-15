<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Event;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user, Event $event): bool
    {
        return $user->can('view', $event);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id
            || $user->id === $comment->event->user_id;
    }
}
