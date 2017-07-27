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
        $this->publishes([
            __DIR__.'/config/search-console.php' => config_path('search-console.php'),
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/search-console.php', 'search-console');

        $searchConsoleConfig = config('search-console');

        $this->app->bind(SearchConsoleClient::class, function () use ($searchConsoleConfig) {
            return SearchConsoleClientFactory::createForConfig($searchConsoleConfig);
        });

        $this->app->bind(SearchConsole::class, function () use ($searchConsoleConfig) {
            $this->guardAgainstInvalidConfiguration($searchConsoleConfig);

            $client = app(SearchConsoleClient::class);

            return new SearchConsole($client);
        });

        $this->app->alias(SearchConsole::class, 'laravel-searchconsole');
    }

    protected function guardAgainstInvalidConfiguration(array $searchConsoleConfig = null)
    {
        if ($searchConsoleConfig['auth_type'] == 'service_account' && ! file_exists($searchConsoleConfig['connections']['service_account']['application_credentials'])) {
            throw InvalidConfiguration::credentialsJsonDoesNotExist($searchConsoleConfig['connections']['service_account']['application_credentials']);
        }

        if ($searchConsoleConfig['auth_type'] == 'oauth_json' && ! file_exists($searchConsoleConfig['connections']['oauth_json']['auth_config'])) {
            throw InvalidConfiguration::credentialsJsonDoesNotExist($searchConsoleConfig['connections']['oauth_json']['auth_config']);
        }
    }
}
