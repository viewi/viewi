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
    // --- task 10: the rest of the Pcre group ---
    'preg_quote' => [
        ['Hello.World?(1+1)'],
        ['a/b#c', '/'],
        ['a#b', '#'],
    ],
    'preg_replace' => [
        ['/a/', 'b', 'banana'],
        ['/(\\w+) (\\w+)/', '$2 $1', 'hello world'],
        ['/(\\d)/', '<\\1>', 'a1b2'],
        ['/\\s+/', ' ', "a  b\t\nc"],
        ['/A/i', 'x', 'aA'],
        ['/a/', 'b', 'aaa', 2],
        [['/a/', '/b/'], ['b', 'c'], 'ab'],
        [['/a/', '/b/'], 'x', 'ab'],
        ['/x/', 'y', ['ax', 'bx']],
        ['/(a)/', '${1}1', 'a'],
    ],
];
