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
    'by-ref-type-change' => [
        'functions' => ['usort', 'sort', 'array_splice', 'array_unshift'],
        'why' => 'A by-reference function rewrites the caller\'s array in place, because JS can not '
            . 'rebind the caller\'s variable. So it can not turn a map (JS object) into a list (JS '
            . 'array): usort([\'b\' => 2, \'a\' => 1]) leaves PHP a list and the browser an object '
            . 'with keys 0 and 1. Iterating and counting behave the same; array checks do not.',
        'advice' => 'Sort lists, or assign the result: $sorted = array_values($map); usort($sorted, …).',
    ],
    'timezone' => [
        'functions' => ['date', 'mktime', 'strtotime', 'getdate', 'idate'],
        'why' => 'PHP formats and parses times in the server\'s default timezone; the browser uses the '
            . 'visitor\'s. The same timestamp can render as a different hour or even day after '
            . 'hydration. (The parity tests run both sides in UTC.)',
        'advice' => 'Use gmdate()/gmmktime(), or format on the server and pass the string in.',
    ],
];
