<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace Zxcvbn\Classes\Matchers;


    use Zxcvbn\Abstracts\BaseMatch;
    use Zxcvbn\Abstracts\Score;

    class Bruteforce extends BaseMatch
    {
        public const BRUTEFORCE_CARDINALITY = 10;
        public $pattern = 'bruteforce';

        /**
         * @param string $password
         * @param array $userInputs
         * @return Bruteforce[]
         */
        public static function match($password, array $userInputs = []): array
        {
            // Matches entire string.
            $match = new static($password, 0, mb_strlen($password) - 1, $password);
            return [$match];
        }

        /**
         * @param bool $isSoleMatch
         * @return array
         */
        public function getFeedback($isSoleMatch): array
        {
            return [
                'warning' => "",
                'suggestions' => [
                ]
            ];
        }

        /**
         * @return float|mixed
         */
        public function getRawGuesses()
        {
            $guesses = pow(self::BRUTEFORCE_CARDINALITY, mb_strlen($this->token));
            if ($guesses === INF)
            {
                return defined('PHP_FLOAT_MAX') ? PHP_FLOAT_MAX : 1e308;
            }

            if (mb_strlen($this->token) === 1)
            {
                $minGuesses = Score::MIN_SUBMATCH_GUESSES_SINGLE_CHAR + 1;
            }
            else
            {
                $minGuesses = Score::MIN_SUBMATCH_GUESSES_MULTI_CHAR + 1;
            }

            return max($guesses, $minGuesses);
        }
    }
