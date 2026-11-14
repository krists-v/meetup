<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Reset your Varza password')
                ->greeting('Reset your password')
                ->line('We got a request to reset the password for your Varza account. This link is good for '.$minutes.' minutes.')
                ->action('Choose a new password', $url)
                ->line('If you did not ask for this, you can ignore the email. Your password stays as it is.');
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Confirm your Varza email')
                ->greeting('Confirm your email')
                ->line('Tap the button so we know this inbox belongs to your Varza account.')
                ->action('Confirm email', $url)
                ->line('If you did not create a Varza account, you can ignore this email.');
        });
    }
}
