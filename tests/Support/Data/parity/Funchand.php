<?php

use Tests\Support\Parity\PhpCallback;

// Parity cases for PhpJsFunctions/Funchand - see tests/Support/Parity/ParityRunner.php for the format.

return [
    'call_user_func' => [
        [new PhpCallback(fn($a, $b) => $a + $b, 'function (a, b) { return a + b; }'), 2, 3],
    ],
    'call_user_func_array' => [
        [new PhpCallback(fn($a, $b) => $a . $b, 'function (a, b) { return String(a) + String(b); }'), ['x', 'y']],
    ],
];
