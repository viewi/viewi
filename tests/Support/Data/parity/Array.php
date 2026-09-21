<?php

use Tests\Support\Parity\PhpCallback;
use Tests\Support\Parity\PhpConstant;

// Parity cases for PhpJsFunctions/Array - see tests/Support/Parity/ParityRunner.php for the format.

$odd = new PhpCallback(fn($v) => $v % 2 === 1, 'function (v) { return v % 2 === 1; }');
$byN = new PhpCallback(fn($a, $b) => $a['n'] - $b['n'], 'function (a, b) { return a["n"] - b["n"]; }');
$minus = new PhpCallback(fn($a, $b) => $a - $b, 'function (a, b) { return a - b; }');
$both = new PhpCallback(fn($v, $k) => $k === 'a' && $v === 1, 'function (v, k) { return k === "a" && v === 1; }');
$cmp = new PhpCallback(fn($a, $b) => $a <=> $b, 'function (a, b) { return a < b ? -1 : (a > b ? 1 : 0); }');
$double = new PhpCallback(fn($v) => $v * 2, 'function (v) { return v * 2; }');
$pair = new PhpCallback(fn($a, $b) => $a . $b, 'function (a, b) { return String(a) + String(b); }');
$sum = new PhpCallback(fn($carry, $v) => $carry + $v, 'function (carry, v) { return carry + v; }');
$walker = new PhpCallback(fn($v, $k) => null, 'function (v, k) { return null; }');
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
    // --- task 8: the rest of the Array group ---
    'array_change_key_case' => [
        [['Ab' => 1, 'cD' => 2]],
        ['args' => [['Ab' => 1, 5 => 2], 1], 'knownDiff' => 'int-key-order'],
    ],
    'array_chunk' => [
        [[1, 2, 3, 4, 5], 2],
        [['a' => 1, 'b' => 2, 'c' => 3], 2, true],
        [[], 2],
    ],
    'array_column' => [
        [[['id' => 3, 'n' => 'a'], ['id' => 5, 'n' => 'b']], 'n'],
        [[['id' => 3, 'n' => 'a'], ['id' => 5, 'n' => 'b']], 'n', 'id'],
        [[['id' => 3, 'n' => 'a'], ['id' => 5]], null, 'id'],
    ],
    'array_combine' => [
        [['a', 'b'], [1, 2]],
        [[5, 'x'], ['v', 'w']],
    ],
    'array_count_values' => [
        ['args' => [['a', 'b', 'a', 1, '1']], 'knownDiff' => 'int-key-order'],
        [['a', 'b', 'a']],
    ],
    'array_diff' => [
        [[1, 2, 3, 4], [2, 4]],
        [['a' => 'x', 'b' => 'y'], ['y']],
        [[1, '1', 2], ['1']],
    ],
    'array_diff_assoc' => [
        ['args' => [['a' => 'g', 'b' => 'r', 0 => 'x'], ['a' => 'g', 'B' => 'r', 1 => 'x']], 'knownDiff' => 'int-key-order'],
        [['a' => 'g', 'b' => 'r'], ['a' => 'g', 'B' => 'r']],
    ],
    'array_diff_key' => [
        [['a' => 1, 'b' => 2, 5 => 3], ['a' => 9, '5' => 9]],
    ],
    'array_diff_uassoc' => [
        [['a' => 'g', 'b' => 'r'], ['a' => 'g', 'B' => 'r'], $cmp],
    ],
    'array_diff_ukey' => [
        [['a' => 1, 'b' => 2], ['b' => 9], $cmp],
    ],
    'array_fill' => [
        [5, 3, 'x'],
        [-3, 3, 'y'],
        [0, 0, 'z'],
    ],
    'array_fill_keys' => [
        ['args' => [['a', 5, 'b'], 0], 'knownDiff' => 'int-key-order'],
        [['a', 'b'], 0],
    ],
    'array_intersect' => [
        [[1, 2, 3], [2, 3, 4]],
        [['a' => 'x', 'b' => 'y'], ['y', 'z']],
    ],
    'array_intersect_assoc' => [
        [['a' => 'g', 'b' => 'r'], ['a' => 'g', 'b' => 'x']],
    ],
    'array_intersect_key' => [
        [['a' => 1, 'b' => 2, 'c' => 3], ['a' => 0, 'c' => 0]],
    ],
    'array_intersect_uassoc' => [
        [['a' => 'g', 'b' => 'r'], ['a' => 'g', 'b' => 'x'], $cmp],
    ],
    'array_intersect_ukey' => [
        [['a' => 1, 'b' => 2], ['b' => 9], $cmp],
    ],
    'array_key_exists' => [
        ['a', ['a' => null]],
        [1, ['1' => 'x']],
        ['1', [1 => 'x']],
        ['z', ['a' => 1]],
        [0, ['x']],
    ],
    'array_map' => [
        [$double, [1, 2, 3]],
        [$double, ['a' => 1, 'b' => 2]],
        [$pair, [1, 2], ['a', 'b']],
        [null, [1, 2], ['a', 'b']],
        [$pair, ['x' => 1], ['y' => 2]],
    ],
    'array_merge_recursive' => [
        [['a' => [1], 'b' => 2], ['a' => [3], 'b' => 4]],
        [['a' => 'x'], ['a' => 'y']],
    ],
    'array_multisort' => [
        ['args' => [[3, 1, 2]], 'refs' => [0]],
        ['args' => [[10, 100, 100, 0], [1, 3, 2, 4]], 'refs' => [0, 1]],
        ['args' => [[1, 2, 3], 3], 'refs' => [0]],
        ['args' => [['b' => 2, 'a' => 1]], 'refs' => [0]],
    ],
    'array_pad' => [
        [[1, 2], 4, 0],
        [[1, 2], -4, 0],
        [[1, 2], 1, 0],
    ],
    'array_pop' => [
        ['args' => [[1, 2, 3]], 'refs' => [0]],
        ['args' => [[]], 'refs' => [0]],
        ['args' => [['a' => 1, 'b' => 2]], 'refs' => [0]],
    ],
    'array_product' => [
        [[2, '3', 4]],
        [[]],
    ],
    'array_push' => [
        ['args' => [[1], 2, 3], 'refs' => [0]],
        ['args' => [[5 => 'a'], 'b'], 'refs' => [0]],
    ],
    'array_rand' => [
        ['args' => [[1, 2, 3]], 'shape' => true],
    ],
    'array_reduce' => [
        [[1, 2, 3], $sum, 0],
        [[], $sum, 'init'],
        [[], $sum],
    ],
    'array_replace' => [
        [['a', 'b', 'c'], [1 => 'x'], [3 => 'y']],
        [['k' => 1], ['k' => 2, 'n' => 3]],
    ],
    'array_replace_recursive' => [
        [['a' => [1, 2]], ['a' => [1 => 'b']]],
    ],
    'array_reverse' => [
        [[1, 2, 3]],
        [['a' => 1, 2, 3]],
        ['args' => [['a' => 1, 2, 3], true], 'knownDiff' => 'int-key-order'],
    ],
    'array_shift' => [
        ['args' => [[1, 2, 3]], 'refs' => [0]],
        ['args' => [[5 => 'a', 9 => 'b', 'k' => 'c']], 'refs' => [0]],
        ['args' => [[]], 'refs' => [0]],
    ],
    'array_sum' => [
        [[1, 2, 3.5]],
        [['1', '2x', 3]],
        [[]],
    ],
    'array_udiff' => [
        [[1, 5, 3], [3], $cmp],
    ],
    'array_udiff_assoc' => [
        [['a' => 1, 'b' => 2], ['a' => 1, 'b' => 3], $cmp],
    ],
    'array_udiff_uassoc' => [
        [['a' => 1, 'b' => 2], ['a' => 1, 'b' => 3], $cmp, $cmp],
    ],
    'array_uintersect' => [
        [[1, 5, 3], [3, 1], $cmp],
    ],
    'array_uintersect_uassoc' => [
        [['a' => 1, 'b' => 2], ['a' => 1, 'b' => 3], $cmp, $cmp],
    ],
    'array_unique' => [
        [[1, '1', 2, 2.0, 'a', 'A']],
        [['x' => 'a', 'y' => 'b', 'z' => 'a']],
        [[3, 1, 3], 2],
    ],
    'array_walk' => [
        [[1, 2], $walker],
    ],
    'array_walk_recursive' => [
        [[1, [2, 3]], $walker],
    ],
    'current' => [
        [[5, 6]],
        [[]],
        [['a' => 1]],
    ],
    'pos' => [
        [[5, 6]],
    ],
    'end' => [
        ['args' => [[1, 2, 3]], 'refs' => [0]],
        ['args' => [[]], 'refs' => [0]],
    ],
    'key' => [
        [['a' => 1]],
        [[7 => 'x']],
        [[]],
    ],
    'next' => [
        ['args' => [[1, 2]], 'refs' => [0]],
        ['args' => [[1]], 'refs' => [0]],
    ],
    'prev' => [
        ['args' => [[1, 2]], 'refs' => [0]],
    ],
    'reset' => [
        ['args' => [[1, 2]], 'refs' => [0]],
        ['args' => [[]], 'refs' => [0]],
    ],
    'range' => [
        [1, 5],
        [5, 1],
        [0, 10, 3],
        ['a', 'e'],
        ['e', 'a', 2],
        [0, 1, 0.25],
        ['1', '3'],
    ],
    'sizeof' => [
        [[1, 2]],
    ],
    'shuffle' => [
        ['args' => [[1, 2, 3]], 'refs' => [0], 'shape' => true],
    ],
    'sort' => [
        ['args' => [[3, '10', 2, 'a', 'B']], 'refs' => [0]],
        ['args' => [['b' => 2, 'a' => 1]], 'refs' => [0], 'knownDiff' => 'by-ref-type-change'],
        ['args' => [['10', '9', '1e1']], 'refs' => [0]],
        ['args' => [['10', '9', 'a'], 2], 'refs' => [0]],
        ['args' => [['b', 'A', 'a'], 2 | 8], 'refs' => [0]],
        ['args' => [['img12', 'img10', 'img2'], 6], 'refs' => [0]],
    ],
    'rsort' => [
        ['args' => [[3, 1, 2]], 'refs' => [0]],
        ['args' => [['10', '9']], 'refs' => [0]],
    ],
    'asort' => [
        ['args' => [['x' => 3, 'y' => 1, 'z' => 2]], 'refs' => [0]],
        ['args' => [['x' => '10', 'y' => '9']], 'refs' => [0]],
    ],
    'arsort' => [
        ['args' => [['x' => 3, 'y' => 1, 'z' => 2]], 'refs' => [0]],
    ],
    'ksort' => [
        ['args' => [['b' => 1, 'a' => 2, 'c' => 3]], 'refs' => [0]],
        ['args' => [['10' => 'a', '9' => 'b', 'x' => 'c']], 'refs' => [0]],
    ],
    'krsort' => [
        ['args' => [['b' => 1, 'a' => 2, 'c' => 3]], 'refs' => [0]],
    ],
    'natsort' => [
        ['args' => [['img12.png', 'img10.png', 'IMG2.png', 'img1.png']], 'refs' => [0], 'knownDiff' => 'int-key-order'],
        ['args' => [['a' => 'img12.png', 'b' => 'img10.png', 'c' => 'IMG2.png', 'd' => 'img1.png']], 'refs' => [0]],
    ],
    'natcasesort' => [
        ['args' => [['img12.png', 'img10.png', 'IMG2.png', 'img1.png']], 'refs' => [0], 'knownDiff' => 'int-key-order'],
        ['args' => [['a' => 'img12.png', 'b' => 'img10.png', 'c' => 'IMG2.png', 'd' => 'img1.png']], 'refs' => [0]],
    ],
    'uasort' => [
        ['args' => [['x' => 3, 'y' => 1, 'z' => 2], $cmp], 'refs' => [0]],
    ],
    'uksort' => [
        ['args' => [['b' => 1, 'a' => 2], $cmp], 'refs' => [0]],
    ],
];
