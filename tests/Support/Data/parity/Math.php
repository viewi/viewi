<?php

// Parity cases for PhpJsFunctions/Math and the transpiler's cast helpers - see
// tests/Support/Parity/ParityRunner.php for the format.

return [
    'round' => [
        [2.5],
        [-2.5],
        [1.955, 2],
        [1234.5678, -2],
        [5.045, 2],
        [-0.4],
        [3],
        ['1.5'],
        [1.45, 1],
    ],
    'floor' => [
        [1.7],
        [-1.2],
        [5],
        ['4.5'],
    ],
    'ceil' => [
        [4.1],
        [-4.1],
        [5],
        ['4.5'],
        [-0.5],
    ],
    'max' => [
        [1, 2, 3],
        [[1, 5, 3]],
        ['apple', 'banana'],
        [1, '2'],
        ['10', '9'],
        [0, 'a'],
        [[1, 2], [1, 3]],
    ],
    'min' => [
        [1, 2, 3],
        [[4, 2, 8]],
        ['10', '9'],
        [0, 'a'],
        [-1, null],
    ],
    'intval' => [
        ['42'],
        ['42abc'],
        ['abc'],
        [42.9],
        [-42.9],
        ['1e3'],
        [' 12'],
        ['0x1A'],
        ['0x1A', 16],
        ['012', 0],
        ['101', 2],
        [true],
        [null],
        ['-7'],
    ],
    '_php_cast_int' => [
        ['123abc'],
        ['1e3'],
        [3.99],
        [null],
        [' 42'],
    ],
    '_php_cast_float' => [
        ['1.5abc'],
        ['abc'],
        [true],
        ['1e3'],
        ['.5'],
    ],
];
