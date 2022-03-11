<?php

namespace SchulzeFelix\SearchConsole;

use Illuminate\Support\ServiceProvider;
use SchulzeFelix\SearchConsole\Exceptions\InvalidConfiguration;

class SearchConsoleServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/search-console.php', 'search-console');

        $this->app->bind(SearchConsoleClient::class, function () {
            $searchConsoleConfig = config('search-console');

            return SearchConsoleClientFactory::createForConfig($searchConsoleConfig);
        });

        $this->app->bind(SearchConsole::class, function () {
            $searchConsoleConfig = config('search-console');

            $this->guardAgainstInvalidConfiguration($searchConsoleConfig);

            $client = app(SearchConsoleClient::class);

            return new SearchConsole($client);
        });

        $this->app->alias(SearchConsole::class, 'laravel-searchconsole');
    }

    /**
     * @param  array|null  $searchConsoleConfig
     *
     * @throws InvalidConfiguration
     */
    protected function guardAgainstInvalidConfiguration(array $searchConsoleConfig = null)
    {
        if ($searchConsoleConfig['auth_type'] == 'service_account' && ! file_exists($searchConsoleConfig['connections']['service_account']['application_credentials'])) {
            throw InvalidConfiguration::credentialsJsonDoesNotExist($searchConsoleConfig['connections']['service_account']['application_credentials']);
        }

        if ($searchConsoleConfig['auth_type'] == 'oauth_json' && ! file_exists($searchConsoleConfig['connections']['oauth_json']['auth_config'])) {
            throw InvalidConfiguration::credentialsJsonDoesNotExist($searchConsoleConfig['connections']['oauth_json']['auth_config']);
        }
    }

    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../config/search-console.php');

        $this->publishes([$source => config_path('search-console.php')]);

        $this->mergeConfigFrom($source, 'search-console');
    }
}
