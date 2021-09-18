<?php

    namespace Zxcvbn\Abstracts;

    abstract class ScoreDefinitions
    {
        const ExtremelyGuessable = 0;

        const VeryGuessable = 1;

        const SomewhatGuessable = 2;

        const SafelyUnguessable = 3;

        const VeryUnguessable = 4;
    }