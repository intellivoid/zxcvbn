<?php

    /** @noinspection PhpMissingFieldTypeInspection */

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

        /**
         * Constructs an object from an array representation
         *
         * @param array $data
         * @return GuessableMatchSequence
         */
        public static function fromArray(array $data): GuessableMatchSequence
        {
            $GuessableMatchSequence = new GuessableMatchSequence();

            if(isset($data['password']))
                $GuessableMatchSequence->Password = $data['password'];

            if(isset($data['guesses']))
                $GuessableMatchSequence->Guesses = $data['guesses'];

            if(isset($data['guesses_log10']))
                $GuessableMatchSequence->GuessesLog10 = $data['guesses_log10'];

            if(isset($data['sequence']))
                $GuessableMatchSequence->Sequence = $data['sequence'];

            return $GuessableMatchSequence;
        }
    }