<?php

// Parity cases for PhpJsFunctions/Datetime - see tests/Support/Parity/ParityRunner.php for the format.
// Both sides run in UTC. $t = 2023-11-14 22:13:20 UTC.

$t = 1700000000;

return [
    'date' => [
        ['Y-m-d H:i:s', $t],
        ['D, d M Y', $t],
        ['N jS F y, g:i a', $t],
        ['l G A h', $t],
        ['U', $t],
        ['L W t z', $t],
        ['c', $t],
        ['r', $t],
        ['\\Y\\m', $t],
        ['e T P O', $t],
        ['n/j/Y', 0],
        ['args' => ['Y'], 'shape' => true],
    ],
    'gmdate' => [
        ['Y-m-d H:i', $t],
        ['c', $t],
    ],
    'gmmktime' => [
        [0, 0, 0, 1, 1, 2024],
        [25, 0, 0, 1, 1, 2024],
        [0, 0, 0, 13, 1, 2024],
        [12, 30, 0, 2, 29, 2024],
    ],
    'strtotime' => [
        ['2024-01-15'],
        ['2024-01-15 10:30:00'],
        ['2024-01-15T10:30:00Z'],
        ['15 January 2024'],
        ['@1700000000'],
        ['+1 day', $t],
        ['-2 hours', $t],
        ['next monday', $t],
        ['tomorrow', $t],
        ['garbage'],
    ],
    'time' => [
        ['args' => [], 'shape' => true],
    ],
    // --- task 10: the rest of the Datetime group ---
    'checkdate' => [
        [2, 29, 2024],
        [2, 29, 2023],
        [13, 1, 2024],
        [4, 31, 2024],
    ],
    'mktime' => [
        [0, 0, 0, 1, 1, 2024],
        [25, 70, 0, 1, 1, 2024],
        [0, 0, 0, 2, 30, 2024],
    ],
    'getdate' => [
        ['args' => [$t], 'knownDiff' => 'int-key-order'],
    ],
    'idate' => [
        ['Y', $t],
        ['m', $t],
        ['z', $t],
        ['U', $t],
    ],
    'date_parse' => [
        ['2024-01-15 10:30:45'],
        ['2024-01-15'],
        ['15 January 2024 08:05'],
    ],
    'gettimeofday' => [
        ['args' => [true], 'shape' => true],
    ],
    'microtime' => [
        ['args' => [true], 'shape' => true],
    ],
];
