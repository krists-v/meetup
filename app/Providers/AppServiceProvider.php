<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public static function shouldForceHttps(string $environment): bool
    {
        return $environment === 'production';
    }

    /**
     * Secure cookies in production unless SESSION_SECURE_COOKIE is set explicitly.
     */
    public static function secureSessionCookie(string $environment, mixed $override): bool
    {
        if ($override !== null) {
            return filter_var($override, FILTER_VALIDATE_BOOLEAN);
        }

        return $environment === 'production';
    }

    /**
     * Production logs go to stderr so Railway can collect them. Any explicit channel, including the local stack, wins.
     */
    public static function defaultLogChannel(?string $channel, string $environment): string
    {
        if (filled($channel)) {
            return $channel;
        }

        return $environment === 'production' ? 'stderr' : 'stack';
    }

    /**
     * Use Resend only when a key is present. Otherwise keep the log mailer so a missing key cannot take the app down.
     */
    public static function resolveMailer(?string $mailer, ?string $resendKey): string
    {
        $mailer = filled($mailer) ? $mailer : 'log';

        if ($mailer === 'resend' && blank($resendKey)) {
            return 'log';
        }

        return $mailer;
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (self::shouldForceHttps($this->app->environment())) {
            URL::forceScheme('https');
        }

        $this->configureRateLimiting();

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Reset your Varza password')
                ->greeting('Choose a new password')
                ->line('You asked to reset your Varza password. This link works for '.$minutes.' minutes.')
                ->action('Choose a new password', $url)
                ->line('If you did not ask for this, you can ignore the email. Your password stays as it is.')
                ->salutation('See you on the pitch.');
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Confirm your Varza email')
                ->greeting('Confirm your email')
                ->line('Tap the button so we know this is your email.')
                ->action('Confirm email', $url)
                ->line('If you did not create a Varza account, you can ignore this email.')
                ->salutation('See you on the pitch.');
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('password-email', function (Request $request) {
            $email = Str::transliterate(Str::lower((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('writes', function (Request $request) {
            return Limit::perMinute(30)->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('events', function (Request $request) {
            return Limit::perHour(10)->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('comments', function (Request $request) {
            return Limit::perMinute(10)->by((string) ($request->user()?->id ?: $request->ip()));
        });
    }
}
