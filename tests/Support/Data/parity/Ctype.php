<?php

// Parity cases for PhpJsFunctions/Ctype — see tests/Support/Parity/ParityRunner.php for the format.

return [
    'ctype_alnum' => [
        ['abc123'],
        ['abc 1'],
        [''],
        ['é'],
    ],
    'ctype_digit' => [
        ['123'],
        ['12.3'],
        [''],
        ['-1'],
    ],
];
