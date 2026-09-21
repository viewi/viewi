<?php

use Tests\Support\Parity\PhpConstant;

// Parity cases for PhpJsFunctions/Json - see tests/Support/Parity/ParityRunner.php for the format.

return [
    'json_encode' => [
        [[1, 2]],
        [['a' => 1]],
        [[]],
        ['é'],
        ['a/b'],
        [1.0],
        [0.1],
        [true],
        [null],
        [['a' => [1, ['b' => null]]]],
        ["<tag>&'\""],
        [[1, 2], new PhpConstant('JSON_PRETTY_PRINT')],
        ['é', new PhpConstant('JSON_UNESCAPED_UNICODE')],
        // the values the transpiler will emit for the constants (#92 item 4)
        [[1, 2], 128],
        [['a' => ['b' => 1], 'c' => []], 128],
        ['é/ü', 256 | 64],
        ["<a>&'\"", 1 | 2 | 4 | 8],
        [[1, 2], 16],
        ["line\u{2028}sep", 256],
        [1.5e-7],
        [1e25],
        [1700000000123456],
        [-0.0],
        ["\x01\t"],
        ['🙂'],
        [NAN],
    ],
];
