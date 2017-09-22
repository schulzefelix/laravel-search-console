<?php

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
            'auth_config' => env('GOOGLE_AUTH_CONFIG'),
        ],

        'service_account' => [
            'application_credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
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
     | WARNING: Don't enable cache if you handling with more than one access token.
     | Google itself removed underlying cache library till they can fix the issue.
     |
     | store parameter: null, file
     | Optional parameters: "lifetime", "prefix"
     */

    'cache' => [
        'store' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */

    'application_name' => env('SEARCH_CONSOLE_APPLICATION_NAME', 'GSC Agent'),
];
