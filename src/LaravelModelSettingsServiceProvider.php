<?php

namespace Dkvhin\LaravelModelSettings;
use Illuminate\Support\ServiceProvider;

class LaravelModelSettingsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/model_settings.php' => config_path('model_settings.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../database/migrations/create_model_settings_table.php.stub' => database_path('migrations/2024_08_29_222954_create_system_model_has_settings_table.php'),
            ], 'migrations');
        }
    }
}
