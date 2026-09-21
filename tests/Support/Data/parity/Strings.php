<?php

use Tests\Support\Parity\PhpConstant;

// Parity cases for PhpJsFunctions/Strings — see tests/Support/Parity/ParityRunner.php for the format.

return [
    'strlen' => [
        [''],
        ['abc'],
        ['args' => ['héllo'], 'knownDiff' => 'bytes-vs-chars'],
        ['args' => ['🙂'], 'knownDiff' => 'bytes-vs-chars'],
    ],
    'mb_strlen' => [
        [''],
        ['héllo'],
        ['🙂'],
    ],
    'md5' => [
        [''],
        ['abc'],
        ['héllo'],
    ],
    'sha1' => [
        [''],
        ['héllo'],
    ],
    'crc32' => [
        ['abc'],
        ['héllo'],
    ],
    'str_pad' => [
        ['5', 3, '0', new PhpConstant('STR_PAD_LEFT')],
        ['abc', 2],
        ['x', 6, 'ab', new PhpConstant('STR_PAD_BOTH')],
    ],
    'trim' => [
        ['  a b  '],
        ["\t\n x \0"],
        ['xxhixx', 'x'],
    ],
    'substr' => [
        ['hello', 1, 3],
        ['hello', -3],
        ['hello', 10],
        ['args' => ['héllo', 0, 2], 'knownDiff' => 'bytes-vs-chars'],
    ],
    'explode' => [
        [',', 'a,b,,c'],
        [',', ''],
        [',', 'a,b,c', 2],
        [',', 'a,b,c', -1],
    ],
    'implode' => [
        [', ', ['a', 'b', 'c']],
        [', ', []],
        ['-', [1, 2.5, true, null]],
    ],
];
