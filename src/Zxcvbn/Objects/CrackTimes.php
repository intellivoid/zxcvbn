<?php

    namespace Zxcvbn\Objects\EstimatedAttackTimes;

    class CrackTimes
    {
        /**
         * @var float|int
         */
        public $OnlineThrottling100PerHour;

        /**
         * @var string
         */
        public $OnlineThrottling100PerHourDisplay;

        /**
         * @var float|int
         */
        public $OnlineNoThrottling10PerSecond;

        /**
         * @var string
         */
        public $OnlineNoThrottling10PerSecondDisplay;

        /**
         * @var float|int
         */
        public $OfflineSlowHashing1e4PerSecond;

        /**
         * @var string
         */
        public $OfflineSlowHashing1e4PerSecondDisplay;

        /**
         * @var float|int
         */
        public $OfflineFastHashing1e10PerSecond;

        /**
         * @var string
         */
        public $OfflineFastHashing1e10PerSecondDisplay;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'online_throttling_100_per_hour' => $this->OnlineThrottling100PerHour,
                'online_throttling_100_per_hour_display' => $this->OnlineThrottling100PerHourDisplay,
                'online_no_throttling_10_per_second' => $this->OnlineNoThrottling10PerSecond,
                'online_no_throttling_10_per_second_display' => $this->OnlineNoThrottling10PerSecondDisplay,
                'offline_slow_hashing_1e4_per_second' => $this->OfflineSlowHashing1e4PerSecond,
                'offline_slow_hashing_1e4_per_second_display' => $this->OfflineSlowHashing1e4PerSecondDisplay,
                'offline_fast_hashing_1e10_per_second' => $this->OfflineFastHashing1e10PerSecond,
                'offline_fast_hashing_1e10_per_second_display' => $this->OfflineFastHashing1e10PerSecondDisplay
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return CrackTimes
         */
        public static function fromArray(array $data): CrackTimes
        {
            $CrackTimesObject = new CrackTimes();

            if(isset($data['online_throttling_100_per_hour']))
                $CrackTimesObject->OnlineThrottling100PerHour = $data['online_throttling_100_per_hour'];

            if(isset($data['online_throttling_100_per_hour_display']))
                $CrackTimesObject->OnlineThrottling100PerHourDisplay = $data['online_throttling_100_per_hour_display'];

            if(isset($data['online_no_throttling_10_per_second']))
                $CrackTimesObject->OnlineNoThrottling10PerSecond = $data['online_no_throttling_10_per_second'];

            if(isset($data['online_no_throttling_10_per_second_display']))
                $CrackTimesObject->OnlineNoThrottling10PerSecondDisplay = $data['online_no_throttling_10_per_second_display'];

            if(isset($data['offline_slow_hashing_1e4_per_second']))
                $CrackTimesObject->OfflineSlowHashing1e4PerSecond = $data['offline_slow_hashing_1e4_per_second'];

            if(isset($data['offline_slow_hashing_1e4_per_second_display']))
                $CrackTimesObject->OfflineSlowHashing1e4PerSecondDisplay = $data['offline_slow_hashing_1e4_per_second_display'];

            if(isset($data['offline_fast_hashing_1e10_per_second']))
                $CrackTimesObject->OfflineFastHashing1e10PerSecond = $data['offline_fast_hashing_1e10_per_second'];

            if(isset($data['offline_fast_hashing_1e10_per_second_display']))
                $CrackTimesObject->OfflineFastHashing1e10PerSecondDisplay = $data['offline_fast_hashing_1e10_per_second_display'];

            return $CrackTimesObject;
        }
    }