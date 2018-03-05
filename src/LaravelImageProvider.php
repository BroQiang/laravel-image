<?php
namespace BroQiang\LaravelImage;

use Illuminate\Support\ServiceProvider;

class LaravelImageProvider extends ServiceProvider
{

    public function boot()
    {

        if (!file_exists(config_path('bro_image.php'))) {
            $this->publishes([
                dirname(__DIR__) . '/config/bro_image.php' => config_path('bro_image.php'),
            ], 'config');
        }

    }

    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/config/bro_image.php', 'bro_image'
        );
    }

}
