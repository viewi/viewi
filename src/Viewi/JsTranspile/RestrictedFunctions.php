<?php

namespace Viewi\JsTranspile;

/**
 * Entries in functions.php that a component may NOT call directly. Their JS ports stay, because
 * other ports depend on them (ctype_* → setlocale, printf → echo, round → _php_cast_int…), but a
 * direct call from component code or a template fails the build with the reason below.
 *
 * SERVER_ONLY: meaningless in a browser - the port would answer about the browser, or fake it.
 * INTERNAL:    helpers and language constructs that other ports / the transpiler use; not PHP
 *              functions a component calls.
 */
class RestrictedFunctions
{
    public const SERVER_ONLY = 'server-only';
    public const INTERNAL = 'internal';

    private const LIST = [
        // shell / filesystem
        'escapeshellarg' => [self::SERVER_ONLY, 'there is no shell in a browser'],
        'file_get_contents' => [self::SERVER_ONLY, 'there is no filesystem in a browser'],
        'realpath' => [self::SERVER_ONLY, 'there is no filesystem in a browser'],
        'md5_file' => [self::SERVER_ONLY, 'there is no filesystem in a browser'],
        'sha1_file' => [self::SERVER_ONLY, 'there is no filesystem in a browser'],
        // HTTP
        'setcookie' => [self::SERVER_ONLY, 'it sets a response header in PHP; the browser port writes document.cookie instead'],
        'setrawcookie' => [self::SERVER_ONLY, 'it sets a response header in PHP; the browser port writes document.cookie instead'],
        // runtime configuration
        'getenv' => [self::SERVER_ONLY, 'the browser has no server environment'],
        'ini_get' => [self::SERVER_ONLY, 'php.ini settings exist only on the server'],
        'ini_set' => [self::SERVER_ONLY, 'php.ini settings exist only on the server'],
        'set_time_limit' => [self::SERVER_ONLY, 'it limits the server request, not the browser'],
        'assert_options' => [self::SERVER_ONLY, 'assertion settings exist only on the server (and it is deprecated since PHP 8.3)'],
        // reflection
        'function_exists' => [self::SERVER_ONLY, 'the answer would describe the browser bundle, not PHP'],
        'get_defined_functions' => [self::SERVER_ONLY, 'the answer would describe the browser bundle, not PHP'],
        // locale
        'setlocale' => [self::SERVER_ONLY, 'it changes the locale of the whole server worker, so every request it serves'],
        'localeconv' => [self::SERVER_ONLY, 'it reads the server locale, which the browser does not share'],
        'nl_langinfo' => [self::SERVER_ONLY, 'it reads the server locale, which the browser does not share'],
        'strcoll' => [self::SERVER_ONLY, 'it compares by the server locale, which the browser does not share'],

        '_bc' => [self::INTERNAL, 'the shared helper behind the bc* functions'],
        '_phpCastString' => [self::INTERNAL, 'the transpiler\'s (string) cast helper'],
        '_php_cast_float' => [self::INTERNAL, 'the transpiler\'s (float) cast helper'],
        '_php_cast_int' => [self::INTERNAL, 'the transpiler\'s (int) cast helper'],
        '_php_compare' => [self::INTERNAL, 'PHP 8 comparison rules for in_array, array_search, max, min…'],
        '_php_array_key' => [self::INTERNAL, 'turns an integer-like key back into a number for array_keys, array_search…'],
        '_php_array_entries' => [self::INTERNAL, 'reads a PHP array as ordered [key, value] pairs'],
        '_php_array' => [self::INTERNAL, 'builds a PHP array from [key, value] pairs (list or map, as PHP decides)'],
        '_php_array_set' => [self::INTERNAL, 'writes a by-reference result back into the caller\'s array'],
        '_php_trim' => [self::INTERNAL, 'the shared body of trim, ltrim and rtrim'],
        '_php_sort_compare' => [self::INTERNAL, 'a comparator for PHP\'s sort flags'],
        '_php_set_op' => [self::INTERNAL, 'the shared body of the array_diff and array_intersect family'],
        '_php_sort' => [self::INTERNAL, 'the shared body of the sort family'],
        '_php_strrpos' => [self::INTERNAL, 'the shared body of strrpos and strripos'],
        '_php_html_unescape' => [self::INTERNAL, 'the shared body of htmlspecialchars_decode and html_entity_decode'],
        '_php_html_escape' => [self::INTERNAL, 'the shared body of htmlspecialchars and htmlentities'],
        '_php_html_entities' => [self::INTERNAL, 'the HTML 4.01 entity table, generated from PHP'],
        'i18n_loc_get_default' => [self::INTERNAL, 'a locale helper for the sort functions'],
        'i18n_loc_set_default' => [self::INTERNAL, 'a locale helper for the sort functions'],
        'echo' => [self::INTERNAL, 'a language construct; printf/print_r/var_dump use it for output'],
        'isset' => [self::INTERNAL, 'a language construct the transpiler inlines'],
        'empty' => [self::INTERNAL, 'a language construct'],
    ];

    /** @return array<string, array{0: string, 1: string}> fn => [kind, reason] */
    public static function all(): array
    {
        return self::LIST;
    }

    /** Why a component may not call $fn directly, or null when it may. */
    public static function message(string $fn): ?string
    {
        if (!isset(self::LIST[$fn])) {
            return null;
        }
        [$kind, $reason] = self::LIST[$fn];
        return $kind === self::SERVER_ONLY
            ? "$fn() is server-only: $reason. Call it on the server (a service or controller) and pass the result to the component."
            : "$fn() is an internal Viewi helper, not a function to call from a component: $reason.";
    }
}
