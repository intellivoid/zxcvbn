<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Zxcvbn\Classes\Matchers;

    use Zxcvbn\Abstracts\BaseMatch;
    use Zxcvbn\Classes\Matcher;
    use Zxcvbn\Classes\Utilities;
    use Zxcvbn\Objects\Feedback;

    class DictionaryMatch extends BaseMatch
    {
        public $pattern = 'dictionary';

        /**
         * The name of the dictionary that the token was found in.
         * @var string
         */
        public $dictionaryName;

        /**
         * The rank of the token in the dictionary.
         * @var int
         */
        public $rank;

        /**
         * The word that was matched from the dictionary.
         * @var string
         */
        public $matchedWord;

        /**
         * Whether the matched word was reversed in the token.
         * @var bool
         */
        public $reversed = false;

        /**
         * Whether the token contained l33t substitutions.
         * @var bool
         */
        public $l33t = false;

        /**
         * A cache of the frequency_lists json file
         * @var array
         */
        protected static $rankedDictionaries = [];

        protected const START_UPPER = "/^[A-Z][^A-Z]+$/u";
        protected const END_UPPER = "/^[^A-Z]+[A-Z]$/u";
        protected const ALL_UPPER = "/^[^a-z]+$/u";
        protected const ALL_LOWER = "/^[^A-Z]+$/u";

        /**
         * Match occurrences of dictionary words in password.
         *
         * @param string $password
         * @param array $userInputs
         * @param array $rankedDictionaries
         * @return DictionaryMatch[]
         */
        public static function match(string $password, array $userInputs = [], array $rankedDictionaries = []): array
        {
            $matches = [];
            if ($rankedDictionaries)
            {
                $dicts = $rankedDictionaries;
            }
            else
            {
                $dicts = static::getRankedDictionaries();
            }

            if (!empty($userInputs))
            {
                $dicts['user_inputs'] = [];
                foreach ($userInputs as $rank => $input)
                {
                    $input_lower = mb_strtolower($input);
                    $dicts['user_inputs'][$input_lower] = $rank + 1; // rank starts at 1, not 0
                }
            }
            foreach ($dicts as $name => $dict)
            {
                $results = static::dictionaryMatch($password, $dict);
                foreach ($results as $result)
                {
                    $result['dictionary_name'] = $name;
                    $matches[] = new static($password, $result['begin'], $result['end'], $result['token'], $result);
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
         * @param array $params An array with keys: [dictionary_name, matched_word, rank].
         */
        public function __construct($password, $begin, $end, $token, array $params = [])
        {
            parent::__construct($password, $begin, $end, $token);
            if (!empty($params)) {
                $this->dictionaryName = $params['dictionary_name'] ?? null;
                $this->matchedWord = $params['matched_word'] ?? null;
                $this->rank = $params['rank'] ?? null;
            }
        }

        /**
         * @param $isSoleMatch
         * @return Feedback
         */
        public function getFeedback($isSoleMatch): Feedback
        {
            $startUpper = '/^[A-Z][^A-Z]+$/u';
            $allUpper = '/^[^a-z]+$/u';

            $feedback = new Feedback($this->getFeedbackWarning($isSoleMatch));

            if (preg_match($startUpper, $this->token))
            {
                $feedback->Suggestions[] = "Capitalization doesn't help very much";
            }
            elseif (preg_match($allUpper, $this->token) && mb_strtolower($this->token) != $this->token)
            {
                $feedback->Suggestions[] = "All-uppercase is almost as easy to guess as all-lowercase";
            }

            return $feedback;
        }

        /**
         * @param $isSoleMatch
         * @return string
         */
        public function getFeedbackWarning($isSoleMatch): string
        {
            switch ($this->dictionaryName)
            {
                case 'passwords':
                    if ($isSoleMatch && !$this->l33t && !$this->reversed)
                    {
                        if ($this->rank <= 10)
                        {
                            return 'This is a top-10 common password';
                        }
                        elseif ($this->rank <= 100)
                        {
                            return 'This is a top-100 common password';
                        }
                        else
                        {
                            return 'This is a very common password';
                        }
                    }
                    elseif ($this->getGuessesLog10() <= 4)
                    {
                        return 'This is similar to a commonly used password';
                    }
                    break;

                case 'english_wikipedia':
                    if ($isSoleMatch)
                    {
                        return 'A word by itself is easy to guess';
                    }
                    break;

                case 'surnames':
                case 'male_names':
                case 'female_names':
                    if ($isSoleMatch)
                    {
                        return 'Names and surnames by themselves are easy to guess';
                    }
                    else
                    {
                        return 'Common names and surnames are easy to guess';
                    }
            }

            return '';
        }

        /**
         * Attempts to find the provided password (as well as all possible substrings) in a dictionary.
         *
         * @param string $password
         * @param array $dict
         * @return array
         */
        protected static function dictionaryMatch(string $password, array $dict): array
        {
            $result = [];
            $length = mb_strlen($password);

            $pw_lower = mb_strtolower($password);

            foreach (range(0, $length - 1) as $i) {
                foreach (range($i, $length - 1) as $j) {
                    $word = mb_substr($pw_lower, $i, $j - $i + 1);

                    if (isset($dict[$word])) {
                        $result[] = [
                            'begin' => $i,
                            'end' => $j,
                            'token' => mb_substr($password, $i, $j - $i + 1),
                            'matched_word' => $word,
                            'rank' => $dict[$word],
                        ];
                    }
                }
            }

            return $result;
        }

        /**
         * Load ranked frequency dictionaries.
         *
         * @return array
         */
        protected static function getRankedDictionaries(): array
        {
            if (empty(self::$rankedDictionaries))
            {
                $json = file_get_contents(Utilities::getDataFilePath('frequency_lists.json'));
                $data = json_decode($json, true);

                $rankedLists = [];
                foreach ($data as $name => $words) {
                    $rankedLists[$name] = array_combine($words, range(1, count($words)));
                }
                self::$rankedDictionaries = $rankedLists;
            }

            return self::$rankedDictionaries;
        }

        /**
         * @return integer
         */
        protected function getRawGuesses(): ?int
        {
            $guesses = $this->rank;
            $guesses *= $this->getUppercaseVariations();

            return $guesses;
        }

        /**
         * @return integer
         */
        protected function getUppercaseVariations(): int
        {
            $word = $this->token;
            if (preg_match(self::ALL_LOWER, $word) || mb_strtolower($word) === $word)
            {
                return 1;
            }

            foreach (array(self::START_UPPER, self::END_UPPER, self::ALL_UPPER) as $regex)
            {
                if (preg_match($regex, $word))
                {
                    return 2;
                }
            }

            $uppercase = count(array_filter(preg_split('//u', $word, null, PREG_SPLIT_NO_EMPTY), 'ctype_upper'));
            $lowercase = count(array_filter(preg_split('//u', $word, null, PREG_SPLIT_NO_EMPTY), 'ctype_lower'));

            $variations = 0;
            for ($i = 1; $i <= min($uppercase, $lowercase); $i++)
            {
                $variations += static::binom($uppercase + $lowercase, $i);
            }
            return $variations;
        }
    }
