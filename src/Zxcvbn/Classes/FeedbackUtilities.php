<?php

    namespace Zxcvbn\Classes;

    use Zxcvbn\Interfaces\MatchInterface;
    use Zxcvbn\Objects\Feedback;

    class FeedbackUtilities
    {
        /**
         * @param int $score
         * @param MatchInterface[] $sequence
         * @return Feedback
         */
        public function getFeedback(int $score, array $sequence): Feedback
        {
            // starting feedback
            if (count($sequence) === 0)
            {
                return new Feedback('', [
                    "Use a few words, avoid common phrases",
                    "No need for symbols, digits, or uppercase letters",
                ]);
            }

            // no feedback if score is good or great.
            if ($score > 2)
            {
                return new Feedback();
            }

            // tie feedback to the longest match for longer sequences
            $longestMatch = $sequence[0];
            foreach (array_slice($sequence, 1) as $match)
            {
                if (mb_strlen($match->token) > mb_strlen($longestMatch->token))
                {
                    $longestMatch = $match;
                }
            }

            /** @var Feedback $feedback */
            $feedback = $longestMatch->getFeedback(count($sequence) === 1);
            $extraFeedback = 'Add another word or two. Uncommon words are better.';
            array_unshift($feedback->Suggestions, $extraFeedback);
            return $feedback;
        }
    }
