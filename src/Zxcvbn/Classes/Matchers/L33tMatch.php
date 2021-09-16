<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Zxcvbn\Classes\Matchers;

    use Zxcvbn\Classes\Matcher;
    use Zxcvbn\Classes\Utilities;
    use Zxcvbn\Objects\Feedback;

    class L33tMatch extends DictionaryMatch
    {

        /**
         * An array of substitutions made to get from the token to the dictionary word.
         * @var array
         */
        public $sub = [];

        /**
         * A user-readable string that shows which substitutions were detected.
         * @var string
         */
        public $subDisplay;

        /**
         * Whether the token contained l33t substitutions.
         * @var bool
         */
        public $l33t = true;

        /**
         * Match occurrences of l33t words in password to dictionary words.
         *
         * @param string $password
         * @param array $userInputs
         * @param array $rankedDictionaries
         * @return array
         */
        public static function match(string $password, array $userInputs = [], array $rankedDictionaries = []): array
        {
            // Translate l33t password and dictionary match the translated password.
            $maps = array_filter(static::getL33tSubstitutions(static::getL33tSubtable($password)));
            if (empty($maps)) {
                return [];
            }

            $matches = [];
            if (!$rankedDictionaries) {
                $rankedDictionaries = static::getRankedDictionaries();
            }

            foreach ($maps as $map) {
                $translatedWord = static::translate($password, $map);

                /** @var L33tMatch[] $results */
                $results = parent::match($translatedWord, $userInputs, $rankedDictionaries);
                foreach ($results as $match) {
                    $token = mb_substr($password, $match->begin, $match->end - $match->begin + 1);

                    # only return the matches that contain an actual substitution
                    if (mb_strtolower($token) === $match->matchedWord) {
                        continue;
                    }

                    # filter single-character l33t matches to reduce noise.
                    # otherwise '1' matches 'i', '4' matches 'a', both very common English words
                    # with low dictionary rank.
                    if (mb_strlen($token) === 1) {
                        continue;
                    }

                    $display = [];
                    foreach ($map as $i => $t) {
                        if (mb_strpos($token, (string)$i) !== false) {
                            $match->sub[$i] = $t;
                            $display[] = "$i -> $t";
                        }
                    }
                    $match->token = $token;
                    $match->subDisplay = implode(', ', $display);

                    $matches[] = $match;
                }
            }

            Utilities::usort($matches, [Matcher::class, 'compareMatches']);
            return $matches;
        }

        /**
         * @param string $password
         * @param int $begin
         * @param int $end
         * @param string $token
         * @param array $params An array with keys: [sub, sub_display].
         */
        public function __construct($password, $begin, $end, $token, array $params = [])
        {
            parent::__construct($password, $begin, $end, $token, $params);
            if (!empty($params)) {
                $this->sub = $params['sub'] ?? null;
                $this->subDisplay = $params['sub_display'] ?? null;
            }
        }

        /**
         * @param $isSoleMatch
         * @return Feedback
         */
        public function getFeedback($isSoleMatch): Feedback
        {
            $feedback = parent::getFeedback($isSoleMatch);
            $feedback->Suggestions[] = "Predictable substitutions like '@' instead of 'a' don't help very much";

            return $feedback;
        }

        /**
         * @param string $string
         * @param array $map
         * @return string
         */
        protected static function translate(string $string, array $map): string
        {
            return str_replace(array_keys($map), array_values($map), $string);
        }

        /**
         * @return string[][]
         */
        protected static function getL33tTable(): array
        {
            return [
                'a' => ['4', '@'],
                'b' => ['8'],
                'c' => ['(', '{', '[', '<'],
                'e' => ['3'],
                'g' => ['6', '9'],
                'i' => ['1', '!', '|'],
                'l' => ['1', '|', '7'],
                'o' => ['0'],
                's' => ['$', '5'],
                't' => ['+', '7'],
                'x' => ['%'],
                'z' => ['2'],
            ];
        }

        /**
         * @param $password
         * @return array
         */
        protected static function getL33tSubtable($password): array
        {
            // The preg_split call below is a multibyte compatible version of str_split
            $passwordChars = array_unique(preg_split('//u', $password, null, PREG_SPLIT_NO_EMPTY));

            $subTable = [];

            $table = static::getL33tTable();
            foreach ($table as $letter => $substitutions)
            {
                foreach ($substitutions as $sub)
                {
                    if (in_array($sub, $passwordChars))
                    {
                        $subTable[$letter][] = $sub;
                    }
                }
            }

            return $subTable;
        }

        /**
         * @param $subtable
         * @return array
         */
        protected static function getL33tSubstitutions($subtable): array
        {
            $keys = array_keys($subtable);
            $substitutions = self::substitutionTableHelper($subtable, $keys, [[]]);

            // Converts the substitution arrays from [ [a, b], [c, d] ] to [ a => b, c => d ]
            return array_map(function ($subArray)
            {
                return array_combine(array_column($subArray, 0), array_column($subArray, 1));
            }, $substitutions);
        }

        /**
         * @param $table
         * @param $keys
         * @param $subs
         * @return mixed
         */
        protected static function substitutionTableHelper($table, $keys, $subs)
        {
            if (empty($keys))
            {
                return $subs;
            }

            $firstKey = array_shift($keys);
            $otherKeys = $keys;
            $nextSubs = [];

            foreach ($table[$firstKey] as $l33tCharacter)
            {
                foreach ($subs as $sub)
                {
                    $dupL33tIndex = false;
                    foreach ($sub as $index => $char)
                    {
                        if ($char[0] === $l33tCharacter)
                        {
                            $dupL33tIndex = $index;
                            break;
                        }
                    }

                    if ($dupL33tIndex === false)
                    {
                        $subExtension = $sub;
                        $subExtension[] = [$l33tCharacter, $firstKey];
                        $nextSubs[] = $subExtension;
                    }
                    else
                    {
                        $subAlternative = $sub;
                        array_splice($subAlternative, $dupL33tIndex, 1);
                        $subAlternative[] = [$l33tCharacter, $firstKey];
                        $nextSubs[] = $sub;
                        $nextSubs[] = $subAlternative;
                    }
                }
            }

            $nextSubs = array_unique($nextSubs, SORT_REGULAR);
            return self::substitutionTableHelper($table, $otherKeys, $nextSubs);
        }

        /**
         * @return float|int
         */
        protected function getRawGuesses(): ?int
        {
            return parent::getRawGuesses() * $this->getL33tVariations();
        }

        /**
         * @return int
         */
        protected function getL33tVariations(): int
        {
            $variations = 1;

            foreach ($this->sub as $substitution => $letter)
            {
                $characters = preg_split('//u', mb_strtolower($this->token), null, PREG_SPLIT_NO_EMPTY);

                $subbed = count(array_filter($characters, function ($character) use ($substitution)
                {
                    return (string)$character === (string)$substitution;
                }));

                $unsubbed = count(array_filter($characters, function ($character) use ($letter)
                {
                    return (string)$character === (string)$letter;
                }));

                if ($subbed === 0 || $unsubbed === 0)
                {
                    $variations *= 2;
                }
                else
                {
                    $possibilities = 0;

                    for ($i = 1; $i <= min($subbed, $unsubbed); $i++)
                    {
                        $possibilities += static::binom($subbed + $unsubbed, $i);
                    }

                    $variations *= $possibilities;
                }
            }

            return $variations;
        }
    }
