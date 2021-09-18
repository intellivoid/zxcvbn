<?php

    namespace Zxcvbn;

    use TimerLib\Timer;
    use Zxcvbn\Classes\FeedbackUtilities;
    use Zxcvbn\Classes\Matcher;
    use Zxcvbn\Classes\Scorer;
    use Zxcvbn\Classes\TimeEstimator;
    use Zxcvbn\Objects\Feedback;
    use Zxcvbn\Objects\PasswordStrength;

    class zxcvbn
    {
        /**
         * @var Matcher
         */
        protected $matcher;

        /**
         * @var Scorer
         */
        protected $scorer;

        /**
         * @var TimeEstimator
         */
        protected $timeEstimator;

        /**
         * @var FeedbackUtilities
         */
        protected $feedback;

        public function __construct()
        {
            $this->matcher = new Matcher();
            $this->scorer = new Scorer();
            $this->timeEstimator = new TimeEstimator();
            $this->feedback = new FeedbackUtilities();
        }

        /**
         * @param string $className
         * @return $this
         * @noinspection PhpMissingReturnTypeInspection
         */
        public function addMatcher(string $className)
        {
            $this->matcher->addMatcher($className);
            return $this;
        }

        /**
         * Calculate password strength via non-overlapping minimum entropy patterns.
         *
         * @param string $password Password to measure
         * @param array $userInputs Optional user inputs
         *
         * @return PasswordStrength Strength result array with keys:
         * @noinspection PhpUnused
         */
        public function passwordStrength(string $password, array $userInputs = []): PasswordStrength
        {
            $timer = new Timer();
            $timer->start();

            $sanitizedInputs = array_map(
                function ($input)
                {
                    return mb_strtolower((string) $input);
                },
                $userInputs
            );

            $matches = $this->matcher->getMatches($password, $sanitizedInputs);

            $return_results = new PasswordStrength();
            $return_results->GuessableMatchSequence = $this->scorer->getMostGuessableMatchSequence($password, $matches);
            $return_results->EstimatedAttackTimes = $this->timeEstimator->estimateAttackTimes($return_results->GuessableMatchSequence->Guesses);
            $return_results->Feedback = $this->feedback->getFeedback(
                $return_results->EstimatedAttackTimes->Score, $return_results->GuessableMatchSequence->Sequence
            );
            $return_results->Timelapse = $timer->stop()->getMilliseconds();

            return $return_results;
        }
    }