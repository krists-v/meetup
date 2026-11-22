@php
    $prefix = $prefix ?? 'player';
    $open = $open ?? false;
@endphp

@if (auth()->id() !== $player->id)
    <div class="w-full space-y-2 text-left">
        @unless ($open)
            <details>
                <summary class="flex min-h-11 cursor-pointer items-center text-sm font-medium text-ink">Report</summary>
        @endunless
        <form method="POST" action="{{ route('players.report', $player) }}" class="space-y-2">
            @csrf
            <x-ui.label for="{{ $prefix }}-reason-{{ $player->id }}" value="Reason" />
            <x-ui.select id="{{ $prefix }}-reason-{{ $player->id }}" name="reason" required>
                <option value="" disabled @selected(old('reason') === null || old('reason') === '')>Pick one</option>
                @foreach (\App\Models\Report::REASONS as $value => $label)
                    <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.error :messages="$errors->get('reason')" />
            <x-ui.label for="{{ $prefix }}-note-{{ $player->id }}" value="Note" />
            <x-ui.textarea id="{{ $prefix }}-note-{{ $player->id }}" name="note" rows="2" maxlength="1000">{{ old('note') }}</x-ui.textarea>
            <x-ui.error :messages="$errors->get('note')" />
            <x-ui.error :messages="$errors->get('report')" />
            <x-ui.button type="submit" :variant="$open ? 'primary' : 'secondary'" block>Report</x-ui.button>
        </form>
        @unless ($open)
            </details>
        @endunless

        @if (auth()->user()->hasBlocked($player))
            <form method="POST" action="{{ route('players.unblock', $player) }}">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="secondary" block>Unblock</x-ui.button>
            </form>
        @else
            <form method="POST" action="{{ route('players.block', $player) }}">
                @csrf
                <x-ui.button type="submit" variant="ghost" block>Block</x-ui.button>
            </form>
        @endif
    </div>
@endif
