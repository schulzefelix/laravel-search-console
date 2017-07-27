<?php

namespace SchulzeFelix\SearchConsole;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SchulzeFelix\SearchConsole\SearchConsole
 */
class SearchConsoleFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-searchconsole';
    }
}
