<?php

    namespace Zxcvbn\Objects;

    use Zxcvbn\Objects\EstimatedAttackTimes\CrackTimes;

    class EstimatedAttackTimes
    {
        /**
         * The estimated times for how long this password could be cracked
         *
         * @var CrackTimes
         */
        public $CrackTimesSeconds;

        /**
         * A display string of the crack times
         *
         * @var string
         */
        public $CrackTimesDisplay;

        /**
         * The strength score of the password
         *
         * @var int
         */
        public $Score;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'crack_times_seconds' => $this->CrackTimesSeconds->toArray(),
                'crack_times_display' => $this->CrackTimesDisplay,
                'score' => $this->Score
            ];
        }

        public static function fromArray(array $data): EstimatedAttackTimes
        {
            $EstimatedAttackTimesObject = new EstimatedAttackTimes();

            if(isset($data['crack_times_seconds']))
                $EstimatedAttackTimesObject->CrackTimesSeconds = CrackTimes::fromArray($data['crack_times_seconds']);

            if(isset($data['crack_times_display']))
                $EstimatedAttackTimesObject->CrackTimesDisplay = $data['crack_times_display'];

            if(isset($data['score']))
                $EstimatedAttackTimesObject->Score = $data['score'];

            return $EstimatedAttackTimesObject;
        }
    }