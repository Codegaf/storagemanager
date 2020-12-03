<?php

namespace Codegaf\StorageManager;

use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;

class StorageManagerProvider extends ServiceProvider {
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../src/stubs/StorageManagerService.stub' => app_path('Services/StorageManagerService/StorageManagerService.php'),
            ], 'service');

            $this->publishes([
                __DIR__.'/../src/stubs/StorageManagerController.stub' => app_path('Http/Controllers/StorageManagerController/StorageManagerController.php'),
            ], 'controller');

        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }

}