<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SocialTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_share_includes_the_private_invite_and_a_copy_link(): void
    {
        $creator = User::factory()->create();
        $event = Event::factory()->private()->create([
            'user_id' => $creator->id,
            'title' => 'Thursday five-a-side',
            'sport' => 'football',
            'sport_type' => 'football',
            'location' => 'Park pitch',
            'event_date' => '2026-12-10 18:00:00',
        ]);

        $this->get(route('events.invite', $event->invite_token))
            ->assertOk()
            ->assertSee('Share on WhatsApp', false)
            ->assertSee('Copy link', false)
            ->assertSee($event->invite_token, false)
            ->assertDontSee('Post comment');

        $this->actingAs($creator)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Share on WhatsApp', false)
            ->assertSee('Copy link', false)
            ->assertSee('https://wa.me/?text=', false)
            ->assertSee($event->invite_token, false)
            ->assertSee($event->shareUrl(), false);

        $this->assertStringContainsString($event->invite_token, $event->whatsAppUrl());
    }

    public function test_a_public_game_shares_its_public_link(): void
    {
        $event = Event::factory()->create([
            'visibility' => 'public',
            'title' => 'Open run',
            'location' => 'City court',
        ]);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('https://wa.me/?text=', false)
            ->assertSee($event->shareUrl(), false)
            ->assertDontSee($event->invite_token);

        $this->assertStringNotContainsString($event->invite_token, $event->whatsAppUrl());
        $this->assertSame(route('events.show', $event), $event->shareUrl());
    }

    public function test_profile_saves_name_photo_and_favorite_sports(): void
    {
        Storage::fake('avatars');

        $user = User::factory()->create(['name' => 'Old Name']);
        $event = Event::factory()->create([
            'visibility' => 'public',
            'max_participants' => 10,
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Alex Rivera',
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->createWithContent('face.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')),
                'favorite_sports' => ['football', 'padel'],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('Alex Rivera', $user->name);
        $this->assertSame(['football', 'padel'], $user->favorite_sports);
        $this->assertNotNull($user->avatar_path);
        Storage::disk('avatars')->assertExists($user->avatar_path);

        $photo = $this->get(route('avatars.show', $user));
        $photo->assertOk();
        $this->assertStringStartsWith('image/', (string) $photo->headers->get('content-type'));

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('id="favorite-football"', false)
            ->assertSee('Sports you play')
            ->assertSee(route('avatars.show', $user), false);

        $this->actingAs($user)->post(route('events.rsvp', $event), ['status' => 'going']);

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee(route('avatars.show', $user), false)
            ->assertSee('Alex Rivera');

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Alex Rivera',
                'email' => $user->email,
                'remove_avatar' => '1',
                'favorite_sports' => ['football'],
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertNull($user->avatar_path);
        $this->assertSame(['football'], $user->favorite_sports);
        $this->get(route('avatars.show', $user))->assertNotFound();
    }

    public function test_a_sideways_photo_is_turned_upright_before_it_is_saved(): void
    {
        if (! function_exists('imagejpeg') || ! function_exists('exif_read_data') || ! function_exists('imagecreatefromstring')) {
            $this->markTestSkipped('GD and EXIF are required to check photo orientation.');
        }

        Storage::fake('avatars');

        $wide = imagecreatetruecolor(40, 10);
        $red = imagecolorallocate($wide, 220, 20, 20);
        $blue = imagecolorallocate($wide, 20, 20, 220);
        imagefilledrectangle($wide, 0, 0, 19, 9, $red);
        imagefilledrectangle($wide, 20, 0, 39, 9, $blue);
        ob_start();
        imagejpeg($wide, null, 95);
        $jpeg = ob_get_clean();
        imagedestroy($wide);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->createWithContent('face.jpg', $this->jpegWithOrientation($jpeg, 6)),
            ])
            ->assertSessionHasNoErrors();

        $stored = Storage::disk('avatars')->get($user->fresh()->avatar_path);
        $image = imagecreatefromstring($stored);
        $this->assertNotFalse($image);
        $this->assertSame(10, imagesx($image));
        $this->assertSame(40, imagesy($image));

        $top = imagecolorsforindex($image, imagecolorat($image, 5, 2));
        $bottom = imagecolorsforindex($image, imagecolorat($image, 5, 37));
        imagedestroy($image);

        $this->assertGreaterThan($top['blue'], $top['red']);
        $this->assertGreaterThan($bottom['red'], $bottom['blue']);
    }

    public function test_mirrored_sideways_photos_land_right_way_up(): void
    {
        if (! function_exists('imagejpeg') || ! function_exists('exif_read_data') || ! function_exists('imagecreatefromstring')) {
            $this->markTestSkipped('GD and EXIF are required to check photo orientation.');
        }

        $wide = imagecreatetruecolor(40, 10);
        $red = imagecolorallocate($wide, 220, 20, 20);
        $blue = imagecolorallocate($wide, 20, 20, 220);
        imagefilledrectangle($wide, 0, 0, 39, 9, $blue);
        imagefilledrectangle($wide, 0, 0, 9, 4, $red);
        ob_start();
        imagejpeg($wide, null, 95);
        $jpeg = ob_get_clean();
        imagedestroy($wide);

        $upright = $this->storedAvatar($jpeg, 5);
        $this->assertSame('R', $this->swatch($upright, 2, 2));
        $this->assertSame('B', $this->swatch($upright, 2, 30));
        imagedestroy($upright);

        $other = $this->storedAvatar($jpeg, 7);
        $this->assertSame('R', $this->swatch($other, 7, 35));
        $this->assertSame('B', $this->swatch($other, 2, 2));
        imagedestroy($other);
    }

    public function test_profile_rejects_a_file_that_is_not_a_photo(): void
    {
        Storage::fake('avatars');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->create('notes.txt', 20, 'text/plain'),
            ])
            ->assertSessionHasErrors('avatar');

        $this->assertNull($user->fresh()->avatar_path);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'favorite_sports' => ['curling'],
            ])
            ->assertSessionHasErrors('favorite_sports.0');
    }

    public function test_comments_are_ordered_escaped_and_limited_to_people_who_can_see_the_game(): void
    {
        $creator = User::factory()->create();
        $player = User::factory()->create(['name' => 'Sam Okonkwo']);
        $stranger = User::factory()->create();
        $event = Event::factory()->private()->create([
            'user_id' => $creator->id,
            'visibility' => 'private',
        ]);
        $event->participants()->attach($player->id, ['status' => 'going']);

        $this->post(route('events.comments.store', $event), ['body' => 'Hello'])
            ->assertRedirect(route('login'));

        $this->actingAs($stranger)
            ->post(route('events.comments.store', $event), ['body' => 'Hello'])
            ->assertForbidden();

        $this->actingAs($player)
            ->post(route('events.comments.store', $event), ['body' => '   '])
            ->assertSessionHasErrors('body');

        $this->actingAs($player)
            ->post(route('events.comments.store', $event), [
                'body' => 'Arriving early <script>alert(1)</script>',
            ])
            ->assertRedirect();

        $this->actingAs($creator)
            ->post(route('events.comments.store', $event), ['body' => 'Bringing a ball'])
            ->assertRedirect();

        $this->actingAs($player)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSeeInOrder(['Arriving early', 'Bringing a ball'])
            ->assertSee(e('Arriving early <script>alert(1)</script>'), false)
            ->assertDontSee('<script>alert(1)</script>', false);

        $comment = $event->comments()->first();
        $creatorComment = $event->comments()->skip(1)->first();

        $this->actingAs($stranger)
            ->delete(route('events.comments.destroy', [$event, $comment]))
            ->assertForbidden();

        $this->actingAs($creator)
            ->delete(route('events.comments.destroy', [$event, $comment]))
            ->assertRedirect();

        $this->assertModelMissing($comment);

        $other = Event::factory()->create(['user_id' => $creator->id]);
        $this->actingAs($creator)
            ->delete(route('events.comments.destroy', [$other, $creatorComment]))
            ->assertNotFound();

        $this->actingAs($player)
            ->delete(route('events.comments.destroy', [$event, $creatorComment]))
            ->assertForbidden();

        $this->actingAs($creator)
            ->delete(route('events.comments.destroy', [$event, $creatorComment]))
            ->assertRedirect();

        $this->assertModelMissing($creatorComment);
    }

    public function test_anyone_logged_in_can_comment_on_a_public_game(): void
    {
        $event = Event::factory()->create(['visibility' => 'public']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('events.comments.store', $event), ['body' => 'I can make it'])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('I can make it')
            ->assertSee('Post comment');

        $comment = $event->comments()->first();
        $this->actingAs($user)
            ->delete(route('events.comments.destroy', [$event, $comment]))
            ->assertRedirect();

        $this->assertModelMissing($comment);
    }

    public function test_comment_posting_is_rate_limited(): void
    {
        RateLimiter::for('comments', fn () => Limit::perMinute(2)->by('social-test'));

        $user = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'visibility' => 'public',
        ]);

        $this->actingAs($user)->post(route('events.comments.store', $event), ['body' => 'One'])->assertRedirect();
        $this->actingAs($user)->post(route('events.comments.store', $event), ['body' => 'Two'])->assertRedirect();
        $this->actingAs($user)
            ->post(route('events.comments.store', $event), ['body' => 'Three'])
            ->assertStatus(429)
            ->assertSee('Too many tries');
    }

    private function storedAvatar(string $jpeg, int $orientation): \GdImage
    {
        Storage::fake('avatars');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->createWithContent('face.jpg', $this->jpegWithOrientation($jpeg, $orientation)),
            ])
            ->assertSessionHasNoErrors();

        $image = imagecreatefromstring(Storage::disk('avatars')->get($user->fresh()->avatar_path));
        $this->assertNotFalse($image);
        $this->assertSame(10, imagesx($image));
        $this->assertSame(40, imagesy($image));

        return $image;
    }

    /**
     * @return 'R'|'B'
     */
    private function swatch(\GdImage $image, int $x, int $y): string
    {
        $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));

        return $color['red'] > $color['blue'] ? 'R' : 'B';
    }

    private function jpegWithOrientation(string $jpeg, int $orientation): string
    {
        $exif = "Exif\x00\x00"
            .'MM'."\x00\x2A"
            ."\x00\x00\x00\x08"
            ."\x00\x01"
            ."\x01\x12"
            ."\x00\x03"
            ."\x00\x00\x00\x01"
            .pack('n', $orientation)."\x00\x00"
            ."\x00\x00\x00\x00";

        return "\xFF\xD8"."\xFF\xE1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
    }
}
