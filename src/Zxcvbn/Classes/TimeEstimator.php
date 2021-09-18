<?php

    namespace Zxcvbn\Classes;

    use Zxcvbn\Objects\EstimatedAttackTimes;

    class TimeEstimator
    {
        /**
         * @param $guesses
         * @return EstimatedAttackTimes
         */
        public function estimateAttackTimes($guesses): EstimatedAttackTimes
        {
            $return_results = new EstimatedAttackTimes();
            $return_results->CrackTimesSeconds = new EstimatedAttackTimes\CrackTimes();

            $return_results->CrackTimesSeconds->OnlineThrottling100PerHour = $guesses / (100 / 3600);
            $return_results->CrackTimesSeconds->OnlineThrottling100PerHourDisplay =
                $this->displayTime($return_results->CrackTimesSeconds->OnlineThrottling100PerHour);
            $return_results->CrackTimesSeconds->OnlineNoThrottling10PerSecond = $guesses / 10;
            $return_results->CrackTimesSeconds->OnlineNoThrottling10PerSecondDisplay =
                $this->displayTime($return_results->CrackTimesSeconds->OnlineNoThrottling10PerSecond);
            $return_results->CrackTimesSeconds->OfflineSlowHashing1e4PerSecond = $guesses / 1e4;
            $return_results->CrackTimesSeconds->OfflineSlowHashing1e4PerSecondDisplay =
                $this->displayTime($return_results->CrackTimesSeconds->OfflineSlowHashing1e4PerSecond);
            $return_results->CrackTimesSeconds->OfflineFastHashing1e10PerSecond = $guesses / 1e10;
            $return_results->CrackTimesSeconds->OfflineFastHashing1e10PerSecondDisplay =
                $this->displayTime($return_results->CrackTimesSeconds->OfflineFastHashing1e10PerSecond);

            $return_results->Score = $this->guessesToScore($guesses);
            return $return_results;
        }

        /**
         * @param $guesses
         * @return int
         */
        protected function guessesToScore($guesses): int
        {
            $DELTA = 5;

            if ($guesses < 1e3 + $DELTA)
            {
                return 0;
            }

            if ($guesses < 1e6 + $DELTA)
            {
                return 1;
            }

            if ($guesses < 1e8 + $DELTA)
            {
                return 2;
            }

            if ($guesses < 1e10 + $DELTA)
            {
                return 3;
            }

            return 4;
        }

        /**
         * @param $seconds
         * @return mixed|string
         */
        protected function displayTime($seconds)
        {
            $callback = function ($seconds)
            {
                $minute = 60;
                $hour = $minute * 60;
                $day = $hour * 24;
                $month = $day * 31;
                $year = $month * 12;
                $century = $year * 100;

                if ($seconds < 1)
                {
                    return [null, 'less than a second'];
                }

                if ($seconds < $minute)
                {
                    $base = round($seconds);
                    return [$base, "$base second"];
                }

                if ($seconds < $hour)
                {
                    $base = round($seconds / $minute);
                    return [$base, "$base minute"];
                }

                if ($seconds < $day)
                {
                    $base = round($seconds / $hour);
                    return [$base, "$base hour"];
                }

                if ($seconds < $month)
                {
                    $base = round($seconds / $day);
                    return [$base, "$base day"];
                }

                if ($seconds < $year)
                {
                    $base = round($seconds / $month);
                    return [$base, "$base month"];
                }

                if ($seconds < $century)
                {
                    $base = round($seconds / $year);
                    return [$base, "$base year"];
                }

                return [null, 'centuries'];
            };

            list($display_num, $display_str) = $callback($seconds);

            if ($display_num > 1)
            {
                $display_str .= 's';
            }

            return $display_str;
        }
    }
