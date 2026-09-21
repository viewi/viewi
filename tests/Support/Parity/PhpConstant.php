<?php

namespace Tests\Support\Parity;

/**
 * A PHP constant as a case argument: new PhpConstant('STR_PAD_LEFT').
 * PHP receives constant('STR_PAD_LEFT'); JS receives what the transpiled code would pass -
 * the transpiler emits the bare identifier, so JS looks the name up on the global object
 * and throws a ReferenceError if the Viewi runtime doesn't define it.
 */
final class PhpConstant
{
    public function __construct(public readonly string $name)
    {
    }
}
