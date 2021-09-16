<?php

    namespace Zxcvbn\Objects;

    class GuessableMatchSequence
    {
        public $Password;

        public $Guesses;

        public $GuessesLog10;

        public $Sequence;

        /**
         * @return array
         */
        public function toArray(): array
        {
            return [
                'password' => $this->Password,
                'guesses' => $this->Guesses,
                'guesses_log10' => $this->GuessesLog10,
                'sequence' => $this->Sequence
            ];
        }
    }