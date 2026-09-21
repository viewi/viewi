<?php

// Parity cases for PhpJsFunctions/Pcre — see tests/Support/Parity/ParityRunner.php for the format.

return [
    'preg_match' => [
        ['/^\d+$/', '123'],
        ['/^\d+$/', 'abc'],
        ['#^/admin/(.*)$#i', '/ADMIN/blog'],
        ['args' => ['/(\w+)@(\w+)/', 'me@host', []], 'refs' => [2]],
    ],
];
