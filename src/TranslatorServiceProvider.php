<?php

namespace Translator;

use Illuminate\Support\ServiceProvider;
use Translator\Localizer;

class TranslatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Localizer $localizer)
    {
        $localizer->setLocale();
        
        $this->publishes([
            __DIR__.'/Eloquent/Translation.php' => app_path().'/Translation.php'
        ], 'eloquent');
        
        $this->publishes([
            __DIR__.'/config/translator.php' => config_path('translator.php')
        ], 'config');
        
        $this->publishes([
            __DIR__.'/migrations/2016_01_01_000000_create_translations_table.php' => base_path( 'database/migrations/2016_01_01_000000_create_translations_table.php')
        ], 'migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Translator\Localizer');
        $this->app->singleton('Translator\TranslatorRepository');
        $this->app->singleton('router', 'Translator\TranslatorRouter');
        
        $this->app->singleton('url', function(){
            return new \Translator\TranslatorURL(
                \App::make('router')->getRoutes(),
                \App::make('request'),
                \App::make('Translator\Localizer')
            );
        }); 
        
        require __DIR__.'/helpers.php';

    }
}
