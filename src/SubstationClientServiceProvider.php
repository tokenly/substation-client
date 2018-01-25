<?php

namespace Tokenly\SubstationClient;

use Illuminate\Support\ServiceProvider;
use Tokenly\SubstationClient\Mock\MockSubstationClient;
use Tokenly\SubstationClient\SubstationClient;

class SubstationClientServiceProvider extends ServiceProvider
{

    public function register()
    {
        /**
         * for package configuring
         */
        $configPath = __DIR__ . '/config/substation-client.php';
        $this->mergeConfigFrom($configPath, 'substation-client');
        $this->publishes([$configPath => config_path('substation-client.php')], 'substation-client');

        // bind classes
        $this->app->bind(SubstationClient::class, function ($app) {
            $config = $app['config']->get('substation-client');
            return new SubstationClient($config['connection_url'], $config['api_token'], $config['api_key']);
        });

        $this->app->bind(MockSubstationClient::class, function ($app) {
            $config = $app['config']->get('substation-client');
            return new MockSubstationClient('http://127.0.0.1', 'TEST_API_TOKEN', 'TEST_API_KEY');
        });
    }

}
