<?php

// Parity cases for PhpJsFunctions/Pcre - see tests/Support/Parity/ParityRunner.php for the format.

return [
    'preg_match' => [
        ['/^\d+$/', '123'],
        ['/^\d+$/', 'abc'],
        ['#^/admin/(.*)$#i', '/ADMIN/blog'],
        ['args' => ['/(\w+)@(\w+)/', 'me@host', []], 'refs' => [2]],
        ['args' => ['/(a)(b)?(c)?/', 'a', []], 'refs' => [2]],
        ['args' => ['/(a)?(b)/', 'b', []], 'refs' => [2]],
        ['args' => ['/x/', 'abc', ['stale']], 'refs' => [2]],
        ['/a\/b/', 'a/b'],
        ['{^\d+$}', '42'],
        ['/^a.c$/s', "a\nc"],
    ],
];
