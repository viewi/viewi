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
            // PHP 8.2+ documents -1/0/1 for these, but a call PHP folds at compile time can still
            // return the byte difference (-2), so the PHP side is compared by sign
            'strcmp', 'strcasecmp', 'strncmp', 'strncasecmp', 'substr_compare'
                => fn(...$args) => $fn(...$args) <=> 0,
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
