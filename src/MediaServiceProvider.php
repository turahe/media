<?php

namespace Turahe\Media;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Turahe\Media\Contracts\ResizeDriver;
use Turahe\Media\Resize\ResizeManager;

class MediaServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/media.php', 'media'
        );

        $this->app->singleton(ResizeManager::class);

        $this->app->bind(ResizeDriver::class, function ($app) {
            return $app->make(ResizeManager::class)->driver();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Migrations
        if ($this->app instanceof Application) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_media_table.stub' => database_path(
                    'migrations/'.date('Y_m_d_His', time()).'_create_media_table.php'
                ),
            ], 'migrations');
        }

        // Config
        $this->publishes([
            __DIR__.'/../config/media.php' => config_path('media.php'),
        ], 'config');
    }
}
