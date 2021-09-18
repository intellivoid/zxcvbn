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
         * @return string|null
         */
        public function getWarning(): ?string
        {
            if(strlen($this->Warning) == 0)
                return null;
            return $this->Warning;
        }

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
            $warning = null;
            if(strlen($this->Warning) > 0)
                $warning = $this->Warning;

            return [
                'warning' => $warning,
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