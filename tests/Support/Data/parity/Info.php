<?php

// Parity cases for PhpJsFunctions/Info - see tests/Support/Parity/ParityRunner.php for the format.

return [
    'version_compare' => [
        ['5.2', '5.10'],
        ['1.0.0', '1.0'],
        ['1.0rc1', '1.0'],
        ['1.0alpha', '1.0beta'],
        ['8.1.0', '8.0.30', '>='],
        ['1.0', '1.0.0', 'eq'],
        ['1.0-dev', '1.0', '<'],
        ['1.0pl1', '1.0'],
    ],
];
