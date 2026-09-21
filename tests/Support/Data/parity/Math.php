<?php

// Parity cases for PhpJsFunctions/Math and the transpiler's cast helpers — see
// tests/Support/Parity/ParityRunner.php for the format.

return [
    'round' => [
        [2.5],
        [-2.5],
        [1.955, 2],
        [1234.5678, -2],
    ],
    'floor' => [
        [1.7],
        [-1.2],
        [5],
    ],
    '_php_cast_int' => [
        ['123abc'],
        ['1e3'],
        [3.99],
        [null],
        [' 42'],
    ],
];
