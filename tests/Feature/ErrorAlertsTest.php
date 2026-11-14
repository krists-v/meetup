<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use ReflectionProperty;
use RuntimeException;
use Sentry\HttpClient\HttpClientInterface;
use Sentry\HttpClient\Request;
use Sentry\HttpClient\Response;
use Sentry\Options;
use Sentry\State\HubInterface;
use Sentry\Transport\HttpTransport;
use Tests\TestCase;

class ErrorAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_app_serves_requests_and_reports_exceptions_without_a_sentry_dsn(): void
    {
        $this->assertTrue(blank(config('sentry.dsn')));
        $this->assertSame(0.0, config('sentry.traces_sample_rate'));
        $this->assertFalse(config('sentry.send_default_pii'));
        $this->assertSame('php://stderr', config('logging.channels.stderr.handler_with.stream'));

        $this->get('/')->assertOk();
        $this->get('/up')->assertOk();

        $this->replaceSentryHttpClient();

        $reported = false;

        Event::listen(MessageLogged::class, function (MessageLogged $event) use (&$reported) {
            if ($event->level === 'error' && str_contains($event->message, 'secret boom')) {
                $reported = true;
            }
        });

        config(['app.debug' => false]);

        Route::get('/_alerts/boom', fn () => throw new RuntimeException('secret boom'));

        $this->get('/_alerts/boom')
            ->assertStatus(500)
            ->assertSee('Something went wrong')
            ->assertDontSee('secret boom');

        $this->assertTrue($reported);

        \Sentry\captureException(new RuntimeException('captured without a dsn'));
    }

    public function test_production_logs_default_to_stderr_and_an_explicit_channel_wins(): void
    {
        $this->assertSame('stderr', AppServiceProvider::defaultLogChannel(null, 'production'));
        $this->assertSame('stderr', AppServiceProvider::defaultLogChannel('', 'production'));
        $this->assertSame('stack', AppServiceProvider::defaultLogChannel(null, 'local'));
        $this->assertSame('stack', AppServiceProvider::defaultLogChannel('stack', 'production'));
        $this->assertSame('single', AppServiceProvider::defaultLogChannel('single', 'local'));

        $entrypoint = file_get_contents(base_path('docker/entrypoint.sh'));

        $this->assertIsString($entrypoint);
        $this->assertStringContainsString('LOG_CHANNEL="${LOG_CHANNEL:-stderr}"', $entrypoint);
    }

    private function replaceSentryHttpClient(): void
    {
        $client = $this->app->make(HubInterface::class)->getClient();

        $this->assertNotNull($client);
        $this->assertNull($client->getOptions()->getDsn());

        $transport = (new ReflectionProperty($client, 'transport'))->getValue($client);

        $this->assertInstanceOf(HttpTransport::class, $transport);

        (new ReflectionProperty($transport, 'httpClient'))->setValue($transport, new class implements HttpClientInterface
        {
            public function sendRequest(Request $request, Options $options): Response
            {
                throw new RuntimeException('Sentry made a network call without a DSN.');
            }
        });
    }
}
