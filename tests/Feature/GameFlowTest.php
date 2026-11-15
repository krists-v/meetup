<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Notifications\PromotedFromWaitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event as EventFacade;
use Tests\TestCase;

class GameFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_game_offers_a_fifo_waitlist_and_promotes_with_notice_and_email(): void
    {
        EventFacade::fake([MessageSent::class]);

        $event = Event::factory()->create([
            'visibility' => 'public',
            'title' => 'Thursday five-a-side',
            'sport' => 'football',
            'sport_type' => 'football',
            'max_participants' => 1,
            'event_date' => '2026-12-10 18:00:00',
        ]);

        $going = User::factory()->create();
        $first = User::factory()->create(['name' => 'First Waiting']);
        $second = User::factory()->create(['name' => 'Second Waiting']);

        $this->actingAs($going)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertSessionHasNoErrors();

        Carbon::setTestNow('2026-11-15 10:00:00');
        $this->actingAs($first)
            ->post(route('events.rsvp', $event), ['status' => 'waitlist'])
            ->assertSessionHasNoErrors();

        Carbon::setTestNow('2026-11-15 11:00:00');
        $this->actingAs($second)
            ->post(route('events.rsvp', $event), ['status' => 'waitlist'])
            ->assertSessionHasNoErrors();
        Carbon::setTestNow();

        $this->assertSame('waitlist', $event->fresh()->getUserStatus($first));
        $this->assertSame(1, $event->fresh()->waitlistPosition($first));
        $this->assertSame(2, $event->fresh()->waitlistPosition($second));

        $this->actingAs($second)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Joined', 'Thursday five-a-side', 'Open near you']);

        $this->actingAs($first)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Join waitlist')
            ->assertSee('You are 1 on the waitlist.')
            ->assertSeeInOrder(['1.', 'First Waiting', '2.', 'Second Waiting']);

        $this->actingAs($going)
            ->post(route('events.rsvp', $event), ['status' => 'not_going'])
            ->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame('going', $event->getUserStatus($first));
        $this->assertSame('waitlist', $event->getUserStatus($second));
        $this->assertSame(1, $event->waitlistPosition($second));
        $this->assertSame(1, $event->goingParticipants()->count());

        EventFacade::assertDispatched(MessageSent::class, function (MessageSent $message) {
            return $message->message->getSubject() === 'A spot opened on Thursday five-a-side';
        });

        $this->assertDatabaseHas('notifications', [
            'type' => PromotedFromWaitlist::class,
            'notifiable_id' => $first->id,
            'notifiable_type' => $first->getMorphClass(),
            'read_at' => null,
        ]);
        $this->assertSame(0, $second->notifications()->count());

        $first->unsetRelations();

        $this->actingAs($first)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('A spot opened. You are going to Thursday five-a-side.')
            ->assertSee('Thursday five-a-side');

        $this->actingAs($first)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertDontSee('A spot opened. You are going to Thursday five-a-side.');

        $this->assertNotNull($first->notifications()->first()->read_at);

        $this->actingAs($first)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('A spot opened. You are going to Thursday five-a-side.');
    }

    public function test_raising_the_limit_promotes_the_waitlist_and_lowering_it_does_not(): void
    {
        EventFacade::fake([MessageSent::class]);

        $creator = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $creator->id,
            'visibility' => 'public',
            'title' => 'Court game',
            'sport' => 'tennis',
            'sport_type' => 'tennis',
            'max_participants' => 2,
            'event_date' => '2026-12-11 18:00:00',
            'location' => 'City court',
        ]);

        $going = User::factory()->create();
        $alsoGoing = User::factory()->create();
        $waiting = User::factory()->create();
        $later = User::factory()->create();

        $this->actingAs($going)->post(route('events.rsvp', $event), ['status' => 'going']);
        $this->actingAs($alsoGoing)->post(route('events.rsvp', $event), ['status' => 'going']);
        $this->actingAs($waiting)->post(route('events.rsvp', $event), ['status' => 'waitlist']);
        $this->actingAs($later)->post(route('events.rsvp', $event), ['status' => 'waitlist']);

        $this->actingAs($creator)
            ->put(route('events.update', $event), $this->payload($event, 3))
            ->assertRedirect(route('events.show', $event));

        $this->assertSame('going', $event->fresh()->getUserStatus($waiting));
        $this->assertSame('waitlist', $event->fresh()->getUserStatus($later));

        $this->actingAs($creator)
            ->put(route('events.update', $event), $this->payload($event->fresh(), 2))
            ->assertRedirect(route('events.show', $event));

        $this->assertSame(2, $event->fresh()->max_participants);
        $this->assertSame('going', $event->fresh()->getUserStatus($going));
        $this->assertSame('going', $event->fresh()->getUserStatus($waiting));
        $this->assertSame('waitlist', $event->fresh()->getUserStatus($later));
        EventFacade::assertDispatchedTimes(MessageSent::class, 1);

        $this->actingAs($creator)
            ->put(route('events.update', $event), $this->payload($event->fresh(), ''))
            ->assertRedirect(route('events.show', $event));

        $this->assertNull($event->fresh()->max_participants);
        $this->assertSame('going', $event->fresh()->getUserStatus($later));
        EventFacade::assertDispatchedTimes(MessageSent::class, 2);
    }

    public function test_promotion_email_uses_the_log_mailer_when_that_is_configured(): void
    {
        config(['mail.default' => 'log']);
        EventFacade::fake([MessageSent::class]);

        $event = Event::factory()->create([
            'visibility' => 'public',
            'max_participants' => 1,
            'title' => 'Logged game',
        ]);
        $going = User::factory()->create();
        $waiting = User::factory()->create();

        $this->actingAs($going)->post(route('events.rsvp', $event), ['status' => 'going']);
        $this->actingAs($waiting)->post(route('events.rsvp', $event), ['status' => 'waitlist']);
        $this->actingAs($going)->post(route('events.rsvp', $event), ['status' => 'maybe']);

        EventFacade::assertDispatched(MessageSent::class);
        $this->assertSame(1, $waiting->notifications()->count());
        $this->assertSame('going', $event->fresh()->getUserStatus($waiting));
    }

    public function test_joining_the_waitlist_again_keeps_the_original_place(): void
    {
        $event = Event::factory()->create([
            'visibility' => 'public',
            'max_participants' => 1,
            'event_date' => '2026-12-10 18:00:00',
        ]);
        $going = User::factory()->create();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($going)->post(route('events.rsvp', $event), ['status' => 'going']);

        Carbon::setTestNow('2026-11-15 10:00:00');
        $this->actingAs($first)->post(route('events.rsvp', $event), ['status' => 'waitlist']);
        Carbon::setTestNow('2026-11-15 11:00:00');
        $this->actingAs($second)->post(route('events.rsvp', $event), ['status' => 'waitlist']);

        $joinedAt = $event->participants()->where('users.id', $first->id)->first()->pivot->created_at;

        Carbon::setTestNow('2026-11-15 12:00:00');
        $this->actingAs($first)
            ->post(route('events.rsvp', $event), ['status' => 'waitlist'])
            ->assertSessionHasNoErrors();
        Carbon::setTestNow();

        $event->refresh();
        $this->assertTrue($joinedAt->equalTo(
            $event->participants()->where('users.id', $first->id)->first()->pivot->created_at
        ));
        $this->assertSame(1, $event->waitlistPosition($first));
        $this->assertSame(2, $event->waitlistPosition($second));

        $this->actingAs($first)->post(route('events.rsvp', $event), ['status' => 'maybe']);
        Carbon::setTestNow('2026-11-15 13:00:00');
        $this->actingAs($first)->post(route('events.rsvp', $event), ['status' => 'waitlist']);
        Carbon::setTestNow();

        $event->refresh();
        $this->assertSame(1, $event->waitlistPosition($second));
        $this->assertSame(2, $event->waitlistPosition($first));
    }

    public function test_a_free_spot_treats_a_waitlist_request_as_going(): void
    {
        $event = Event::factory()->create([
            'visibility' => 'public',
            'max_participants' => 4,
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('events.rsvp', $event), ['status' => 'waitlist'])
            ->assertSessionHasNoErrors();

        $this->assertSame('going', $event->fresh()->getUserStatus($user));
    }

    public function test_calendar_download_and_google_link_use_the_app_timezone(): void
    {
        $this->assertSame('Europe/Riga', config('app.timezone'));

        $event = Event::factory()->create([
            'visibility' => 'public',
            'title' => 'Floorball',
            'sport' => 'other',
            'sport_type' => 'other',
            'custom_sport' => 'Floorball',
            'location' => 'Park pitch',
            'event_date' => '2026-12-10 18:00:00',
            'schedule_type' => 'fixed',
        ]);

        $ics = $this->get(route('events.calendar', $event));
        $ics->assertOk();
        $ics->assertHeader('content-type', 'text/calendar; charset=utf-8');
        $body = $ics->getContent();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $body);
        $this->assertStringContainsString('TZID:Europe/Riga', $body);
        $this->assertStringContainsString('DTSTART;TZID=Europe/Riga:20261210T180000', $body);
        $this->assertStringContainsString('DTEND;TZID=Europe/Riga:20261210T200000', $body);
        $this->assertStringContainsString('SUMMARY:Floorball', $body);

        $google = 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => 'Floorball',
            'dates' => '20261210T180000/20261210T200000',
            'ctz' => 'Europe/Riga',
            'location' => 'Park pitch',
            'details' => 'Floorball on Varza',
        ]);

        $this->actingAs($event->creator)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Download .ics', false)
            ->assertSee(e($google), false);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Download .ics', false)
            ->assertSee('Google Calendar', false);

        $previousTimezone = date_default_timezone_get();
        config(['app.timezone' => 'UTC']);
        date_default_timezone_set('UTC');

        try {
            $utcEvent = Event::factory()->create([
                'visibility' => 'public',
                'title' => 'UTC game',
                'location' => 'Hall',
                'event_date' => '2026-12-10 16:00:00',
                'schedule_type' => 'fixed',
            ]);
            $utcBody = $this->get(route('events.calendar', $utcEvent))->assertOk()->getContent();
            $this->assertStringContainsString('DTSTART:20261210T160000Z', $utcBody);
            $this->assertStringContainsString('DTEND:20261210T180000Z', $utcBody);
            $this->assertStringNotContainsString('BEGIN:VTIMEZONE', $utcBody);
        } finally {
            config(['app.timezone' => 'Europe/Riga']);
            date_default_timezone_set($previousTimezone);
        }
    }

    public function test_calendar_stays_hidden_for_polls_and_private_games_follow_the_invite(): void
    {
        $poll = Event::factory()->create([
            'visibility' => 'public',
            'schedule_type' => 'poll',
        ]);

        $this->get(route('events.calendar', $poll))->assertNotFound();

        $creator = User::factory()->create();
        $player = User::factory()->create();
        $stranger = User::factory()->create();
        $private = Event::factory()->private()->create([
            'user_id' => $creator->id,
            'schedule_type' => 'fixed',
            'event_date' => '2026-12-12 19:00:00',
        ]);
        $private->participants()->attach($player->id, ['status' => 'going']);

        $this->get(route('events.calendar', $private))->assertNotFound();
        $this->get(route('events.calendar', ['event' => $private, 'token' => $private->invite_token]))
            ->assertOk();
        $this->get(route('events.invite', $private->invite_token))
            ->assertOk()
            ->assertSee('Download .ics', false)
            ->assertSee('Google Calendar', false);

        $this->actingAs($stranger)->get(route('events.calendar', $private))->assertForbidden();
        $this->actingAs($player)->get(route('events.calendar', $private))->assertOk();
    }

    public function test_creator_can_repeat_a_fixed_game_next_week(): void
    {
        $creator = User::factory()->create();
        $friend = User::factory()->create();
        $event = Event::factory()->private()->create([
            'user_id' => $creator->id,
            'title' => 'Thursday five-a-side',
            'sport' => 'football',
            'sport_type' => 'football',
            'event_date' => '2026-12-10 18:00:00',
            'location' => 'Park pitch',
            'max_participants' => 8,
            'description' => 'Bring a light shirt',
            'schedule_type' => 'fixed',
        ]);
        $event->participants()->attach($friend->id, ['status' => 'going']);

        $this->actingAs($friend)
            ->post(route('events.repeat', $event))
            ->assertForbidden();

        $this->actingAs($creator)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Same time next week');

        $response = $this->actingAs($creator)->post(route('events.repeat', $event));

        $copy = Event::query()->whereKeyNot($event->id)->first();
        $this->assertNotNull($copy);
        $response->assertRedirect(route('events.edit', $copy));

        $this->assertTrue($copy->event_date->equalTo($event->event_date->copy()->addDays(7)));
        $this->assertNotSame($event->invite_token, $copy->invite_token);
        $this->assertSame(32, strlen((string) $copy->invite_token));
        $this->assertSame('Thursday five-a-side', $copy->title);
        $this->assertSame('football', $copy->sport);
        $this->assertSame('Park pitch', $copy->location);
        $this->assertSame(8, $copy->max_participants);
        $this->assertSame('private', $copy->visibility);
        $this->assertSame('Bring a light shirt', $copy->description);
        $this->assertSame($creator->id, $copy->user_id);
        $this->assertSame(1, $copy->participants()->count());
        $this->assertSame('going', $copy->getUserStatus($creator));
        $this->assertNull($copy->getUserStatus($friend));
        $this->assertSame('going', $event->fresh()->getUserStatus($friend));

        $this->actingAs($creator)
            ->get(route('events.edit', $copy))
            ->assertOk()
            ->assertSee('Thursday five-a-side', false)
            ->assertSee('Park pitch', false)
            ->assertSee('2026-12-17T18:00', false);
    }

    public function test_a_poll_cannot_be_repeated(): void
    {
        $creator = User::factory()->create();
        $poll = Event::factory()->create([
            'user_id' => $creator->id,
            'schedule_type' => 'poll',
            'visibility' => 'public',
        ]);

        $this->actingAs($creator)
            ->get(route('events.show', $poll))
            ->assertOk()
            ->assertDontSee('Same time next week');

        $this->actingAs($creator)
            ->post(route('events.repeat', $poll))
            ->assertForbidden();

        $this->assertSame(1, Event::count());
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Event $event, int|string $limit): array
    {
        return [
            'title' => $event->title,
            'sport' => $event->sport ?: $event->sport_type,
            'event_date' => $event->event_date->format('Y-m-d\TH:i'),
            'location' => $event->location,
            'max_participants' => $limit,
            'description' => (string) $event->description,
            'visibility' => $event->visibility,
        ];
    }
}
