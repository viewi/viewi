<?php

// Parity cases for PhpJsFunctions/Misc - see tests/Support/Parity/ParityRunner.php for the format.

return [
    'pack' => [
        ['A3', 'ab'],
        ['a4', 'ab'],
        ['C3', 65, 66, 67],
        ['n', 0x4142],
        ['v', 0x4142],
        ['N', 0x41424344],
        ['H*', '414243'],
    ],
    'uniqid' => [
        ['args' => [], 'shape' => true],
        ['args' => ['p_'], 'shape' => true],
    ],
];
