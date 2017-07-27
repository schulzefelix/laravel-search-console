<?php

namespace SchulzeFelix\SearchConsole;

use Carbon\Carbon;
use SchulzeFelix\SearchConsole\Exceptions\InvalidPeriod;

class Period
{
    /** @var Carbon */
    public $startDate;

    /** @var Carbon */
    public $endDate;

    public static function create(Carbon $startDate, Carbon $endDate): Period
    {
        return new static($startDate, $endDate);
    }

    /**
     * @param int $numberOfDays
     * @return Period
     *
     * Modified to match Google Search Console delay in data (3 Days) and PDT timezone
     */
    public static function days(int $numberOfDays): Period
    {
        $endDate = Carbon::today('PDT')->subDays(3);

        $startDate = Carbon::today('PDT')->subDays($numberOfDays + 2)->startOfDay();

        return new static($startDate, $endDate);
    }

    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        if ($startDate > $endDate) {
            throw InvalidPeriod::startDateCannotBeAfterEndDate($startDate, $endDate);
        }

        $this->startDate = $startDate;

        $this->endDate = $endDate;
    }
}
