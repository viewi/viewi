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
    // --- task 9: the rest of the Math group ---
    'abs' => [
        [-5],
        [-5.5],
        ['-3'],
        [0],
        [-0.0],
    ],
    'sin' => [
        ['args' => [1], 'approx' => true],
        ['args' => [0], 'approx' => true],
        ['args' => [-0.5], 'approx' => true],
    ],
    'cos' => [
        ['args' => [1], 'approx' => true],
        ['args' => [0], 'approx' => true],
    ],
    'tan' => [
        ['args' => [1], 'approx' => true],
    ],
    'asin' => [
        ['args' => [0.5], 'approx' => true],
        ['args' => [2], 'approx' => true],
    ],
    'acos' => [
        ['args' => [0.5], 'approx' => true],
    ],
    'atan' => [
        ['args' => [1], 'approx' => true],
    ],
    'sinh' => [
        ['args' => [1], 'approx' => true],
    ],
    'cosh' => [
        ['args' => [1], 'approx' => true],
    ],
    'tanh' => [
        ['args' => [1], 'approx' => true],
    ],
    'asinh' => [
        ['args' => [1], 'approx' => true],
    ],
    'acosh' => [
        ['args' => [2], 'approx' => true],
        ['args' => [0.5], 'approx' => true],
    ],
    'atanh' => [
        ['args' => [0.5], 'approx' => true],
    ],
    'exp' => [
        ['args' => [1], 'approx' => true],
        ['args' => [0], 'approx' => true],
        ['args' => [710], 'approx' => true],
    ],
    'expm1' => [
        ['args' => [1e-10], 'approx' => true],
        ['args' => [1], 'approx' => true],
    ],
    'log10' => [
        ['args' => [1000], 'approx' => true],
        ['args' => [0], 'approx' => true],
        ['args' => [-1], 'approx' => true],
    ],
    'log1p' => [
        ['args' => [1e-10], 'approx' => true],
    ],
    'atan2' => [
        ['args' => [1, 1], 'approx' => true],
        ['args' => [0, -1], 'approx' => true],
    ],
    'log' => [
        ['args' => [M_E], 'approx' => true],
        ['args' => [8, 2], 'approx' => true],
        ['args' => [100, 10], 'approx' => true],
        ['args' => [0], 'approx' => true],
    ],
    'pow' => [
        [2, 10],
        [2, -1],
        [0, 0],
        ['3', 2],
        [2, 0.5],
        [-8, 1 / 3],
        [2, 64],
        ['args' => [10, -3], 'approx' => true],
    ],
    'hypot' => [
        ['args' => [3, 4], 'approx' => true],
    ],
    'sqrt' => [
        [16],
        [2],
        [-1],
    ],
    'pi' => [
        [],
    ],
    'deg2rad' => [
        ['args' => [180], 'approx' => true],
    ],
    'rad2deg' => [
        ['args' => [M_PI], 'approx' => true],
    ],
    'fmod' => [
        [10, 3],
        [-10, 3],
        [10.5, 3],
        [1, 0],
    ],
    'is_finite' => [
        [1.5],
        [INF],
        [NAN],
    ],
    'is_infinite' => [
        [-INF],
        [1],
    ],
    'is_nan' => [
        [NAN],
        [0.0],
    ],
    'base_convert' => [
        ['ff', 16, 2],
        ['777', 8, 10],
        ['zz', 36, 10],
        ['FF', 16, 10],
        ['1g', 16, 10],
    ],
    'bindec' => [
        ['1010'],
        ['102'],
        [''],
    ],
    'decbin' => [
        [10],
        [0],
        [-1],
    ],
    'dechex' => [
        [255],
        [-1],
    ],
    'decoct' => [
        [8],
        [-1],
    ],
    'hexdec' => [
        ['ff'],
        ['0xff'],
        ['zz1'],
    ],
    'octdec' => [
        ['777'],
        ['8'],
    ],
    'getrandmax' => [
        [],
    ],
    'mt_getrandmax' => [
        [],
    ],
    'rand' => [
        ['args' => [], 'shape' => true],
        ['args' => [1, 6], 'shape' => true],
    ],
    'mt_rand' => [
        ['args' => [1, 6], 'shape' => true],
    ],
    'lcg_value' => [
        ['args' => [], 'shape' => true],
    ],
];
