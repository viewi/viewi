<?php

namespace Tests\Support\Parity;

use Closure;

/**
 * A callback as a case argument (usort, array_filter, array_map…), written once per language:
 * new PhpCallback(fn($a, $b) => $a - $b, 'function (a, b) { return a - b; }')
 * The JS side is what the transpiler would emit for the PHP closure — keep them equivalent.
 */
final class PhpCallback
{
    public function __construct(public readonly Closure $php, public readonly string $js)
    {
    }
}
