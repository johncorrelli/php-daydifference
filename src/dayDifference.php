<?php

namespace johncorrelli;

use \DatePeriod as DatePeriod;
use \DateInterval as DateInterval;
use \DateTime as DateTime;

class dayDifference
{
    const DEFAULT_DAYS_OF_THE_WEEK = [1, 2, 3, 4, 5];
    const DEFAULT_EXCLUDED_DATES = [];

    private $allowedDaysOfTheWeek = [];
    private $endDate;
    private $excludedDates = [];
    private $startDate;

    public function __construct(
        DateTime $startDate,
        DateTime $endDate,
        ?array $allowedDaysOfTheWeek = null,
        ?array $excludedDates = null
    ) {
        $this->allowedDaysOfTheWeek = $allowedDaysOfTheWeek ?? self::DEFAULT_DAYS_OF_THE_WEEK;
        $this->endDate = clone $endDate;
        $this->excludedDates = $excludedDates ?? self::DEFAULT_EXCLUDED_DATES;
        $this->startDate = clone $startDate;
    }

    public function difference()
    {
        if ($this->startDate <= $this->endDate) {
            $isNegative = false;
            $trueStartDate = $this->startDate;
            $trueEndDate = $this->endDate;
        } else {
            // REVERSE THE DAYS BECAUSE IT'S NEGATIVE
            $isNegative = true;
            $trueStartDate = $this->endDate;
            $trueEndDate = $this->startDate;
        }

        $totalDays = $this->totalDays($trueStartDate, $trueEndDate);
        $daysCovered = $this->daysCovered($trueStartDate, $trueEndDate);

        $dayDifference = $this->totalDaysWithExclusions($totalDays, $daysCovered, $this->allowedDaysOfTheWeek, $this->excludedDates);

        if ($isNegative) {
            $dayDifference = $dayDifference * -1;
        }

        return $dayDifference;
    }

    private function daysCovered(DateTime $startDate, DateTime $endDate): DatePeriod
    {
        return new DatePeriod($startDate, new DateInterval('P1D'), $endDate);
    }

    private function isDateExcluded(DateTime $day, array $excludedDates): bool
    {
        $fullFormat = $day->format('Y-m-d');
        $repeatYear = '*-'.$day->format('m-d');

        return in_array($fullFormat, $excludedDates)
            || in_array($repeatYear, $excludedDates);
    }

    private function isDayAllowed(DateTime $day, array $allowedDaysOfTheWeek): bool
    {
        return in_array($day->format('w'), $allowedDaysOfTheWeek);
    }

    private function isDayCounted(DateTime $day, $allowedDaysOfTheWeek, $excludedDates): bool
    {
        $isDayAllowed = $this->isDayAllowed($day, $allowedDaysOfTheWeek);
        $isDateExcluded = $this->isDateExcluded($day, $excludedDates);

        return $isDayAllowed && !$isDateExcluded;
    }

    private function totalDays(DateTime $startDate, DateTime $endDate): int
    {
        $difference = $endDate->diff($startDate);

        return $difference->days;
    }

    private function totalDaysWithExclusions(
        int $totalDays,
        DatePeriod $daysCovered,
        array $allowedDaysOfTheWeek,
        array $excludedDates
    ): int {
        foreach ($daysCovered as $day) {
            $isDayCounted = $this->isDayCounted($day, $allowedDaysOfTheWeek, $excludedDates);

            if ($isDayCounted) {
                continue;
            }

            $totalDays--;
        }

        return $totalDays;
    }
}
