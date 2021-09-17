<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Zxcvbn\Classes\Matchers;

    use Zxcvbn\Abstracts\BaseMatch;
    use Zxcvbn\Classes\Matcher;
    use Zxcvbn\Classes\Utilities;
    use Zxcvbn\Objects\Feedback;

    class YearMatch extends BaseMatch
    {

        public const NUM_YEARS = 119;
        public $pattern = 'regex';
        public $regexName = 'recent_year';

        /**
         * Match occurrences of years in a password
         *
         * @param string $password
         * @param array $userInputs
         * @return YearMatch[]
         */
        public static function match(string $password, array $userInputs = []): array
        {
            $matches = [];
            $groups = static::findAll($password, "/(19\d\d|200\d|201\d)/u");

            foreach ($groups as $captures)
            {
                $matches[] = new static($password, $captures[1]['begin'], $captures[1]['end'], $captures[1]['token']);
            }

            Utilities::usort($matches, [Matcher::class, 'compareMatches']);
            return $matches;
        }

        /**
         * @param $isSoleMatch
         * @return Feedback
         * @noinspection PhpUnusedParameterInspection
         */
        public function getFeedback($isSoleMatch): Feedback
        {
            return new Feedback('Recent years are easy to guess', [
                'Avoid recent years',
                'Avoid years that are associated with you',
            ]);
        }

        /**
         * @return mixed
         */
        protected function getRawGuesses()
        {
            $yearSpace = abs((int)$this->token - DateMatch::getReferenceYear());
            return max($yearSpace, DateMatch::MIN_YEAR_SPACE);
        }
    }
