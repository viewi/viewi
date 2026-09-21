<?php

// Parity cases for PhpJsFunctions/Ctype - see tests/Support/Parity/ParityRunner.php for the format.

return [
    'ctype_alnum' => [
        ['abc123'],
        ['abc 1'],
        [''],
        ['é'],
    ],
    'ctype_digit' => [
        ['123'],
        ['12.3'],
        [''],
        ['-1'],
    ],
    // --- task 10: the rest of the Ctype group ---
    'ctype_alpha' => [
        ['abcXYZ'],
        ['abc1'],
        [''],
    ],
    'ctype_cntrl' => [
        ["\n\t"],
        ['a'],
    ],
    'ctype_graph' => [
        ['abc!'],
        ['a b'],
    ],
    'ctype_lower' => [
        ['abc'],
        ['aBc'],
    ],
    'ctype_upper' => [
        ['ABC'],
        ['AbC'],
    ],
    'ctype_print' => [
        ['a b!'],
        ["a\n"],
    ],
    'ctype_punct' => [
        ['!?.,'],
        ['!a'],
    ],
    'ctype_space' => [
        [" \n\t\r\x0B\f"],
        [' a'],
    ],
    'ctype_xdigit' => [
        ['AbCdEf09'],
        ['0xff'],
    ],
];
