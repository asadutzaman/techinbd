<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(\App\Support\CatalogCache::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Catch N+1 queries: tests fail on a lazy load, local dev only logs it
        Model::preventLazyLoading(! $this->app->isProduction());
        if (! $this->app->runningUnitTests()) {
            Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
                logger()->warning('Lazy loaded ' . get_class($model) . '::' . $relation . ' (N+1 query)');
            });
        }

        // Both the admin (AdminLTE) and the storefront theme are Bootstrap 4; Laravel's default pagination
        // markup is Tailwind, which rendered as page-sized arrows. (The shop uses custom-pagination.)
        Paginator::useBootstrapFour();

        // Storefront layout data: menu categories and header badge counts
        view()->composer('layouts.app', \App\Http\View\Composers\MenuComposer::class);
        view()->composer('layouts.app', \App\Http\View\Composers\HeaderCountsComposer::class);

        // Admin sidebar: how many orders are waiting to be confirmed
        view()->composer('admin.layouts.app', fn ($view) => $view->with('pendingOrderCount', \App\Models\Order::where('status', 'pending')->count()));

        // The password reset email. Its link is built on APP_URL, not on the host the request came in
        // with, so nobody can have a reset link point at another site.
        ResetPassword::toMailUsing(function ($user, string $token) {
            $url = rtrim(config('app.url'), '/') . route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()], false);
            $minutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

            return (new MailMessage)
                ->subject('Reset your ' . config('shop.name') . ' password')
                ->greeting('Hi ' . Str::before(trim($user->name), ' ') . ',')
                ->line('We got a request to reset the password for your account.')
                ->action('Set a new password', $url)
                ->line("The link works for {$minutes} minutes. If you didn't ask for this, ignore this email and your password stays the same.")
                ->salutation('Thanks, ' . config('shop.name'));
        });
    }
}
