<?php

namespace Tests\Support\Parity;

use InvalidArgumentException;

/**
 * Type-preserving wire format shared by PHP and parity-runner.mjs.
 *
 * Every value becomes ['t' => tag, 'v' => payload] so int/bool/null/[]/{} survive JSON:
 *   null | bool | int | float | string | bytes (non-UTF-8, base64) | list | map ([[key, value]…])
 * Arguments only: const (a PhpConstant) and callback (a PhpCallback) - resolved per language.
 * JS adds: undefined | badstring (lone UTF-16 surrogates) | function | object | error.
 * Floats that JSON can't carry travel as the strings "NAN" / "INF" / "-INF" / "-0".
 */
final class Value
{
    public static function encode(mixed $value): array
    {
        return match (true) {
            $value === null => ['t' => 'null'],
            is_bool($value) => ['t' => 'bool', 'v' => $value],
            is_int($value) => ['t' => 'int', 'v' => $value],
            is_float($value) => ['t' => 'float', 'v' => self::encodeFloat($value)],
            is_string($value) => mb_check_encoding($value, 'UTF-8')
                ? ['t' => 'string', 'v' => $value]
                : ['t' => 'bytes', 'v' => base64_encode($value)],
            is_array($value) => array_is_list($value)
                ? ['t' => 'list', 'v' => array_map([self::class, 'encode'], $value)]
                : ['t' => 'map', 'v' => self::encodePairs($value)],
            $value instanceof \stdClass => ['t' => 'map', 'v' => self::encodePairs((array)$value)],
            $value instanceof PhpConstant => ['t' => 'const', 'v' => $value->name],
            $value instanceof PhpCallback => ['t' => 'callback', 'v' => $value->js],
            default => throw new InvalidArgumentException('Parity: can not encode ' . get_debug_type($value)),
        };
    }

    /**
     * Canonical text form used for the assertion, so PHPUnit prints a readable diff.
     * int and float both render as number(…): JS has one number type, so 1 and 1.0 are
     * indistinguishable in the browser and comparing them would only produce noise.
     * Map keys render as strings: PHP canonicalises "5" to 5, JS object keys are always strings.
     */
    public static function render(array $tagged, int $depth = 0): string
    {
        $pad = str_repeat('  ', $depth);
        $v = $tagged['v'] ?? null;
        switch ($tagged['t']) {
            case 'null':
            case 'undefined':
            case 'function':
                return $tagged['t'];
            case 'bool':
                return 'bool(' . ($v ? 'true' : 'false') . ')';
            case 'int':
            case 'float':
                return 'number(' . self::renderNumber($v) . ')';
            case 'string':
                return 'string(' . json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ')';
            case 'badstring':
                return 'badstring(' . $v . ')';
            case 'bytes':
                return 'bytes(' . bin2hex(base64_decode($v)) . ')';
            case 'error':
                return 'error(' . $v . ')';
            case 'object':
                return 'object(' . $v . ')';
            case 'list':
                if ($v === []) {
                    return 'list[]';
                }
                $items = array_map(fn($item) => $pad . '  ' . self::render($item, $depth + 1), $v);
                return "list[\n" . implode(",\n", $items) . "\n$pad]";
            case 'map':
                if ($v === []) {
                    return 'map{}';
                }
                $items = array_map(
                    fn($pair) => $pad . '  ' . json_encode((string)$pair[0], JSON_UNESCAPED_UNICODE) . ': ' . self::render($pair[1], $depth + 1),
                    $v
                );
                return "map{\n" . implode(",\n", $items) . "\n$pad}";
        }
        throw new InvalidArgumentException('Parity: unknown tag ' . $tagged['t']);
    }

    /** Types and sizes only, for functions whose values are random by design (rand, uniqid…). */
    public static function shape(array $tagged): string
    {
        $v = $tagged['v'] ?? null;
        return match ($tagged['t']) {
            'int', 'float' => 'number',
            'string' => 'string(' . mb_strlen($v) . ')',
            'list' => 'list(' . count($v) . ')[' . implode(', ', array_map([self::class, 'shape'], $v)) . ']',
            'map' => 'map{' . implode(', ', array_map(fn($pair) => $pair[0] . ': ' . self::shape($pair[1]), $v)) . '}',
            default => $tagged['t'],
        };
    }

    private static function encodeFloat(float $value): float|string
    {
        return match (true) {
            is_nan($value) => 'NAN',
            is_infinite($value) => $value > 0 ? 'INF' : '-INF',
            $value === 0.0 && fdiv(1, $value) < 0 => '-0',
            default => $value,
        };
    }

    private static function renderNumber(int|float|string $v): string
    {
        if (is_string($v) || is_int($v)) {
            return (string)$v;
        }
        if ($v == floor($v) && abs($v) < 1e15) {
            return sprintf('%.0f', $v);
        }
        return json_encode($v); // shortest round-trip digits (serialize_precision -1)
    }

    private static function encodePairs(array $map): array
    {
        $pairs = [];
        foreach ($map as $key => $item) {
            $pairs[] = [(string)$key, self::encode($item)];
        }
        return $pairs;
    }
}
