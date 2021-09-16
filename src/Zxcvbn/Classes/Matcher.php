<?php

    namespace Zxcvbn\Classes;


    use Zxcvbn\Interfaces\MatchInterface;

    class Matcher
    {
        private const DEFAULT_MATCHERS = [];

        private $additionalMatchers = [];

        /**
         * Get matches for a password.
         *
         * @param string $password  Password string to match
         * @param array $userInputs Array of values related to the user (optional)
         * @code array('Alice Smith')
         * @endcode
         *
         * @return MatchInterface[] Array of Match objects.
         *
         * @see  zxcvbn/src/matching.coffee::omnimatch
         */
        public function getMatches($password, array $userInputs = [])
        {
            $matches = [];
            foreach ($this->getMatchers() as $matcher) {
                $matched = $matcher::match($password, $userInputs);
                if (is_array($matched) && !empty($matched)) {
                    $matches[] = $matched;
                }
            }

            $matches = array_merge([], ...$matches);
            self::usortStable($matches, [$this, 'compareMatches']);

            return $matches;
        }

        /**
         * Adds a custom matching class
         *
         * @param string $className
         * @return $this
         */
        public function addMatcher(string $className)
        {
            if (!is_a($className, MatchInterface::class, true)) {
                throw new \InvalidArgumentException(sprintf('Matcher class must implement %s', MatchInterface::class));
            }

            $this->additionalMatchers[$className] = $className;

            return $this;
        }


        public static function compareMatches(BaseMatch $a, BaseMatch $b)
        {
            $beginDiff = $a->begin - $b->begin;
            if ($beginDiff) {
                return $beginDiff;
            }
            return $a->end - $b->end;
        }

        /**
         * Load available Match objects to match against a password.
         *
         * @return array Array of classes implementing MatchInterface
         */
        protected function getMatchers()
        {
            return array_merge(
                self::DEFAULT_MATCHERS,
                array_values($this->additionalMatchers)
            );
        }
    }
