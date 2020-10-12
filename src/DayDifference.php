<?php

namespace johncorrelli;

use DateTimeInterface;

class DayDifference
{
    /**
     * Number of seconds in one day.
     */
    const ONE_DAY = 86400;

    /**
     * @var array<int> 0-6 representation of days of the week, where 0 = Sunday, and 6 = Saturday
     */
    private array $allowedDaysOfTheWeek = [];

    private DateTimeInterface $endDate;

    /**
     * @var array<string> YYYY-MM-DD Dates that should not be counted. ie: 2020-01-01 or *-07-04 for dates that repeat each year.
     */
    private array $excludedDates = [];

    private DateTimeInterface $startDate;

    /**
     * @param array<int>    $allowedDaysOfTheWeek
     * @param array<string> $excludedDates
     */
    public function __construct(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        array $allowedDaysOfTheWeek = [0, 1, 2, 3, 4, 5, 6],
        array $excludedDates = []
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->allowedDaysOfTheWeek = $allowedDaysOfTheWeek;
        $this->excludedDates = $excludedDates;
    }

    /**
     * Determines the number of allowed days between a start and an end date.
     */
    public function difference(): int
    {
        $isPositive = $this->startDate <= $this->endDate;

        if ($isPositive) {
            $multiply = 1;
            $startStamp = $this->startDate->getTimestamp();
            $endStamp = $this->endDate->getTimestamp();
        } else {
            $multiply = -1;
            $startStamp = $this->endDate->getTimestamp();
            $endStamp = $this->startDate->getTimestamp();
        }

        return $this->findDifference($startStamp, $endStamp, $this->allowedDaysOfTheWeek, $this->excludedDates) * $multiply;
    }

    /**
     * Returns the number of allowed days between the start and end timestamp.
     *
     * @param array<int>    $allowedDaysOfTheWeek
     * @param array<string> $excludedDates
     */
    private function findDifference(int $startStamp, int $endStamp, array $allowedDaysOfTheWeek = [], array $excludedDates = []): int
    {
        $isFullWeek = count($allowedDaysOfTheWeek) === 7;
        $hasExclusions = !empty($excludedDates);
        $currentStamp = $startStamp;
        $totalDays = 0;

        while ($currentStamp < $endStamp) {
            $totalDays += (int) (
                // the day of the week is allowed
                ($isFullWeek || $this->isAllowed($currentStamp, $allowedDaysOfTheWeek))
                // the date is not excluded
                && (!$hasExclusions || !$this->isExcluded($currentStamp, $excludedDates))
            );

            $currentStamp += self::ONE_DAY;
        }

        return $totalDays;
    }

    /**
     * Determines if the day of the week is allowed based off of the supplied allowedDaysOfTheWeek array.
     *
     * @param array<int> $allowedDaysOfTheWeek
     */
    private function isAllowed(int $timestamp, array $allowedDaysOfTheWeek): bool
    {
        return in_array(date('w', $timestamp), $allowedDaysOfTheWeek);
    }

    /**
     * Determines if the date is allowed based of excluded dates.
     *
     * @param array<string> $excludedDates
     */
    private function isExcluded(int $timestamp, array $excludedDates): bool
    {
        return in_array(date('Y-m-d', $timestamp), $excludedDates)
            || in_array(date('*-m-d', $timestamp), $excludedDates);
    }
}
