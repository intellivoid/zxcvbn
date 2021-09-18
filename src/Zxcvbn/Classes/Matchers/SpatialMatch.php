<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace Zxcvbn\Classes\Matchers;

    use Zxcvbn\Abstracts\BaseMatch;
    use Zxcvbn\Classes\Matcher;
    use Zxcvbn\Classes\Utilities;
    use Zxcvbn\Objects\Feedback;

    class SpatialMatch extends BaseMatch
    {
        public const SHIFTED_CHARACTERS = '~!@#$%^&*()_+QWERTYUIOP{}|ASDFGHJKL:"ZXCVBNM<>?';
        public const KEYBOARD_STARTING_POSITION = 94;
        public const KEYPAD_STARTING_POSITION = 15;
        public const KEYBOARD_AVERAGE_DEGREES = 4.5957446809; // 432 / 94
        public const KEYPAD_AVERAGE_DEGREES = 5.0666666667; // 76 / 15
        public $pattern = 'spatial';

        /**
         * The number of characters the shift key was held for in the token.
         * @var int
         */
        public $shiftedCount;

        /**
         * The number of turns on the keyboard required to complete the token.
         * @var int
         */
        public $turns;

        /**
         * The keyboard layout that the token is a spatial match on.
         * @var string
         */
        public $graph;

        /**
         * A cache of the adjacency_graphs json file
         * @var array
         */
        protected static $adjacencyGraphs = [];

        /**
         * Match spatial patterns based on keyboard layouts (e.g. qwerty, dvorak, keypad).
         *
         * @param string $password
         * @param array $userInputs
         * @param array $graphs
         * @return SpatialMatch[]
         */
        public static function match(string $password, array $userInputs = [], array $graphs = []): array
        {

            $matches = [];
            if (!$graphs)
            {
                $graphs = static::getAdjacencyGraphs();
            }

            foreach ($graphs as $name => $graph)
            {
                $results = static::graphMatch($password, $graph, $name);
                foreach ($results as $result)
                {
                    $result['graph'] = $name;
                    $matches[] = new static($password, $result['begin'], $result['end'], $result['token'], $result);
                }
            }
            Utilities::usort($matches, [Matcher::class, 'compareMatches']);
            return $matches;
        }

        /**
         * @param $isSoleMatch
         * @return Feedback
         * @noinspection PhpUnusedParameterInspection
         */
        public function getFeedback($isSoleMatch): Feedback
        {
            $warning = $this->turns == 1
                ? 'Straight rows of keys are easy to guess'
                : 'Short keyboard patterns are easy to guess';

            return new Feedback($warning, [
                'Use a longer keyboard pattern with more turns'
            ]);
        }

        /**
         * @param string $password
         * @param int $begin
         * @param int $end
         * @param string $token
         * @param array $params An array with keys: [graph (required), shifted_count, turns].
         */
        public function __construct($password, $begin, $end, $token, array $params = [])
        {
            parent::__construct($password, $begin, $end, $token);
            $this->graph = $params['graph'];
            if (!empty($params)) {
                $this->shiftedCount = $params['shifted_count'] ?? null;
                $this->turns = $params['turns'] ?? null;
            }
        }

        /**
         * Match spatial patterns in a adjacency graph.
         * @param string $password
         * @param array $graph
         * @param string $graphName
         * @return array
         */
        protected static function graphMatch(string $password, array $graph, string $graphName): array
        {
            $result = [];
            $i = 0;

            $passwordLength = mb_strlen($password);

            while ($i < $passwordLength - 1)
            {
                $j = $i + 1;
                $lastDirection = null;
                $turns = 0;
                $shiftedCount = 0;

                // Check if the initial character is shifted
                if ($graphName === 'qwerty' || $graphName === 'dvorak')
                {
                    if (mb_strpos(self::SHIFTED_CHARACTERS, mb_substr($password, $i, 1)) !== false)
                    {
                        $shiftedCount++;
                    }
                }

                while (true)
                {
                    $prevChar = mb_substr($password, $j - 1, 1);
                    $found = false;
                    $curDirection = -1;
                    $adjacents = $graph[$prevChar] ?? [];

                    // Consider growing pattern by one character if j hasn't gone over the edge.
                    if ($j < $passwordLength)
                    {
                        $curChar = mb_substr($password, $j, 1);
                        foreach ($adjacents as $adj)
                        {
                            if($adj == null)
                                continue;
                            $curDirection += 1;
                            $curCharPos = static::indexOf($adj, $curChar);
                            if ($adj !== null && $curCharPos !== -1)
                            {
                                $found = true;
                                $foundDirection = $curDirection;

                                if ($curCharPos === 1)
                                {
                                    $shiftedCount += 1;
                                }

                                if ($lastDirection !== $foundDirection)
                                {
                                    $turns += 1;
                                    $lastDirection = $foundDirection;
                                }

                                break;
                            }
                        }
                    }

                    // if the current pattern continued, extend j and try to grow again
                    if ($found) {
                        $j += 1;
                    } else {
                        // otherwise push the pattern discovered so far, if any...

                        // Ignore length 1 or 2 chains.
                        if ($j - $i > 2) {
                            $result[] = [
                                'begin' => $i,
                                'end' => $j - 1,
                                'token' => mb_substr($password, $i, $j - $i),
                                'turns' => $turns,
                                'shifted_count' => $shiftedCount
                            ];
                        }
                        // ...and then start a new search for the rest of the password.
                        $i = $j;
                        break;
                    }
                }
            }

            return $result;
        }

        /**
         * Get the index of a string a character first
         *
         * @param string $string
         * @param string $char
         * @return int
         */
        protected static function indexOf(string $string, string $char): int
        {
            $pos = mb_strpos($string, $char);
            return ($pos === false ? -1 : $pos);
        }

        /**
         * Load adjacency graphs.
         *
         * @return array
         */
        public static function getAdjacencyGraphs(): array
        {
            if (empty(self::$adjacencyGraphs))
            {
                $json = file_get_contents(Utilities::getDataFilePath('adjacency_graphs.json'));
                $data = json_decode($json, true);

                $data = [
                    'qwerty' => $data['qwerty'],
                    'dvorak' => $data['dvorak'],
                    'keypad' => $data['keypad'],
                    'mac_keypad' => $data['mac_keypad'],
                ];
                self::$adjacencyGraphs = $data;
            }

            return self::$adjacencyGraphs;
        }

        /**
         * @return float|int
         * @noinspection PhpConditionAlreadyCheckedInspection
         * @noinspection SpellCheckingInspection
         * @noinspection DuplicatedCode
         */
        protected function getRawGuesses()
        {
            if ($this->graph === 'qwerty' || $this->graph === 'dvorak')
            {
                $startingPosition = self::KEYBOARD_STARTING_POSITION;
                $averageDegree = self::KEYBOARD_AVERAGE_DEGREES;
            }
            else
            {
                $startingPosition = self::KEYPAD_STARTING_POSITION;
                $averageDegree = self::KEYPAD_AVERAGE_DEGREES;
            }

            $guesses = 0;
            $length = mb_strlen($this->token);
            $turns = $this->turns;

            // estimate the number of possible patterns w/ length L or less with t turns or less.
            for ($i = 2; $i <= $length; $i++)
            {
                $possibleTurns = min($turns, $i - 1);
                for ($j = 1; $j <= $possibleTurns; $j++)
                {
                    $guesses += static::binom($i - 1, $j - 1) * $startingPosition * pow($averageDegree, $j);
                }
            }

            // add extra guesses for shifted keys. (% instead of 5, A instead of a.)
            // math is similar to extra guesses of l33t substitutions in dictionary matches.
            if ($this->shiftedCount > 0)
            {
                $shifted = $this->shiftedCount;
                $unshifted = $length - $shifted;

                if ($shifted === 0 || $unshifted === 0)
                {
                    $guesses *= 2;
                }
                else
                {
                    $variations = 0;
                    for ($i = 1; $i <= min($shifted, $unshifted); $i++)
                    {
                        $variations += static::binom($shifted + $unshifted, $i);
                    }
                    $guesses *= $variations;
                }
            }

            return $guesses;
        }
    }
