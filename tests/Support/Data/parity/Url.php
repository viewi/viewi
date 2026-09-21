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
];
