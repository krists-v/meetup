<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CardRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_price_is_the_total_split_by_the_spots(): void
    {
        $event = Event::factory()->make([
            'price' => 10,
            'max_participants' => 4,
        ]);
        $this->assertSame('€2.50 / person', $event->priceLabel());

        $event->max_participants = 3;
        $this->assertSame('€3.33 / person', $event->priceLabel());

        $event->price = 0;
        $this->assertSame('Free', $event->priceLabel());

        $event->price = null;
        $this->assertNull($event->priceLabel());

        $event->price = 20;
        $event->max_participants = null;
        $this->assertSame('€20 total', $event->priceLabel());
    }

    public function test_a_game_without_a_photo_uses_the_sport_picture(): void
    {
        $event = Event::factory()->create([
            'sport' => 'padel',
            'photo_path' => null,
        ]);

        $this->assertStringContainsString('/images/sports/padel.svg', $event->photoUrl());
        $this->assertFileExists(public_path('images/sports/padel.svg'));
        $this->assertFileExists(public_path('images/sports/generic.svg'));

        $event->sport = 'not-a-sport';
        $this->assertSame('other', $event->defaultPhotoSlug());

        $backgrounds = [];

        foreach (Event::SPORTS as $slug => $label) {
            $svg = file_get_contents(public_path('images/sports/'.$slug.'.svg'));
            $this->assertIsString($svg);
            $this->assertStringNotContainsString('<text', $svg);
            $this->assertStringNotContainsString('>'.$label.'<', $svg);
            $this->assertStringNotContainsString('#f4f4f5', $svg);
            preg_match('/<rect width="800" height="480" fill="(#[0-9A-Fa-f]{6})"/', $svg, $fill);
            $this->assertArrayHasKey(1, $fill);
            $backgrounds[] = strtolower($fill[1]);
        }

        $this->assertSame($backgrounds, array_values(array_unique($backgrounds)));

        $generic = file_get_contents(public_path('images/sports/generic.svg'));
        $this->assertIsString($generic);
        $this->assertStringNotContainsString('<text', $generic);
        $this->assertStringNotContainsString('>Game<', $generic);
    }

    public function test_a_host_can_add_a_photo_and_a_cost_and_a_bad_file_is_refused(): void
    {
        Storage::fake('photos');
        $host = User::factory()->create();

        $this->actingAs($host)
            ->post(route('events.store'), [
                'title' => 'Court night',
                'sport' => 'tennis',
                'event_date' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'location' => 'Riga, Mezaparks Tennis Courts',
                'visibility' => 'public',
                'max_participants' => 4,
                'price' => 20,
                'photo' => UploadedFile::fake()->create('notes.txt', 20, 'text/plain'),
            ])
            ->assertSessionHasErrors('photo');

        $this->actingAs($host)
            ->post(route('events.store'), [
                'title' => 'Court night',
                'sport' => 'tennis',
                'event_date' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'location' => 'Riga, Mezaparks Tennis Courts',
                'visibility' => 'public',
                'max_participants' => 4,
                'price' => -1,
            ])
            ->assertSessionHasErrors('price');

        $this->actingAs($host)
            ->post(route('events.store'), [
                'title' => 'Court night',
                'sport' => 'tennis',
                'event_date' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'location' => 'Riga, Mezaparks Tennis Courts',
                'visibility' => 'public',
                'max_participants' => 4,
                'price' => 20,
                'photo' => UploadedFile::fake()->image('court.jpg', 40, 20),
            ])
            ->assertSessionHasNoErrors();

        $event = Event::query()->where('title', 'Court night')->firstOrFail();
        $this->assertNotNull($event->photo_path);
        $this->assertSame('€5 / person', $event->priceLabel());
        $this->assertSame('Mezaparks Tennis Courts', $event->areaLabel());
        Storage::disk('photos')->assertExists($event->photo_path);

        $this->get(route('events.photo', $event))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=86400, public');

        $this->actingAs($host)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('€5 / person')
            ->assertSee('Mezaparks Tennis Courts')
            ->assertSee(route('events.photo', $event), false);
    }

    public function test_a_private_game_photo_is_only_for_people_who_can_see_the_game(): void
    {
        Storage::fake('photos');
        $host = User::factory()->create();
        $event = Event::factory()->private()->create([
            'user_id' => $host->id,
            'photo_path' => 'secret.jpg',
        ]);
        Storage::disk('photos')->put('secret.jpg', 'photo');

        $this->get(route('events.photo', $event))->assertNotFound();

        $this->actingAs(User::factory()->create())
            ->get(route('events.photo', $event))
            ->assertNotFound();

        $player = User::factory()->create();
        $event->participants()->attach($player->id, ['status' => 'going']);

        $this->actingAs($player)
            ->get(route('events.photo', $event))
            ->assertOk();

        $this->actingAs($host)
            ->get(route('events.photo', $event))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->assertStringContainsString(
            'token='.urlencode($event->invite_token),
            $event->photoUrl($event->invite_token),
        );
    }

    public function test_an_invite_link_can_open_a_private_game_photo(): void
    {
        Storage::fake('photos');
        $event = Event::factory()->private()->create([
            'photo_path' => 'secret.jpg',
        ]);
        Storage::disk('photos')->put('secret.jpg', 'photo');

        $this->get(route('events.photo', ['event' => $event, 'token' => $event->invite_token]))
            ->assertOk();

        $this->get(route('events.photo', ['event' => $event, 'token' => 'not-the-invite']))
            ->assertNotFound();
    }

    public function test_the_same_header_and_footer_show_for_guests_and_members(): void
    {
        $this->get(route('games.index'))
            ->assertOk()
            ->assertSee('data-app-header', false)
            ->assertSee('Log in')
            ->assertSee('Sign up')
            ->assertSee('See what\'s on. Then go play.', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-app-header', false)
            ->assertSee('Sign up')
            ->assertSee('See what\'s on. Then go play.', false);

        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-app-header', false)
            ->assertSee('Start a game')
            ->assertSee('See what\'s on. Then go play.', false)
            ->assertDontSee('Sign up');
    }

    public function test_the_picture_has_one_sport_pill_and_the_place_stays_in_the_row(): void
    {
        $home = $this->get('/');
        $home->assertOk();
        $this->assertSame(1, substr_count($home->getContent(), 'data-image-pill'));
        $home->assertSee('Padel')
            ->assertSee('Riga · Mežaparks Tennis Courts')
            ->assertDontSee('>Mežaparks<', false);

        $event = Event::factory()->create([
            'title' => 'Hall game',
            'sport' => 'other',
            'custom_sport' => 'Kurling',
            'location' => 'Riga, Arena hall',
            'visibility' => 'public',
            'event_date' => now()->addDay(),
        ]);

        $preview = $this->get(route('events.show', $event));
        $preview->assertOk();
        $this->assertSame(1, substr_count($preview->getContent(), 'data-image-pill'));
        $preview->assertSee('Kurling')
            ->assertSee('Arena hall')
            ->assertDontSee('bottom-3 left-6', false);

        $list = $this->get(route('games.index'));
        $list->assertOk();
        $this->assertSame(1, substr_count($list->getContent(), 'data-image-pill'));
        $list->assertSee('Kurling')
            ->assertSee('Arena hall')
            ->assertDontSee('bottom-3 left-6', false);

        $this->actingAs($event->creator)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('data-image-pill', false)
            ->assertSee('Kurling')
            ->assertSee('Arena hall')
            ->assertDontSee('bottom-3 left-6', false);
    }
}
