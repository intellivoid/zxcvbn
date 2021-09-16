<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Zxcvbn\Classes;

    use Zxcvbn\Abstracts\BaseMatch;
    use Zxcvbn\Abstracts\Score;
    use Zxcvbn\Classes\Matchers\Bruteforce;
    use Zxcvbn\Interfaces\MatchInterface;
    use Zxcvbn\Objects\GuessableMatchSequence;

    class Scorer
    {
        protected $password;
        protected $excludeAdditive;
        protected $optimal = [];

        /**
         * @param string $password
         * @param MatchInterface[] $matches
         * @param bool $excludeAdditive
         * @return GuessableMatchSequence Returns an array with these keys: [password, guesses, guesses_log10, sequence]
         * @noinspection PhpUnused
         */
        public function getMostGuessableMatchSequence(string $password, array $matches, bool $excludeAdditive): GuessableMatchSequence
        {
            $this->password = $password;
            $this->excludeAdditive = $excludeAdditive;
            $length = mb_strlen($password);
            $emptyArray = $length > 0 ? array_fill(0, $length, []) : [];
            $matchesByEndIndex = $emptyArray;

            foreach ($matches as $match)
            {
                $matchesByEndIndex[$match->end][] = $match;
            }

            // small detail: for deterministic output, sort each sublist by i.
            foreach ($matchesByEndIndex as &$matches)
            {
                usort($matches, function ($a, $b)
                {
                    /** @var $a BaseMatch */
                    /** @var $b BaseMatch */
                    return $a->begin - $b->begin;
                });
            }

            $this->optimal = [
                'm' => $emptyArray,
                'pi' => $emptyArray,
                'g' => $emptyArray,
            ];

            for ($k = 0; $k < $length; $k++)
            {
                /** @var BaseMatch $match */
                foreach ($matchesByEndIndex[$k] as $match)
                {
                    if ($match->begin > 0)
                    {
                        foreach ($this->optimal['m'][$match->begin - 1] as $l => $null)
                        {
                            $l = (int)$l;
                            $this->update($match, $l + 1);
                        }
                    }
                    else
                    {
                        $this->update($match, 1);
                    }
                }
                $this->bruteforceUpdate($k);
            }


            if ($length === 0)
            {
                $guesses = 1;
                $optimalSequence = [];
            }
            else
            {
                $optimalSequence = $this->unwind($length);
                $optimalSequenceLength = count($optimalSequence);
                $guesses = $this->optimal['g'][$length - 1][$optimalSequenceLength];
            }

            $ReturnResults = new GuessableMatchSequence();
            $ReturnResults->Password = $password;
            $ReturnResults->Guesses = $guesses;
            $ReturnResults->GuessesLog10 = log10($guesses);
            $ReturnResults->Sequence = $optimalSequence;

            return $ReturnResults;
        }

        /**
         * helper: considers whether a length-l sequence ending at match m is better (fewer guesses)
         * than previously encountered sequences, updating state if so.
         * @param BaseMatch $match
         * @param int $length
         */
        protected function update(BaseMatch $match, int $length)
        {
            $k = $match->end;
            $pi = $match->getGuesses();

            if ($length > 1)
            {
                $pi *= $this->optimal['pi'][$match->begin - 1][$length - 1];
            }

            $g = $this->factorial($length) * $pi;
            if (!$this->excludeAdditive)
            {
                $g += pow(Score::MIN_GUESSES_BEFORE_GROWING_SEQUENCE, $length - 1);
            }

            foreach ($this->optimal['g'][$k] as $competingL => $competingG)
            {
                if ($competingL > $length)
                {
                    continue;
                }

                if ($competingG <= $g)
                {
                    return;
                }
            }

            $this->optimal['g'][$k][$length] = $g;
            $this->optimal['m'][$k][$length] = $match;
            $this->optimal['pi'][$k][$length] = $pi;

            ksort($this->optimal['g'][$k]);
            ksort($this->optimal['m'][$k]);
            ksort($this->optimal['pi'][$k]);
        }

        /**
         * helper: evaluate bruteforce matches ending at k
         * @param int $end
         */
        protected function bruteforceUpdate(int $end)
        {
            $match = $this->makeBruteforceMatch(0, $end);
            $this->update($match, 1);

            for ($i = 1; $i <= $end; $i++)
            {
                $match = $this->makeBruteforceMatch($i, $end);
                foreach ($this->optimal['m'][$i - 1] as $l => $lastM)
                {
                    $l = (int)$l;

                    if ($lastM->pattern === 'bruteforce')
                    {
                        continue;
                    }

                    $this->update($match, $l + 1);
                }
            }
        }

        /**
         * helper: make bruteforce match objects spanning i to j, inclusive.
         * @param int $begin
         * @param int $end
         * @return Bruteforce
         */
        protected function makeBruteforceMatch(int $begin, int $end): Bruteforce
        {
            return new Bruteforce($this->password, $begin, $end, mb_substr($this->password, $begin, $end - $begin + 1));
        }

        /**
         * helper: step backwards through optimal.m starting at the end, constructing the final optimal match sequence.
         * @param int $n
         * @return MatchInterface[]
         */
        protected function unwind(int $n): array
        {
            $optimalSequence = [];
            $k = $n - 1;

            // find the final best sequence length and score
            $l = null;
            $g = INF;

            foreach ($this->optimal['g'][$k] as $candidateL => $candidateG)
            {
                if ($candidateG < $g)
                {
                    $l = $candidateL;
                    $g = $candidateG;
                }
            }

            while ($k >= 0)
            {
                $m = $this->optimal['m'][$k][$l];
                array_unshift($optimalSequence, $m);
                $k = $m->begin - 1;
                $l--;
            }

            return $optimalSequence;
        }

        /**
         * unoptimized, called only on small n
         * @param int $n
         * @return int
         */
        protected function factorial(int $n): int
        {
            if ($n < 2)
            {
                return 1;
            }

            $f = 1;

            for ($i = 2; $i <= $n; $i++)
            {
                $f *= $i;
            }

            return $f;
        }
    }