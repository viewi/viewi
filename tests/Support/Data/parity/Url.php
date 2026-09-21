<?php

// Parity cases for PhpJsFunctions/Url - see tests/Support/Parity/ParityRunner.php for the format.

return [
    'urlencode' => [
        ['a b&c'],
        ['~._-'],
        ['é'],
        ['a+b'],
        ["!*'()"],
    ],
    'rawurlencode' => [
        ['a b'],
        ['~._-'],
        ['é'],
        ["!*'()"],
    ],
    'rawurldecode' => [
        ['a%20b+c'],
        ['%C3%A9'],
        ['%zz'],
        ['100%'],
    ],
    // --- task 10: the rest of the Url group ---
    'urldecode' => [
        ['a+b%20c'],
        ['%C3%A9'],
        ['%zz'],
    ],
    'base64_encode' => [
        ['hello'],
        ['é'],
        [''],
    ],
    'base64_decode' => [
        ['aGVsbG8='],
        ['w6k='],
        ['aGVsbG8'],
        ['!!!'],
    ],
    'http_build_query' => [
        [['a' => 1, 'b' => 'x y']],
        [['a' => [1, 2], 'b' => ['c' => 'd']]],
        [['t' => true, 'f' => false, 'n' => null]],
        [['é' => 'ü&=']],
        [[1, 2], 'p_'],
        [['a' => 1, 'b' => 2], '', ';'],
    ],
    'parse_url' => [
        ['https://user:pw@example.com:8080/path/x.php?q=1&r[]=2#frag'],
        ['/relative/path?x=1'],
        ['//example.com/p'],
        ['mailto:a@b.c'],
        ['https://example.com/p', 1],
        ['http:///bad'],
    ],
];
