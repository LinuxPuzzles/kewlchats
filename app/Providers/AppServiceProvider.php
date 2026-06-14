<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Xmpp\EjabberdApiProvisioner;
use App\Services\Xmpp\MockXmppProvisioner;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(XmppProvisioner::class, function () {
            return match (config('xmpp.driver')) {
                'ejabberd' => new EjabberdApiProvisioner(),
                default => new MockXmppProvisioner(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Admin area access. Used as the `can:admin` route middleware and `@can('admin')`.
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        // On-brand copy for the two emails we send. Both render through the branded
        // markdown mail components (resources/views/vendor/mail/*).
        VerifyEmail::toMailUsing(fn (object $notifiable, string $url) => (new MailMessage)
            ->subject('Verify your KewlChats email')
            ->greeting('Almost there!')
            ->line('Verify your email to activate your chat account.')
            ->action('Verify email', $url)
            ->line("If you didn't sign up for KewlChats, you can safely ignore this email."));

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                ->subject('Reset your KewlChats password')
                ->greeting('Password reset')
                ->line('We got a request to reset your password. Tap below to choose a new one.')
                ->action('Reset password', $url)
                ->line('This link expires in '.config('auth.passwords.users.expire', 60).' minutes.')
                ->line("Didn't request this? Ignore this email — your password won't change.");
        });
    }
}
