<x-legal-page
    title="How it works"
    description="Get off the couch, find a game near you, and play with someone new."
    subtitle="From the couch to a game near you."
    :contact="false"
>
    <div class="flex gap-3">
        <x-ui.icon name="home" class="mt-0.5 text-ink-faint" />
        <div>
            <h2 class="text-lg font-bold text-ink">Get off the couch</h2>
            <p class="mt-1">Varza is for a rainy Saturday when you are bored at home. See what is on nearby, go play, and meet someone new.</p>
        </div>
    </div>

    <div class="flex gap-3">
        <x-ui.icon name="map-pin" class="mt-0.5 text-ink-faint" />
        <div>
            <h2 class="text-lg font-bold text-ink">Find a game</h2>
            <p class="mt-1">Open what's on near you. Pick an activity, a day, or a place. A public game is there for anyone. A private game opens with the link the host sends.</p>
        </div>
    </div>

    <div class="flex gap-3">
        <x-ui.icon name="check" class="mt-0.5 text-ink-faint" />
        <div>
            <h2 class="text-lg font-bold text-ink">Tap I'm in</h2>
            <p class="mt-1">On an open game, I'm in puts you on the list. If it is full, you can join the waitlist. If the host is taking requests, they confirm you before the spot is yours.</p>
        </div>
    </div>

    <div class="flex gap-3">
        <x-ui.icon name="clock" class="mt-0.5 text-ink-faint" />
        <div>
            <h2 class="text-lg font-bold text-ink">Before you play</h2>
            <p class="mt-1">You get a reminder before the game. Leave a note if you are bringing a ball, or if you will be a few minutes late.</p>
        </div>
    </div>

    <div class="flex gap-3">
        <x-ui.icon name="plus" class="mt-0.5 text-ink-faint" />
        <div>
            <h2 class="text-lg font-bold text-ink">Host a game</h2>
            <p class="mt-1">Add a photo so people recognise the place. If you are splitting a cost, put the total and each person sees their share. Set the level, so a beginner knows they fit. The roster is where you confirm who is in.</p>
        </div>
    </div>

    <div class="flex gap-3">
        <x-ui.icon name="bar-chart" class="mt-0.5 text-ink-faint" />
        <div>
            <h2 class="text-lg font-bold text-ink">Show up</h2>
            <p class="mt-1">Your show-up score is how often you actually played. If someone ruins a game, report them. Block them if you do not want to share a game again.</p>
        </div>
    </div>

    <div class="flex gap-3">
        <x-ui.icon name="help" class="mt-0.5 text-ink-faint" />
        <div class="space-y-2">
            <h2 class="text-lg font-bold text-ink">A few questions</h2>
            <p>Is it free? Yes. Varza does not charge you to find a game or to host one.</p>
            <p>Who do I pay? If a game has a cost, you pay the host. Varza never takes that money.</p>
            <p>What if plans change? Say you can't make it. If you are hosting, cancel the game so people can make another plan.</p>
        </div>
    </div>

    <x-ui.button :href="route('games.index')" block>Find a game</x-ui.button>
</x-legal-page>
