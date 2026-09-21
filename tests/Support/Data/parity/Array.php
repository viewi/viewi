<?php

use Tests\Support\Parity\PhpCallback;
use Tests\Support\Parity\PhpConstant;

// Parity cases for PhpJsFunctions/Array - see tests/Support/Parity/ParityRunner.php for the format.

$odd = new PhpCallback(fn($v) => $v % 2 === 1, 'function (v) { return v % 2 === 1; }');
$byN = new PhpCallback(fn($a, $b) => $a['n'] - $b['n'], 'function (a, b) { return a["n"] - b["n"]; }');
$minus = new PhpCallback(fn($a, $b) => $a - $b, 'function (a, b) { return a - b; }');
$both = new PhpCallback(fn($v, $k) => $k === 'a' && $v === 1, 'function (v, k) { return k === "a" && v === 1; }');
$keyIsB = new PhpCallback(fn($k) => $k === 'b', 'function (k) { return k === "b"; }');

return [
    'count' => [
        [[]],
        [[1, 2, 3]],
        [['a' => 1, 'b' => 2]],
        [[1, [2, 3]], new PhpConstant('COUNT_RECURSIVE')],
        [[1, [2, 3]], 1],
    ],
    'array_keys' => [
        [['a' => 1, 'b' => 2]],
        ['args' => [[5 => 'x', 2 => 'y']], 'knownDiff' => 'int-key-order'],
        [[]],
        [['x', 'y']],
        [['a' => 1, 'b' => 2, 'c' => 1], 1],
    ],
    'in_array' => [
        ['1', [1, 2]],
        ['1', [1, 2], true],
        [0, ['a']],
        ['abc', [0]],
        [null, [0]],
        ['1e1', ['10']],
        ['b', ['x' => 'a', 'y' => 'b']],
    ],
    'array_values' => [
        [['a' => 1, 'b' => 2]],
        [[]],
        [[5 => 'a', 9 => 'b']],
    ],
    'array_filter' => [
        [[1, 0, 2, null, 3]],
        [['a' => 1, 'b' => 0]],
        [[1, 2, 3, 4], $odd],
        [[]],
        [['a' => 1, 'b' => 2], $keyIsB, new PhpConstant('ARRAY_FILTER_USE_KEY')],
        [['a' => 1, 'b' => 2], $keyIsB, 2],
        [['a' => 1, 'b' => 2], $both, 1],
        [['0', 'x', '', 'y']],
    ],
    'array_flip' => [
        [['a', 'b']],
        [['x' => 1, 'y' => 2]],
        [['x' => 'a', 'y' => 'a']],
    ],
    'array_merge' => [
        [[1, 2], [3]],
        [['a' => 1], ['a' => 2, 'b' => 3]],
        [[5 => 'x'], [5 => 'y']],
        [[], []],
        ['args' => [['a' => 1], [7 => 'z']], 'knownDiff' => 'int-key-order'],
        [],
    ],
    'array_search' => [
        ['b', ['a', 'b']],
        ['z', ['a']],
        ['1', [0, 1]],
        ['1', [0, 1], true],
        [2, ['x' => 1, 'y' => 2]],
        ['v', [5 => 'v']],
    ],
    'array_slice' => [
        [[1, 2, 3, 4], 1, 2],
        [[1, 2, 3], -2],
        [['a' => 1, 'b' => 2, 'c' => 3], 1],
        [[1, 2, 3], 1, null, true],
        [[1, 2, 3], 5],
        [[1, 2, 3], 0, -1],
    ],
    'array_splice' => [
        ['args' => [[1, 2, 3, 4], 1, 2], 'refs' => [0]],
        ['args' => [[1, 2, 3], 1, 0, ['x', 'y']], 'refs' => [0]],
        ['args' => [[1, 2, 3], -1], 'refs' => [0]],
        ['args' => [['a' => 1, 'b' => 2], 0, 1], 'refs' => [0]],
        ['args' => [[1, 2, 3], 1, 1, 'z'], 'refs' => [0]],
    ],
    'array_unshift' => [
        ['args' => [[2, 3], 1], 'refs' => [0]],
        ['args' => [[], 'a', 'b'], 'refs' => [0]],
        ['args' => [['k' => 1], 0], 'refs' => [0]],
    ],
    'usort' => [
        ['args' => [[3, 1, 2], $minus], 'refs' => [0]],
        ['args' => [['b' => 2, 'a' => 1], $minus], 'refs' => [0], 'knownDiff' => 'by-ref-type-change'],
        ['args' => [[['n' => 2], ['n' => 1]], $byN], 'refs' => [0]],
        ['args' => [[], $minus], 'refs' => [0]],
    ],
];
