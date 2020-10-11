<?php

use johncorrelli\DayDifference;

describe('DayDifference', function() {
    function getDiff(DateTimeInterface $startDate, DateTimeInterface $endDate, array $daysOfTheWeek, array $excludedDays)
    {
        $diff = new DayDifference($startDate, $endDate, $daysOfTheWeek, $excludedDays);

        return $diff->difference();
    }

    it('should return 0 when start and end date are the same', function() {
        $startDate = new DateTime();

        for ($i = 1; $i < 10; $i++) {
            expect(getDiff($startDate, $startDate, [0, 1, 2, 3, 4, 5, 6], []))->toBe(0);
        }
    });

    it('should handle negative dates', function() {
        $startDate = new DateTime();
        $endDate = new DateTime("-1 day");

        expect(getDiff($startDate, $endDate, [0, 1, 2, 3, 4, 5, 6], []))->toBe(-1);
    });

    it('should handle relative dates without excluded dates', function() {
        $startDate = new DateTime();

        for ($i = 1; $i < 10; $i++) {
            expect(getDiff($startDate, new DateTime("{$i} day"), [0, 1, 2, 3, 4, 5, 6], []))->toBe($i);
        }
    });

    it('should handle skipping specific days of the week', function() {
        $startDate = new DateTime('2020-01-06');
        $endDate = new DateTime('2020-01-13');

        expect(getDiff($startDate, $endDate, [   1, 2, 3, 4, 5, 6], []))->toBe(6);
        expect(getDiff($startDate, $endDate, [0,    2, 3, 4, 5, 6], []))->toBe(6);
        expect(getDiff($startDate, $endDate, [0, 1,    3, 4, 5, 6], []))->toBe(6);
        expect(getDiff($startDate, $endDate, [0, 1, 2,    4, 5, 6], []))->toBe(6);
        expect(getDiff($startDate, $endDate, [0, 1, 2, 3,    5, 6], []))->toBe(6);
        expect(getDiff($startDate, $endDate, [0, 1, 2, 3, 4,    6], []))->toBe(6);
        expect(getDiff($startDate, $endDate, [0, 1, 2, 3, 4, 5   ], []))->toBe(6);

        expect(getDiff($startDate, $endDate, [      2, 3, 4, 5, 6], []))->toBe(5);
        expect(getDiff($startDate, $endDate, [0,       3, 4, 5, 6], []))->toBe(5);
        expect(getDiff($startDate, $endDate, [0, 1,       4, 5, 6], []))->toBe(5);
        expect(getDiff($startDate, $endDate, [0, 1, 2,       5, 6], []))->toBe(5);
        expect(getDiff($startDate, $endDate, [0, 1, 2, 3,       6], []))->toBe(5);
        expect(getDiff($startDate, $endDate, [0, 1, 2, 3, 4      ], []))->toBe(5);

        expect(getDiff($startDate, $endDate, [0], []))->toBe(1);
        expect(getDiff($startDate, $endDate, [1], []))->toBe(1);
        expect(getDiff($startDate, $endDate, [2], []))->toBe(1);
        expect(getDiff($startDate, $endDate, [3], []))->toBe(1);
        expect(getDiff($startDate, $endDate, [4], []))->toBe(1);
        expect(getDiff($startDate, $endDate, [5], []))->toBe(1);
        expect(getDiff($startDate, $endDate, [6], []))->toBe(1);
    });

    it('should handle skipping weekends', function() {
        $startDate = new DateTime('2020-01-06');
        $endDate = new DateTime('2020-01-13');

        expect(getDiff($startDate, $endDate, [1, 2, 3, 4, 5], []))->toBe(5);
    });

    it('should handle skipping specific dates', function() {
        $startDate = new DateTime('2020-01-06');
        $endDate = new DateTime('2020-01-13');

        expect(getDiff($startDate, $endDate, [1, 2, 3, 4, 5], ['2020-01-07']))->toBe(4);
    });

    it('should handle skipping yearly repeating dates', function() {
        $startDate = new DateTime('2020-01-06');
        $endDate = new DateTime('2020-01-13');

        expect(getDiff($startDate, $endDate, [1, 2, 3, 4, 5], ['*-01-07']))->toBe(4);
    });

    it('should handle combing all the things', function() {
        $startDate = new DateTime('2020-01-06');
        $endDate = new DateTime('2020-01-13');

        expect(getDiff($startDate, $endDate, [1, 2, 3, 4, 5], ['2020-01-08', '*-01-07']))->toBe(3);
    });

    it('should handle skipping the defined start and/or end dates', function() {
        $startDate = new DateTime('2020-01-06');
        $endDate = new DateTime('2020-01-13');

        expect(getDiff($startDate, $endDate, [1, 2, 3, 4, 5], ['2020-01-06', '*-01-13']))->toBe(4);
    });

    it('should handle many many tasks', function() {
        $startDate = new DateTime('2020-01-01');
        $endDate = new DateTime('2020-12-31');

        $holidays = [
            // repeating exclusions
            '*-01-01',
            '*-01-02',
            '*-07-04',
            '*-12-24',
            '*-12-25',

            // random specific exclusions
            '2020-04-01',
            '2020-06-22',
            '2020-10-10',
        ];

        for ($i = 1; $i <= 1000; $i++) {
            expect(getDiff($startDate, $endDate, [0, 1, 2, 3, 4, 5, 6], $holidays))->toBe(365 - count($holidays));
        }
    });
});
