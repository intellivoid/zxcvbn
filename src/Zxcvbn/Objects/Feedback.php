<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Zxcvbn\Objects;

    class Feedback
    {
        /**
         * The main warning of the feedback
         *
         * @var string
         */
        public $Warning;

        /**
         * Potential suggestions to correct the warning
         *
         * @var string[]
         */
        public $Suggestions;

        /**
         * @param string $warning
         * @param array $Suggestions
         */
        public function __construct(string $warning='', array $Suggestions=[])
        {
            $this->Warning = $warning;
            $this->Suggestions = $Suggestions;
        }

        /**
         * @return array
         * @noinspection PhpUnused
         */
        public function toArray(): array
        {
            return [
                'warning' => $this->Warning,
                'suggestions' => $this->Suggestions
            ];
        }

        /**
         * @param array $data
         * @return Feedback
         * @noinspection PhpUnused
         */
        public static function fromArray(array $data): Feedback
        {
            $FeedbackObject = new Feedback();

            if(isset($data['warning']))
                $FeedbackObject->Warning = $data['warning'];

            if(isset($data['suggestions']))
                $FeedbackObject->Suggestions = $data['suggestions'];

            return $FeedbackObject;
        }
    }