<?php

// Parity cases for PhpJsFunctions/Network - see tests/Support/Parity/ParityRunner.php for the format.

return [
    'ip2long' => [
        ['192.168.1.1'],
        ['255.255.255.255'],
        ['1.2.3'],
        ['256.1.1.1'],
        ['0.0.0.0'],
    ],
    'long2ip' => [
        [3232235777],
        [0],
        [4294967295],
    ],
    'inet_pton' => [
        ['127.0.0.1'],
        ['args' => ['10.200.1.1'], 'knownDiff' => 'bytes-vs-chars'],
        ['bogus'],
    ],
    'inet_ntop' => [
        ["\x7f\x00\x00\x01"],
    ],
];
