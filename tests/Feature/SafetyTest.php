<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Tests\TestCase;

class SafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_429_after_five_attempts_for_the_same_email_and_ip(): void
    {
        $user = User::factory()->create();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(429)
            ->assertSee('Too many tries')
            ->assertSee('Wait a minute, then try again.')
            ->assertSee('Back home');
    }

    public function test_a_write_route_returns_429_after_its_limit(): void
    {
        RateLimiter::for('writes', fn () => Limit::perMinute(2)->by('safety-test'));

        $user = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('events.rsvp', $event), ['status' => 'maybe'])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('events.rsvp', $event), ['status' => 'not_going'])
            ->assertStatus(429)
            ->assertSee('Too many tries');
    }

    public function test_privacy_and_terms_are_linked_from_register(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('Privacy')
            ->assertSee('not legal advice')
            ->assertSee('Krists')
            ->assertSee('delete your account')
            ->assertSee(config('app.contact_email'));

        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Terms')
            ->assertSee('not legal advice')
            ->assertSee('Krists');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('By creating an account you agree to the')
            ->assertSee(route('terms'), false)
            ->assertSee(route('privacy'), false);
    }

    public function test_security_headers_are_present(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
    }

    public function test_error_pages_use_the_varza_copy(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $event = Event::factory()->private()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($stranger)
            ->get(route('events.show', $event))
            ->assertForbidden()
            ->assertSee("You can't open this")
            ->assertSee("This one isn't for you. Head home.")
            ->assertDontSee('invite link');

        $this->actingAs($stranger)
            ->get(route('events.edit', $event))
            ->assertForbidden()
            ->assertSee("This one isn't for you. Head home.")
            ->assertDontSee('private');

        $this->get('/not-a-real-page')
            ->assertNotFound()
            ->assertSee('That page is not here');

        $this->get('/_safety/expired')->assertNotFound();

        Route::get('/_safety/expired', fn () => abort(419));
        Route::get('/_safety/down', fn () => abort(503));
        Route::get('/_safety/hour', fn () => throw new TooManyRequestsHttpException(3600));
        Route::get('/_safety/boom', fn () => throw new \RuntimeException('secret boom'));

        $this->get('/_safety/expired')
            ->assertStatus(419)
            ->assertSee('Your session expired');

        $this->get('/_safety/down')
            ->assertStatus(503)
            ->assertSee('Ejam is briefly down');

        $this->get('/_safety/hour')
            ->assertStatus(429)
            ->assertSee('Try again in about an hour.')
            ->assertDontSee('Wait a minute');

        config(['app.debug' => false]);

        $this->get('/_safety/boom')
            ->assertStatus(500)
            ->assertSee('Something went wrong')
            ->assertDontSee('secret boom');
    }

    public function test_railway_proxy_headers_mark_the_request_as_secure(): void
    {
        Route::get('/_safety/proto', function (Request $request) {
            return $request->secure() ? 'secure' : 'insecure';
        });

        $this->call('GET', '/_safety/proto', server: [
            'REMOTE_ADDR' => '203.0.113.5',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->assertOk()->assertSee('secure');
    }

    public function test_production_forces_https_and_secure_cookies(): void
    {
        $this->assertTrue(AppServiceProvider::shouldForceHttps('production'));
        $this->assertFalse(AppServiceProvider::shouldForceHttps('local'));
        $this->assertTrue(AppServiceProvider::secureSessionCookie('production', null));
        $this->assertFalse(AppServiceProvider::secureSessionCookie('testing', null));
        $this->assertFalse(AppServiceProvider::secureSessionCookie('production', false));
        $this->assertFalse(config('session.secure'));

        $this->app['env'] = 'production';
        URL::forceScheme(null);

        try {
            (new AppServiceProvider($this->app))->boot();
            $this->assertStringStartsWith('https://', url('/'));
        } finally {
            $this->app['env'] = 'testing';
            URL::forceScheme(null);
        }
    }
}
