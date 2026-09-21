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
    // --- task 9: the rest of the Var group ---
    'boolval' => [
        ['0'],
        [''],
        ['0.0'],
        [[]],
        [[0]],
        [0.0],
        ['a'],
        [null],
    ],
    'floatval' => [
        ['1.5abc'],
        ['abc'],
        ['-1e3x'],
        [true],
        [[1]],
    ],
    'doubleval' => [
        ['2.5'],
    ],
    'strval' => [
        [0.1 + 0.2],
        [true],
        [null],
        [1e20],
        [-0.0],
    ],
    'gettype' => [
        [1],
        [1.5],
        ['args' => [1.0], 'knownDiff' => 'number-type'],
        ['s'],
        [true],
        [null],
        [[]],
        [['a' => 1]],
    ],
    'is_bool' => [
        [false],
        [0],
    ],
    'is_float' => [
        [1.5],
        ['args' => [1.0], 'knownDiff' => 'number-type'],
        [1],
        ['1.5'],
    ],
    'is_double' => [
        [1.5],
    ],
    'is_integer' => [
        [1],
        [1.5],
    ],
    'is_long' => [
        [1],
    ],
    'is_null' => [
        [null],
        [0],
        [''],
    ],
    'is_object' => [
        [[]],
        [['a' => 1]],
        [null],
    ],
    'is_scalar' => [
        [1],
        ['s'],
        [true],
        [null],
        [[]],
    ],
    'is_string' => [
        ['s'],
        [1],
    ],
    'is_callable' => [
        [[]],
        ['no_such_function_here'],
    ],
    'print_r' => [
        [[1, 'a' => [2]], true],
        ['s', true],
        [true, true],
        [null, true],
        [1.5, true],
    ],
    'var_export' => [
        [[1, 'a' => [true, null]], true],
        ['it\'s', true],
        [1.5, true],
        [false, true],
        [[], true],
        ['args' => [2.0, true], 'knownDiff' => 'number-type'],
        [1e25, true],
        [0.00001, true],
    ],
    'var_dump' => [
        [1],
    ],
    'serialize' => [
        [[1, 'a' => 'x']],
        ['é'],
        [1.5],
        [true],
        [null],
        [['n' => [1, 2]]],
    ],
    'unserialize' => [
        ['a:2:{i:0;i:1;s:1:"a";s:1:"x";}'],
        ['s:2:"é";'],
        ['d:1.5;'],
        ['b:1;'],
        ['N;'],
        ['garbage'],
    ],
];
