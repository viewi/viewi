<?php

namespace Tests\Support\Parity;

/**
 * PHP side for the entries in functions.php that are not callable PHP functions:
 * the transpiler's cast helpers and language constructs (isset…). Everything else is
 * compared against the real PHP function of the same name.
 */
final class PhpEquivalents
{
    public static function get(string $fn): ?callable
    {
        return match ($fn) {
            '_php_cast_int' => fn($value) => (int)$value,
            '_php_cast_float' => fn($value) => (float)$value,
            '_phpCastString' => fn($value) => (string)$value,
            '_php_compare' => fn($a, $b) => $a <=> $b,
            'isset' => function (...$values) {
                foreach ($values as $value) {
                    if ($value === null) {
                        return false;
                    }
                }
                return true;
            },
            default => null,
        };
    }
}
