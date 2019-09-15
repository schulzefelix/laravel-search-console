<?php

namespace SchulzeFelix\SearchConsole;

use Google_Client;
use GuzzleHttp\Client;
use Google_Service_Webmasters;
use Symfony\Component\Cache\Adapter\Psr16Adapter;


class SearchConsoleClientFactory
{
    /**
     * @param array $searchConsoleConfig
     * @return SearchConsoleClient
     */
    public static function createForConfig(array $searchConsoleConfig): SearchConsoleClient
    {
        $authenticatedClient = self::createAuthenticatedGoogleClient($searchConsoleConfig);

        return new SearchConsoleClient($authenticatedClient);
    }

    /**
     * @param array $config
     * @return Google_Client
     */
    public static function createAuthenticatedGoogleClient(array $config): Google_Client
    {
        $client = new Google_Client();

        self::configureAuthentication($client, $config);

        $client->addScope(Google_Service_Webmasters::WEBMASTERS);
        $client->setAccessType('offline');

        self::configureGzip($client, $config['application_name']);
        self::configureCache($client, $config['cache']);

        return $client;
    }

    /**
     * @param Google_Client $client
     * @param $config
     */
    protected static function configureCache(Google_Client $client, $config)
    {
        $config = collect($config);

        $store = \Cache::store($config->get('store'));

        $cache = new Psr16Adapter($store);

        $client->setCache($cache);

        $client->setCacheConfig(
            $config->except('store')->toArray()
        );
    }

    /**
     * @param Google_Client $client
     * @param $application_name
     */
    private static function configureGzip(Google_Client $client, $application_name)
    {
        $client->setApplicationName($application_name.' (gzip)');

        $options = [];
        $options['base_uri'] = Google_Client::API_BASE_PATH;
        $options['headers'] = [
            'User-Agent' => $application_name.' (gzip)',
            'Accept-Encoding' => 'gzip',
        ];

        $guzzleClient = new Client($options);

        $client->setHttpClient($guzzleClient);
    }

    /**
     * @param Google_Client $client
     * @param $config
     */
    private static function configureAuthentication(Google_Client $client, $config)
    {
        switch ($config['auth_type']):
            case 'oauth':
                $client->setClientId($config['connections']['oauth']['client_id']);
        $client->setClientSecret($config['connections']['oauth']['client_secret']);
        break;
        case 'oauth_json':
                $client->setAuthConfig($config['connections']['oauth_json']['auth_config']);
        break;
        case 'service_account':
                $client->useApplicationDefaultCredentials($config['connections']['service_account']['application_credentials']);
        break;
        endswitch;
    }
}
