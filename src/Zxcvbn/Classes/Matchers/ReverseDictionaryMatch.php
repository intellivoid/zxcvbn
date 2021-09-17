<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Zxcvbn\Classes\Matchers;

    use Zxcvbn\Classes\Matcher;
    use Zxcvbn\Classes\Utilities;
    use Zxcvbn\Objects\Feedback;

    class ReverseDictionaryMatch extends DictionaryMatch
    {
        /**
         * Whether the matched word was reversed in the token.
         * @var bool
         */
        public $reversed = true;

        /**
         * Match occurrences of reversed dictionary words in password.
         *
         * @param $password
         * @param array $userInputs
         * @param array $rankedDictionaries
         * @return ReverseDictionaryMatch[]
         */
        public static function match($password, array $userInputs = [], array $rankedDictionaries = []): array
        {
            /** @var ReverseDictionaryMatch[] $matches */
            $matches = parent::match(self::mbStrRev($password), $userInputs, $rankedDictionaries);
            foreach ($matches as $match)
            {
                $tempBegin = $match->begin;

                // Change the token, password and [begin, end] values to match the original password
                $match->token = self::mbStrRev($match->token);
                $match->password = self::mbStrRev($match->password);
                $match->begin = mb_strlen($password) - 1 - $match->end;
                $match->end = mb_strlen($password) - 1 - $tempBegin;
            }
            Utilities::usort($matches, [Matcher::class, 'compareMatches']);
            return $matches;
        }

        /**
         * @return float|int
         */
        protected function getRawGuesses(): ?int
        {
            return parent::getRawGuesses() * 2;
        }

        /**
         * @param $isSoleMatch
         * @return Feedback
         */
        public function getFeedback($isSoleMatch): Feedback
        {
            $feedback = parent::getFeedback($isSoleMatch);

            if (mb_strlen($this->token) >= 4)
            {
                $feedback->Suggestions[] = "Reversed words aren't much harder to guess";
            }

            return $feedback;
        }

        /**
         * @param $string
         * @param null $encoding
         * @return string
         */
        public static function mbStrRev($string, $encoding = null): string
        {
            if ($encoding === null) {
                $encoding = mb_detect_encoding($string) ?: 'UTF-8';
            }
            $length = mb_strlen($string, $encoding);
            $reversed = '';
            while ($length-- > 0) {
                $reversed .= mb_substr($string, $length, 1, $encoding);
            }

            return $reversed;
        }
    }
