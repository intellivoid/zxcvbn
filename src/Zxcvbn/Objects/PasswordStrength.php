<?php

    namespace Zxcvbn\Objects;

    class PasswordStrength
    {
        /**
         * @var GuessableMatchSequence
         */
        public $GuessableMatchSequence;

        /**
         * @var EstimatedAttackTimes
         */
        public $EstimatedAttackTimes;

        /**
         * @var Feedback
         */
        public $Feedback;

        /**
         * @var int
         */
        public $Timelapse;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'guessable_match_sequence' => $this->GuessableMatchSequence->toArray(),
                'estimated_attack_times' => $this->EstimatedAttackTimes->toArray(),
                'feedback' => $this->Feedback->toArray(),
                'timelapse' => $this->Timelapse
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return PasswordStrength
         */
        public static function fromArray(array $data): PasswordStrength
        {
            $PasswordStrengthObject = new PasswordStrength();

            if(isset($data['guessable_match_sequence']))
                $PasswordStrengthObject->GuessableMatchSequence = GuessableMatchSequence::fromArray($data['guessable_match_sequence']);

            if(isset($data['estimated_attack_times']))
                $PasswordStrengthObject->EstimatedAttackTimes = EstimatedAttackTimes::fromArray($data['estimated_attack_times']);

            if(isset($data['feedback']))
                $PasswordStrengthObject->Feedback = Feedback::fromArray($data['feedback']);

            if(isset($data['timestamp']))
                $PasswordStrengthObject->Timelapse = $data['timestamp'];

            return $PasswordStrengthObject;
        }
    }