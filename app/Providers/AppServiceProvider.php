<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

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

        // Storefront layout data: menu categories and header badge counts
        view()->composer('layouts.app', \App\Http\View\Composers\MenuComposer::class);
        view()->composer('layouts.app', \App\Http\View\Composers\HeaderCountsComposer::class);
    }
}
