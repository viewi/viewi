<?php

// PHP↔JS differences Viewi accepts instead of fixing: a JS port can't close them without
// becoming something else. A parity case opts in with 'knownDiff' => '<id>' and must then keep
// differing — once it matches, PhpJsParityTest fails and asks for the marker to be removed.
// `functions` lists where it shows; `advice` is what a component author should do about it.

return [
    'bytes-vs-chars' => [
        'functions' => ['strlen', 'substr'],
        'why' => 'PHP string functions count UTF-8 bytes; JS strings count UTF-16 characters. '
            . 'strlen("héllo") is 6 on the server and 5 in the browser, and substr() offsets land '
            . 'on different characters once the text has any non-ASCII in it.',
        'advice' => 'For user-visible text use mb_strlen(), which counts characters on both sides '
            . '(mb_substr() has no JS port yet).',
    ],
    'int-key-order' => [
        'functions' => ['array_keys', 'array_values', 'foreach'],
        'why' => 'A PHP array with integer keys that is not a list becomes a plain JS object, and JS '
            . 'objects always iterate integer-like keys first, in ascending order. [5 => "x", 2 => "y"] '
            . 'arrives in the browser as {"2": "y", "5": "x"}: json_encode keeps the order in the JSON '
            . 'text, but JSON.parse reorders the keys on the way in. '
            . 'The key-returning functions (array_keys, array_search, key, array_key_first/last, '
            . 'array_flip) give integer-like keys back as numbers; foreach keys stay strings.',
        'advice' => 'Keep ordered data as a list of records ([["id" => 5, …], …]), not as a map '
            . 'keyed by id.',
    ],
    'number-type' => [
        'functions' => ['*'],
        'why' => 'JS has one number type, so 1 and 1.0 are the same value in the browser; is_int() / '
            . 'is_float() can not tell them apart there. The parity tests compare numbers by value '
            . 'for that reason.',
        'advice' => 'Don\'t branch on int vs float in component code.',
    ],
    'float-to-string' => [
        'functions' => ['implode', 'strval', '(string)'],
        'why' => 'Very large and very small floats print differently: PHP writes 1.0E+20 and 1.0E-7, '
            . 'JS writes 100000000000000000000 and 1e-7.',
        'advice' => 'Format numbers for display with number_format() or sprintf().',
    ],
];
