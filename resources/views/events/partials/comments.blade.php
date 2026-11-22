<x-ui.section title="Notes">
    <x-ui.card class="space-y-4">
        @php
            $notes = $event->comments->reject(function ($comment) {
                return $comment->user !== null && auth()->user()->hasBlocked($comment->user);
            });
        @endphp
        @if ($notes->isEmpty())
            <p class="text-base text-ink-muted">No notes yet. Say if you're bringing a ball.</p>
        @else
            <ul class="space-y-4">
                @foreach ($notes as $comment)
                    <li class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex min-w-0 items-center gap-2">
                                <x-ui.avatar :name="$comment->user->name" :user="$comment->user" size="meta" />
                                <a href="{{ route('players.show', $comment->user) }}" class="truncate text-base font-medium text-ink">{{ $comment->user->name }}</a>
                            </span>
                            <span class="shrink-0 text-base text-ink-muted">{{ $comment->created_at->timezone(config('app.timezone'))->format('D j M, H:i') }}</span>
                        </div>
                        <p class="whitespace-pre-line text-base text-ink">{{ $comment->body }}</p>
                        @include('players.partials.actions', ['player' => $comment->user, 'prefix' => 'note-'.$comment->id])
                        @if (auth()->id() === $comment->user_id || $event->isCreator(auth()->user()))
                            <form method="POST" action="{{ route('events.comments.destroy', [$event, $comment]) }}">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="ghost" block>Remove</x-ui.button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('events.comments.store', $event) }}" class="space-y-2">
            @csrf
            <x-ui.label for="comment-body" value="Note" />
            <x-ui.textarea id="comment-body" name="body" rows="3" maxlength="1000" required placeholder="Who is bringing a ball?">{{ old('body') }}</x-ui.textarea>
            <x-ui.error :messages="$errors->get('body')" />
            <x-ui.button type="submit" variant="secondary" block>Add a note</x-ui.button>
        </form>
    </x-ui.card>
</x-ui.section>
