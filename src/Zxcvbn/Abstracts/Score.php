<?php

    namespace Zxcvbn\Abstracts;

    abstract class Score
    {
        const MIN_GUESSES_BEFORE_GROWING_SEQUENCE = 10000;

        const MIN_SUBMATCH_GUESSES_SINGLE_CHAR = 10;

        const MIN_SUBMATCH_GUESSES_MULTI_CHAR = 50;
    }