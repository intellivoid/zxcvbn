<?php

    require('ppm');
    import('net.intellivoid.zxcvbn');

    if(count($argv) <= 1)
    {
        print("Pass on the password via a cli argument" . PHP_EOL);
        exit();
    }

    $zxcvbn = new \Zxcvbn\zxcvbn();
    print(json_encode($zxcvbn->passwordStrength($argv[1])->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);