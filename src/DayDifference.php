<?php

namespace johncorrelli;

class DayDifference
{
    /**
     * Number of seconds in one day.
     */
    public const SECONDS_IN_ONE_DAY = 86400;

    /**
     * @var array<int> 0-6 representation of days of the week, where 0 = Sunday, and 6 = Saturday
     */
    private array $allowedDaysOfTheWeek = [];

    private \DateTimeInterface $endDate;

    /**
     * @var array<string> YYYY-MM-DD Dates that should not be counted. ie: 2020-01-01 or *-07-04 for dates that repeat each year.
     */
    private array $excludedDates = [];

    private \DateTimeInterface $startDate;

    /**
     * @var array<int, array<string>> an associative array mapping between timestamps and their exploded date values
     */
    private static array $cache = [];

    /**
     * @param array<int>    $allowedDaysOfTheWeek
     * @param array<string> $excludedDates
     */
    public function __construct(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $allowedDaysOfTheWeek = [0, 1, 2, 3, 4, 5, 6],
        array $excludedDates = []
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->allowedDaysOfTheWeek = $allowedDaysOfTheWeek;
        $this->excludedDates = $excludedDates;
    }

    /**
     * @param array<int, array<string>> $cache
     */
    public static function set_cache($cache): void
    {
        self::$cache = $cache;
    }

    /**
     * @return array<int, array<string>>
     */
    public static function get_cache()
    {
        return self::$cache;
    }

    /**
     * Determines the number of allowed days between a start and an end date.
     */
    public function difference(): int
    {
        $isPositive = $this->startDate <= $this->endDate;

        if ($isPositive) {
            $startStamp = $this->startDate->getTimestamp();
            $endStamp = $this->endDate->getTimestamp();

            return $this->findDifference($startStamp, $endStamp, $this->allowedDaysOfTheWeek, $this->excludedDates);
        }

        $startStamp = $this->endDate->getTimestamp();
        $endStamp = $this->startDate->getTimestamp();

        return $this->findDifference($startStamp, $endStamp, $this->allowedDaysOfTheWeek, $this->excludedDates) * -1;
    }

    /**
     * Returns the number of allowed days between the start and end timestamp.
     *
     * @param array<int>    $allowedDaysOfTheWeek
     * @param array<string> $excludedDates
     */
    private function findDifference(int $startStamp, int $endStamp, array $allowedDaysOfTheWeek = [], array $excludedDates = []): int
    {
        // Convert $allowedDaysOfTheWeek to a set-like associative array
        $allowedDaysSet = array_flip($allowedDaysOfTheWeek);

        // Convert $excludedDates to a set-like associative array
        $excludedDatesSet = array_flip($excludedDates);

        $isFullWeek = count($allowedDaysOfTheWeek) === 7;
        $hasExclusions = !empty($excludedDates);
        $totalDays = (int) ceil(($endStamp - $startStamp) / self::SECONDS_IN_ONE_DAY);

        if ($isFullWeek && !$hasExclusions) {
            return $totalDays;
        }

        $currentStamp = $startStamp;
        while ($currentStamp < $endStamp) {
            /*
             * The date() call is expensive when done multiple times. We can now do that conversion once. Then grab the formatted values after that.
             *
             * @var string $dateFormats
             */
            if (!isset(self::$cache[$currentStamp])) {
                $dateFormats = date('w,Y-m-d,*-m-d', $currentStamp);

                /**
                 * @var string $weekDay the number (in string form) representing the current day
                 * @var string $specificDate Y-m-d format of the current day
                 * @var string $wildcardDate *-m-d format of the current day, to allow for exclusions that repeat every year
                 */
                [$weekDay, $specificDate, $wildcardDate] = explode(',', $dateFormats);

                self::$cache[$currentStamp] = [$weekDay, $specificDate, $wildcardDate];
            } else {
                [$weekDay, $specificDate, $wildcardDate] = self::$cache[$currentStamp];
            }

            // Reduce the total days if the current date in the loop is to be excluded.
            if (
                !isset($allowedDaysSet[(int) $weekDay]) // Day is skipped because it's not in the $allowedDaysSet.
                || isset($excludedDatesSet[$specificDate]) // Day is skipped because it's a specifically excluded date.
                || isset($excludedDatesSet[$wildcardDate]) // Day is skipped because the month/day is excluded every year.
            ) {
                --$totalDays;
            }

            $currentStamp += self::SECONDS_IN_ONE_DAY;
        }

        return $totalDays;
    }
}
