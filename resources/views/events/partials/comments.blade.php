<x-ui.section title="Notes">
    <x-ui.card class="space-y-4 p-4 sm:p-6">
        @if ($event->comments->isEmpty())
            <p class="text-sm text-ink-muted">No notes yet. Say if you're bringing a ball.</p>
        @else
            <ul class="space-y-4">
                @foreach ($event->comments as $comment)
                    <li class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex min-w-0 items-center gap-2">
                                <x-ui.avatar :name="$comment->user->name" :user="$comment->user" class="h-7 w-7 text-[11px]" />
                                <span class="truncate text-sm font-medium text-ink">{{ $comment->user->name }}</span>
                            </span>
                            <span class="shrink-0 text-xs text-ink-muted">{{ $comment->created_at->timezone(config('app.timezone'))->format('D j M, H:i') }}</span>
                        </div>
                        <p class="whitespace-pre-line text-sm text-ink">{{ $comment->body }}</p>
                        @if (auth()->id() === $comment->user_id || $event->isCreator(auth()->user()))
                            <form method="POST" action="{{ route('events.comments.destroy', [$event, $comment]) }}">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="ghost" class="px-2">Delete</x-ui.button>
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
            <x-ui.button type="submit" variant="secondary" class="w-full sm:w-auto">Add a note</x-ui.button>
        </form>
    </x-ui.card>
</x-ui.section>
