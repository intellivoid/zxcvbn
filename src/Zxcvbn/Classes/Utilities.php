<?php

    namespace Zxcvbn\Classes;

    class Utilities
    {
        /**
         * A stable implementation of usort().
         *
         * @param array $array
         * @param callable $value_compare_func
         * @return bool
         */
        public static function usort(array &$array, callable $value_compare_func)
        {
            $index = 0;

            foreach ($array as &$item)
            {
                $item = [$index++, $item];
            }

            $result = usort($array, function ($a, $b) use ($value_compare_func)
            {
                $result = $value_compare_func($a[1], $b[1]);
                return $result == 0 ? $a[0] - $b[0] : $result;
            });

            foreach ($array as &$item)
            {
                $item = $item[1];
            }
            return $result;
        }

    }