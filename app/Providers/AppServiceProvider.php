<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider
 *
 * The main service provider for the application. This provider is used to bind services
 * into the service container and to bootstrap application-level functionality.
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This method is used to bind any services into the container. It is called
     * before the `boot` method.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * This method is called after all other service providers have been registered,
     * meaning you have access to all other services that have been registered by the framework.
     * It is used for tasks like registering event listeners, routes, or any other piece
     * of functionality you want to add to your application.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
