# php-daydifference

A simple php tool to help you calculate the number of days between two given dates, while also giving you the ability to factor in excluded dates.

Excluded dates can be:
- a day of the week (ie: Weekends)
- a specific date (ie: 2022-12-25)
- a repeating date (ie: *-01-01)

## Example usage

A simple use case of this would be to determine how many work days there are for a given date range.

```php
$startDate = new DateTime('2022-01-01');
$endDate = new DateTime('2022-02-01');
$workDays = [1, 2, 3, 4, 5]; // 0 = sunday, 1 = monday, etc.
$holidays = [
  '*-01-01',
  '2022-01-03',
];

$init = new DayDifference($startDate, $endDate, $workDays, $holidays);
$dayDifference = $init->difference();
```

## Contributing

This package doesn't get much activity so I don't have CI setup for this. Please ensure all commits pass:

- `composer standards`
- `composer static`
- `composer test`

You can run `composer standards:fix` to automatically adhere the code to the standard.
