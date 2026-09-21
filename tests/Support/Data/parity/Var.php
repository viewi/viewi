<?php

// Parity cases for PhpJsFunctions/Var - see tests/Support/Parity/ParityRunner.php for the format.

return [
    'is_array' => [
        [[]],
        [['a' => 1]],
        ['x'],
        [null],
    ],
    'is_int' => [
        [1],
        ['args' => [1.0], 'knownDiff' => 'number-type'],
        [1.5],
        ['1'],
        [true],
    ],
    'is_numeric' => [
        ['12'],
        ['12.5'],
        ['1e3'],
        [' 12'],
        ['12 '],
        ['0x1A'],
        ['abc'],
        [''],
        ['.5'],
        ['5.'],
        [5],
        [null],
        ['-'],
        ['+1'],
    ],
];
