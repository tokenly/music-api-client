<?php

namespace Tokenly\MusicClient\Provider;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Tokenly\APIClient\TokenlyAPI;
use Tokenly\HmacAuth\Generator;
use Tokenly\MusicClient\MusicAPI;

/**
* 
*/
class MusicClientProvider extends ServiceProvider
{
    
    public function boot() {
        $this->publishes([$this->packageConfigPath() => config_path('musicclient.php')], 'config');
    }

    public function register() {
        $this->mergeConfigFrom(
            $this->packageConfigPath(), 'musicclient'
        );

        $this->app->bind('MusicAPI', function($app) {
            $tokenly_api = new TokenlyAPI(Config::get('musicclient.base_url'), new Generator(), Config::get('musicclient.client_secret'), Config::get('musicclient.client_secret'));
            return new MusicAPI($tokenly_api);
        });

    }

    protected function packageConfigPath() {
        return __DIR__.'/../../config/musicclient.php';
    }
}