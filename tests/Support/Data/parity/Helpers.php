<?php

// Parity cases for the internal helpers other ports and the transpiler are built on - see
// tests/Support/Parity/ParityRunner.php for the format. _php_compare is PHP 8's <=>.

return [
    '_php_compare' => [
        ['10', '9'],
        ['abc', 'abd'],
        ['1e1', '10'],
        [' 1', '1'],
        [0, 'a'],
        [0, ''],
        ['a', 0],
        [5, '5'],
        [5, '5x'],
        [null, 0],
        [null, ''],
        [null, '0'],
        [null, 'a'],
        [false, '0'],
        [true, 'a'],
        [false, []],
        [[1, 2], [1, 3]],
        [[1, 2], [1]],
        [['a' => 1], ['b' => 1]],
        [[1], 99],
        [1.5, '1.5'],
        [-1, null],
    ],
    '_php_cast_bool' => [
        ['0'],
        [''],
        ['0.0'],
        [' '],
        [0],
        [0.0],
        [-0.0],
        [NAN],
        [[]],
        [[0]],
        [['a' => null]],
        [null],
        ['false'],
    ],
    '_php_cast_array' => [
        [null],
        ['s'],
        [5],
        [[1, 2]],
        [['a' => 1]],
        [false],
    ],
];
