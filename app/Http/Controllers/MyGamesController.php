<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyGamesController extends Controller
{
    /**
     * Statuses that mean you joined, asked to join, or said you can't make it.
     *
     * @var list<string>
     */
    private const PLAYING = ['going', 'maybe', 'not_going', 'waitlist', 'requested', 'invited'];

    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'playing' ? 'playing' : 'hosting';
        $when = $request->query('when') === 'past' ? 'past' : 'upcoming';
        $user = $request->user();

        $events = Event::query()
            ->with([
                'creator',
                'participants' => function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                },
            ])
            ->withCount(['goingParticipants', 'timeOptions'])
            ->when($tab === 'hosting', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($tab === 'playing', function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id)
                    ->whereHas('participants', function ($query) use ($user) {
                        $query->where('users.id', $user->id)
                            ->whereIn('event_user.status', self::PLAYING);
                    });
            })
            ->when($when === 'past', function ($query) {
                $query->where('event_date', '<', now())->orderByDesc('event_date');
            })
            ->when($when === 'upcoming', function ($query) {
                $query->where('event_date', '>=', now())->orderBy('event_date');
            })
            ->get();

        return view('my-games.index', [
            'events' => $events,
            'tab' => $tab,
            'when' => $when,
        ]);
    }
}
