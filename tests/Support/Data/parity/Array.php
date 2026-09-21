<?php

// Parity cases for PhpJsFunctions/Array — see tests/Support/Parity/ParityRunner.php for the format.

return [
    'count' => [
        [[]],
        [[1, 2, 3]],
        [['a' => 1, 'b' => 2]],
    ],
    'array_keys' => [
        [['a' => 1, 'b' => 2]],
        ['args' => [[5 => 'x', 2 => 'y']], 'knownDiff' => 'int-key-order'],
        [[]],
    ],
    'in_array' => [
        ['1', [1, 2]],
        ['1', [1, 2], true],
        [0, ['a']],
    ],
    'array_values' => [
        [['a' => 1, 'b' => 2]],
        [[]],
    ],
];
