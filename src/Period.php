<?php

namespace SchulzeFelix\SearchConsole;

use DateTime;
use Carbon\Carbon;
use SchulzeFelix\SearchConsole\Exceptions\InvalidPeriod;

class Period
{
    /** @var \DateTime */
    public $startDate;

    /** @var \DateTime */
    public $endDate;

    public static function create(DateTime $startDate, $endDate): Period
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

    public function __construct(DateTime $startDate, DateTime $endDate)
    {
        if ($startDate > $endDate) {
            throw InvalidPeriod::startDateCannotBeAfterEndDate($startDate, $endDate);
        }

        $this->startDate = $startDate;

        $this->endDate = $endDate;
    }
}
