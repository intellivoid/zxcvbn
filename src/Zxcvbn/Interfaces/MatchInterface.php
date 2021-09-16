<?php

    namespace Zxcvbn\Interfaces;

    interface MatchInterface
    {
        /**
         * Match this password.
         *
         * @param string $password   Password to check for match
         * @param array  $userInputs Array of values related to the user (optional)
         * @code array('Alice Smith')
         * @endcode
         *
         * @return array Array of Match objects
         */
        public static function match(string $password, array $userInputs = []): array;

        /**
         * @return integer
         */
        public function getGuesses(): int;

        /**
         * @return float
         */
        public function getGuessesLog10(): float;
    }
