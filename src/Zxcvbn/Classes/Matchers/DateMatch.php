<?php

    namespace Zxcvbn\Classes\Matchers;

    use Zxcvbn\Abstracts\BaseMatch;
    use Zxcvbn\Classes\Matcher;
    use Zxcvbn\Classes\Utilities;
    use Zxcvbn\Objects\Feedback;

    class DateMatch extends BaseMatch
    {
        public const NUM_YEARS = 119; // Years match against 1900 - 2019
        public const NUM_MONTHS = 12;
        public const NUM_DAYS = 31;
        public const MIN_YEAR = 1000;
        public const MAX_YEAR = 2050;
        public const MIN_YEAR_SPACE = 20;
        public $pattern = 'date';

        private static $DATE_SPLITS = [
            4 => [         # For length-4 strings, eg 1191 or 9111, two ways to split:
                [1, 2],    # 1 1 91 (2nd split starts at index 1, 3rd at index 2)
                [2, 3],    # 91 1 1
            ],
            5 => [
                [1, 3],    # 1 11 91
                [2, 3]     # 11 1 91
            ],
            6 => [
                [1, 2],    # 1 1 1991
                [2, 4],    # 11 11 91
                [4, 5],    # 1991 1 1
            ],
            7 => [
                [1, 3],    # 1 11 1991
                [2, 3],    # 11 1 1991
                [4, 5],    # 1991 1 11
                [4, 6],    # 1991 11 1
            ],
            8 => [
                [2, 4],    # 11 11 1991
                [4, 6],    # 1991 11 11
            ],
        ];
        protected const DATE_NO_SEPARATOR = '/^\d{4,8}$/u';
        protected const DATE_WITH_SEPARATOR = '/^(\d{1,4})([\s\/\\\\_.-])(\d{1,2})\2(\d{1,4})$/u';
        public $day;
        public $month;
        public $year;
        public $separator;

        /**
         * Match occurrences of dates in a password
         *
         * @param string $password
         * @param array $userInputs
         * @return array
         */
        public static function match(string $password, array $userInputs = [])
        {
            $matches = [];

            $dates = static::removeRedundantMatches(array_merge(
                static::datesWithoutSeparators($password),
                static::datesWithSeparators($password)
            ));

            foreach ($dates as $date)
            {
                $matches[] = new static($password, $date['begin'], $date['end'], $date['token'], $date);
            }

            Utilities::usort($matches, [Matcher::class, 'compareMatches']);
            return $matches;
        }

        /**
         * @param $isSoleMatch
         * @return Feedback
         * @noinspection PhpUnused
         */
        public function getFeedback($isSoleMatch): Feedback
        {
            return new Feedback('Dates are often easy to guess', [
                'Avoid dates and years that are associated with you'
            ]);
        }

        /**
         * @param string $password
         * @param int $begin
         * @param int $end
         * @param string $token
         * @param array $params An array with keys: [day, month, year, separator].
         */
        public function __construct($password, $begin, $end, $token, array $params)
        {
            parent::__construct($password, $begin, $end, $token);
            $this->day = $params['day'];
            $this->month = $params['month'];
            $this->year = $params['year'];
            $this->separator = $params['separator'];
        }

        /**
         * Find dates with separators in a password.
         *
         * @param string $password
         * @return array
         */
        protected static function datesWithSeparators(string $password): array
        {
            $matches = [];
            $length = mb_strlen($password);

            for ($begin = 0; $begin < $length - 5; $begin++)
            {
                for ($end = $begin + 5; $end - $begin < 10 && $end < $length; $end++)
                {
                    $token = mb_substr($password, $begin, $end - $begin + 1);

                    if (!preg_match(static::DATE_WITH_SEPARATOR, $token, $captures))
                    {
                        continue;
                    }

                    $date = static::checkDate([
                        (int) $captures[1],
                        (int) $captures[3],
                        (int) $captures[4]
                    ]);

                    if ($date === false)
                    {
                        continue;
                    }

                    $matches[] = [
                        'begin' => $begin,
                        'end' => $end,
                        'token' => $token,
                        'separator' => $captures[2],
                        'day' => $date['day'],
                        'month' => $date['month'],
                        'year' => $date['year'],
                    ];
                }
            }

            return $matches;
        }

        /**
         * Find dates without separators in a password.
         *
         * @param string $password
         * @return array
         */
        protected static function datesWithoutSeparators(string $password): array
        {
            $matches = [];
            $length = mb_strlen($password);

            // dates without separators are between length 4 '1191' and 8 '11111991'
            for ($begin = 0; $begin < $length - 3; $begin++)
            {
                for ($end = $begin + 3; $end - $begin < 8 && $end < $length; $end++)
                {
                    $token = mb_substr($password, $begin, $end - $begin + 1);

                    if (!preg_match(static::DATE_NO_SEPARATOR, $token))
                    {
                        continue;
                    }

                    $candidates = [];
                    $possibleSplits = static::$DATE_SPLITS[mb_strlen($token)];

                    foreach ($possibleSplits as $splitPositions)
                    {
                        $day = mb_substr($token, 0, $splitPositions[0]);
                        $month = mb_substr($token, $splitPositions[0], $splitPositions[1] - $splitPositions[0]);
                        $year = mb_substr($token, $splitPositions[1]);

                        $date = static::checkDate([$day, $month, $year]);

                        if ($date !== false)
                        {
                            $candidates[] = $date;
                        }
                    }

                    if (empty($candidates))
                    {
                        continue;
                    }

                    $bestCandidate = $candidates[0];
                    $minDistance = self::getDistanceForMatchCandidate($bestCandidate);

                    foreach ($candidates as $candidate)
                    {
                        $distance = self::getDistanceForMatchCandidate($candidate);
                        if ($distance < $minDistance)
                        {
                            $bestCandidate = $candidate;
                            $minDistance = $distance;
                        }
                    }

                    $day = $bestCandidate['day'];
                    $month = $bestCandidate['month'];
                    $year = $bestCandidate['year'];

                    $matches[] = [
                        'begin' => $begin,
                        'end' => $end,
                        'token' => $token,
                        'separator' => '',
                        'day' => $day,
                        'month' => $month,
                        'year' => $year
                    ];
                }
            }

            return $matches;
        }

        /**
         * @param array $candidate
         * @return int Returns the number of years between the detected year and the current year for a candidate.
         */
        protected static function getDistanceForMatchCandidate(array $candidate): int
        {
            return abs((int)$candidate['year'] - static::getReferenceYear());
        }

        /**
         * @return int
         */
        public static function getReferenceYear(): int
        {
            return (int)date('Y');
        }

        /**
         * @param int[] $ints Three numbers in an array representing day, month and year (not necessarily in that order).
         * @return array|bool Returns an associative array containing 'day', 'month' and 'year' keys, or false if the
         *                    provided date array is invalid.
         */
        protected static function checkDate(array $ints)
        {
            if ($ints[1] > 31 || $ints[1] <= 0)
            {
                return false;
            }

            $invalidYear = count(array_filter($ints, function ($int)
            {
                return ($int >= 100 && $int < static::MIN_YEAR)
                    || ($int > static::MAX_YEAR);
            }));

            if ($invalidYear > 0)
            {
                return false;
            }

            $over12 = count(array_filter($ints, function ($int)
            {
                return $int > 12;
            }));

            $over31 = count(array_filter($ints, function ($int)
            {
                return $int > 31;
            }));

            $under1 = count(array_filter($ints, function ($int)
            {
                return $int <= 0;
            }));

            if ($over31 >= 2 || $over12 == 3 || $under1 >= 2)
            {
                return false;
            }

            $possibleYearSplits = [
                [$ints[2], [$ints[0], $ints[1]]], // year last
                [$ints[0], [$ints[1], $ints[2]]], // year first
            ];

            foreach ($possibleYearSplits as list($year, $rest))
            {
                if ($year >= static::MIN_YEAR && $year <= static::MAX_YEAR)
                {
                    if ($dm = static::mapIntsToDayMonth($rest))
                    {
                        return [
                            'year'  => $year,
                            'month' => $dm['month'],
                            'day'   => $dm['day'],
                        ];
                    }
                    # for a candidate that includes a four-digit year,
                    # when the remaining ints don't match to a day and month,
                    # it is not a date.
                    return false;
                }
            }

            foreach ($possibleYearSplits as list($year, $rest))
            {
                if ($dm = static::mapIntsToDayMonth($rest))
                {
                    return [
                        'year'  => static::twoToFourDigitYear($year),
                        'month' => $dm['month'],
                        'day'   => $dm['day'],
                    ];
                }
            }

            return false;
        }

        /**
         * @param int[] $ints Two numbers in an array representing day and month (not necessarily in that order).
         * @return array|bool Returns an associative array containing 'day' and 'month' keys, or false if any combination
         *                    of the two numbers does not match a day and month.
         */
        protected static function mapIntsToDayMonth(array $ints)
        {
            foreach ([$ints, array_reverse($ints)] as list($d, $m))
            {
                if ($d >= 1 && $d <= 31 && $m >= 1 && $m <= 12)
                {
                    return [
                        'day'   => $d,
                        'month' => $m
                    ];
                }
            }

            return false;
        }

        /**
         * @param int $year A two-digit number representing a year.
         * @return int Returns the most likely four digit year for the provided number.
         */
        protected static function twoToFourDigitYear(int $year)
        {
            if ($year > 99) {
                return $year;
            }

            if ($year > 50) {
                // 87 -> 1987
                return $year + 1900;
            }

            // 15 -> 2015
            return $year + 2000;
        }

        /**
         * Removes date matches that are strict substrings of others.
         *
         * @param array $matches An array of matches (not Match objects)
         * @return array The provided array of matches, but with matches that are strict substrings of others removed.
         */
        protected static function removeRedundantMatches(array $matches): array
        {
            return array_filter($matches, function ($match) use ($matches)
            {
                foreach ($matches as $otherMatch)
                {
                    if ($match === $otherMatch)
                    {
                        continue;
                    }

                    if ($otherMatch['begin'] <= $match['begin'] && $otherMatch['end'] >= $match['end'])
                    {
                        return false;
                    }
                }

                return true;
            });
        }

        /**
         * @return float|int
         */
        protected function getRawGuesses()
        {
            // base guesses: (year distance from REFERENCE_YEAR) * num_days * num_years
            $yearSpace = max(abs($this->year - static::getReferenceYear()), static::MIN_YEAR_SPACE);
            $guesses = $yearSpace * 365;

            // add factor of 4 for separator selection (one of ~4 choices)
            if ($this->separator)
            {
                $guesses *= 4;
            }

            return $guesses;
        }
    }