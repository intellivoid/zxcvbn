<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Zxcvbn\Classes;

    use InvalidArgumentException;
    use Zxcvbn\Abstracts\BaseMatch;
    use Zxcvbn\Classes\Matchers\DateMatch;
    use Zxcvbn\Classes\Matchers\DictionaryMatch;
    use Zxcvbn\Classes\Matchers\L33tMatch;
    use Zxcvbn\Classes\Matchers\RepeatMatch;
    use Zxcvbn\Classes\Matchers\ReverseDictionaryMatch;
    use Zxcvbn\Classes\Matchers\SequenceMatch;
    use Zxcvbn\Classes\Matchers\SpatialMatch;
    use Zxcvbn\Classes\Matchers\YearMatch;
    use Zxcvbn\Interfaces\MatchInterface;

    class Matcher
    {
        private const DEFAULT_MATCHERS = [
            DateMatch::class,
            DictionaryMatch::class,
            ReverseDictionaryMatch::class,
            L33tMatch::class,
            RepeatMatch::class,
            SequenceMatch::class,
            SpatialMatch::class,
            YearMatch::class
        ];

        private $additionalMatchers = [];

        /**
         * Get matches for a password.
         *
         * @param string $password  Password string to match
         * @param array $userInputs Array of values related to the user (optional)
         * @return MatchInterface[] Array of Match objects.
         * @noinspection PhpUnused
         */
        public function getMatches(string $password, array $userInputs = []): array
        {
            $matches = [];
            foreach ($this->getMatchers() as $matcher)
            {
                $matched = $matcher::match($password, $userInputs);
                if (is_array($matched) && !empty($matched))
                {
                    $matches[] = $matched;
                }
            }

            $matches = array_merge([], ...$matches);
            Utilities::usort($matches, [$this, 'compareMatches']);

            return $matches;
        }

        /**
         * Adds a custom matching class
         *
         * @param string $className
         * @return $this
         * @noinspection PhpUnused
         */
        public function addMatcher(string $className): Matcher
        {
            if (!is_a($className, MatchInterface::class, true)) {
                throw new InvalidArgumentException(sprintf('Matcher class must implement %s', MatchInterface::class));
            }

            $this->additionalMatchers[$className] = $className;

            return $this;
        }

        /**
         * @param BaseMatch $a
         * @param BaseMatch $b
         * @return mixed
         */
        public static function compareMatches(BaseMatch $a, BaseMatch $b)
        {
            $beginDiff = $a->begin - $b->begin;

            if ($beginDiff)
            {
                return $beginDiff;
            }

            return $a->end - $b->end;
        }

        /**
         * Load available Match objects to match against a password.
         *
         * @return array Array of classes implementing MatchInterface
         */
        protected function getMatchers(): array
        {
            return array_merge(
                self::DEFAULT_MATCHERS,
                array_values($this->additionalMatchers)
            );
        }
    }
