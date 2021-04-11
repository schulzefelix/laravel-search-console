# Laravel Search Console

[![Latest Version](https://img.shields.io/github/release/schulzefelix/laravel-search-console.svg?style=flat-square)](https://github.com/schulzefelix/laravel-search-console/releases)
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![StyleCI](https://styleci.io/repos/97710032/shield)](https://styleci.io/repos/97710032)
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

Using this package you can easily retrieve data from Google Search Console API.

## Install

> For Laravel 5.8, use version 1.2.0 of this package!

This package can be installed through Composer.

``` bash
$ composer require schulzefelix/laravel-search-console
```

Optionally, you can publish the config file of this package with this command:

``` bash
php artisan vendor:publish --provider="SchulzeFelix\SearchConsole\SearchConsoleServiceProvider"
```

The following config file will be published in `config/search-console.php`

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    | Google offers access via OAuth client IDs or service accounts.
    | For more information see: https://developers.google.com/identity/protocols/OAuth2
    |
    | Supported: "oauth", "oauth_json", "service_account",
    */
 
    'auth_type' => env('GOOGLE_AUTH_TYPE', 'oauth'),
 
    /*
    |--------------------------------------------------------------------------
    | Application Credentials
    |--------------------------------------------------------------------------
    |
    | https://developers.google.com/api-client-library/php/auth/service-accounts#creatinganaccount
    */
 
    'connections' => [
 
        'oauth' => [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        ],
 
        'oauth_json' => [
            'auth_config' => storage_path('app/searchconsole/oauth-account-credentials.json'),
        ],
 
        'service_account' => [
            'application_credentials' => storage_path('app/searchconsole/service-account-credentials.json'),
        ],
 
    ],
 
    /*
     |--------------------------------------------------------------------------
     | Cache Settings
     |--------------------------------------------------------------------------
     | Here you may configure the "store" that the underlying Google_Client will
     | use to store it's data.  You may also add extra parameters that will
     | be passed on setCacheConfig (see docs for google-api-php-client).
     |
     | Optional parameters: "lifetime", "prefix"
     */
 
    'cache' => [
        'store' => 'file',
    ],
 
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */
 
    'application_name' => env('SEARCH_CONSOLE_APPLICATION_NAME', 'GSC Agent'),
];
```

## Usage

Here are two basic example to retrieve all sites and an export for search analytics data.
### List Sites

```php
$sites = SearchConsole::setAccessToken($token)->listSites();
```

### Search Analytics

```php
    $data = SearchConsole::setAccessToken($token)->setQuotaUser('uniqueQuotaUserString')
        ->searchAnalyticsQuery(
            'https://www.example.com/',
            Period::create(Carbon::now()->subDays(30), Carbon::now()->subDays(2)),
            ['query', 'page', 'country', 'device', 'date'],
            [['dimension' => 'query', 'operator' => 'notContains', 'expression' => 'cheesecake']],
            1000,
            'web',
            'all',
            'auto'
        );
```

## Provided methos
### Retrieve One Site
```php
public function public function getSite(string $siteUrl): array
```

### Retrieve All Sites
```php
public function public function listSites(): Collection
```

### Retrieve Search Analytics Data
```php
public function searchAnalyticsQuery(string $siteUrl, Period $period, array $dimensions = [], array $filters = [], int $rows = 1000, string $searchType = 'web', string $dataState = 'final', string $aggregationType = 'auto'): Collection
```

### Check Access Token
```php
public function public function isAccessTokenExpired(): Bool
```

## Provided fluent configuration

### Set Access Token (Required)

```php
$sites = SearchConsole::setAccessToken($token)->listSites();
```

### Set Quota User
To avoid to the API limits, you can provide a unique string for the authenticated account.

More information: https://developers.google.com/webmaster-tools/search-console-api-original/v3/limits
```php
$sites = SearchConsole::setAccessToken($token)->setQuotaUser('uniqueQuotaUserString')->listSites();
```

## Get Underlying Service
You can get access to the underlying `Google_Service_Webmasters` object:

```php
SearchConsole::getWebmastersService();
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email github@schulze.co instead of using the issue tracker.

## Credits

- [Felix Schulze][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/schulzefelix/laravel-search-console.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/schulzefelix/laravel-search-console/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/schulzefelix/laravel-search-console.svg?style=flat-square
[ico-code-quality]: https://scrutinizer-ci.com/g/schulzefelix/laravel-search-console/badges/quality-score.png?b=master
[ico-downloads]: https://img.shields.io/packagist/dt/schulzefelix/laravel-search-console.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/schulzefelix/laravel-search-console
[link-travis]: https://travis-ci.org/schulzefelix/laravel-search-console
[link-scrutinizer]: https://scrutinizer-ci.com/g/schulzefelix/laravel-search-console/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/schulzefelix/laravel-search-console
[link-downloads]: https://packagist.org/packages/schulzefelix/laravel-search-console
[link-author]: https://github.com/schulzefelix
[link-contributors]: ../../contributors
