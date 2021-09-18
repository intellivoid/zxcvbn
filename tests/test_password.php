<?php

    require('ppm');
    import('net.intellivoid.zxcvbn');

    if(count($argv) <= 1)
    {
        print("Pass on the password via a cli argument" . PHP_EOL);
        exit();
    }

    $zxcvbn = new \Zxcvbn\zxcvbn();
    $results = $zxcvbn->passwordStrength($argv[1]);
    print(json_encode($results->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL . PHP_EOL);

    switch($results->EstimatedAttackTimes->Score)
    {
        case \Zxcvbn\Abstracts\ScoreDefinitions::ExtremelyGuessable:
            print('Score 0, The password is extremely guessable (within 10^3 guesses)' . PHP_EOL);
            break;

        case \Zxcvbn\Abstracts\ScoreDefinitions::VeryGuessable:
            print('Score 1, The password is very guessable (guesses < 10^6)' . PHP_EOL);
            break;

        case \Zxcvbn\Abstracts\ScoreDefinitions::SomewhatGuessable:
            print('Score 2, The password is somewhat guessable (guesses < 10^8), provides some protection from unthrottled online attacks' . PHP_EOL);
            break;

        case \Zxcvbn\Abstracts\ScoreDefinitions::SafelyUnguessable:
            print('Score 3, The password is safely unguessable (guesses < 10^10), offers moderate protection from offline slow-hash scenario' . PHP_EOL);
            break;

        case \Zxcvbn\Abstracts\ScoreDefinitions::VeryUnguessable:
            print('Score 4, The password is very unguessable (guesses >= 10^10) and provides strong protection from offline slow-hash scenario' . PHP_EOL);
            break;
    }


    if($results->Feedback->Warning !== null)
        print($results->Feedback->Warning . PHP_EOL);

    if(count($results->Feedback->Suggestions) > 0)
    {
        print('Here are some suggestions to improve password security' . PHP_EOL);
        foreach($results->Feedback->Suggestions as $suggestion)
        {
            print(' - ' . $suggestion . PHP_EOL);
        }
    }