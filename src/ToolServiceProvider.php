<?php

namespace DigitalCloud\PermissionTool;

use DigitalCloud\PermissionTool\Http\Middleware\Authorize;
use DigitalCloud\PermissionTool\Policies\PermissionPolicy;
use DigitalCloud\PermissionTool\Policies\RolePolicy;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ToolServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'PermissionTool');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'PermissionTool');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/PermissionTool'),
        ], 'PermissionTool-lang');
        $this->app->booted(function () {
            $this->routes();
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/PermissionTool')
                ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->extend(
            Gate::class,
            function (Gate $gate) {
                return $gate
                    ->policy(config('permission.models.permission'), PermissionPolicy::class)
                    ->policy(config('permission.models.role'), RolePolicy::class)
                ;
            }
        );
    }

    public function provides()
    {
        return [
            Gate::class,
        ];
    }
}
