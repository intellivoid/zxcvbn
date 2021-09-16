<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Zxcvbn\Abstracts;

    use Zxcvbn\Interfaces\MatchInterface;

    abstract class BaseMatch implements MatchInterface
    {
        public $password;
        public $begin;
        public $end;
        public $token;
        public $pattern;

        /**
         * @param $password
         * @param $begin
         * @param $end
         * @param $token
         */
        public function __construct($password, $begin, $end, $token)
        {
            $this->password = $password;
            $this->begin = $begin;
            $this->end = $end;
            $this->token = $token;
        }

        /**
         * Find all occurrences of regular expression in a string.
         *
         * @param string $string
         * @param string $regex
         * @param int $offset
         * @return array
         * @noinspection PhpUnused
         */
        public static function findAll(string $string, string $regex, int $offset = 0): array
        {
            $charsBeforeOffset = mb_substr($string, 0, $offset);
            $byteOffset = strlen($charsBeforeOffset);

            $count = preg_match_all($regex, $string, $matches, PREG_SET_ORDER, $byteOffset);

            if (!$count)
            {
                return [];
            }

            $groups = [];
            foreach ($matches as $group)
            {
                $captureBegin = 0;
                $match = array_shift($group);
                $matchBegin = mb_strpos($string, $match, $offset);
                $captures = [
                    [
                        'begin' => $matchBegin,
                        'end' => $matchBegin + mb_strlen($match) - 1,
                        'token' => $match,
                    ],
                ];
                foreach ($group as $capture)
                {
                    $captureBegin = mb_strpos($match, $capture, $captureBegin);
                    $captures[] = [
                        'begin' => $matchBegin + $captureBegin,
                        'end' => $matchBegin + $captureBegin + mb_strlen($capture) - 1,
                        'token' => $capture,
                    ];
                }
                $groups[] = $captures;
                $offset += mb_strlen($match) - 1;
            }
            return $groups;
        }

        /**
         * Calculate binomial coefficient (n choose k).
         *
         * @param $n
         * @param $k
         * @return int
         * @noinspection PhpUnused
         * @noinspection SpellCheckingInspection
         */
        public static function binom($n, $k): int
        {
            $j = $res = 1;

            if ($k < 0 || $k > $n) {
                return 0;
            }
            if (($n - $k) < $k) {
                $k = $n - $k;
            }
            while ($j <= $k) {
                $res *= $n--;
                $res /= $j++;
            }

            return $res;
        }

        /**
         * @return mixed
         */
        abstract protected function getRawGuesses();

        /**
         * @return int|mixed
         */
        public function getGuesses()
        {
            return max($this->getRawGuesses(), $this->getMinimumGuesses());
        }

        /**
         * @return int
         */
        protected function getMinimumGuesses(): int
        {
            if (mb_strlen($this->token) < mb_strlen($this->password))
            {
                if (mb_strlen($this->token) === 1)
                {
                    return Score::MIN_SUBMATCH_GUESSES_SINGLE_CHAR;
                }
                else
                {
                    return Score::MIN_SUBMATCH_GUESSES_MULTI_CHAR;
                }
            }
            return 0;
        }

        /**
         * @return float
         */
        public function getGuessesLog10(): float
        {
            return log10($this->getGuesses());
        }
    }
