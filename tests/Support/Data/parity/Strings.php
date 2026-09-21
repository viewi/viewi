<?php

use Tests\Support\Parity\PhpConstant;

// Parity cases for PhpJsFunctions/Strings - see tests/Support/Parity/ParityRunner.php for the format.

return [
    'strlen' => [
        [''],
        ['abc'],
        ['args' => ['héllo'], 'knownDiff' => 'bytes-vs-chars'],
        ['args' => ['🙂'], 'knownDiff' => 'bytes-vs-chars'],
    ],
    'mb_strlen' => [
        [''],
        ['héllo'],
        ['🙂'],
    ],
    'md5' => [
        [''],
        ['abc'],
        ['héllo'],
    ],
    'sha1' => [
        [''],
        ['héllo'],
    ],
    'crc32' => [
        ['abc'],
        ['héllo'],
    ],
    'str_pad' => [
        ['5', 3, '0', new PhpConstant('STR_PAD_LEFT')],
        ['abc', 2],
        ['x', 6, 'ab', new PhpConstant('STR_PAD_BOTH')],
        // the values the transpiler will emit for the constants (#92 item 4)
        ['5', 3, '0', 0],
        ['x', 6, 'ab', 2],
        ['abc', 7, '-', 1],
        ['abc', 5],
    ],
    'trim' => [
        ['  a b  '],
        ["\t\n x \0"],
        ['xxhixx', 'x'],
        ["\u{a0}x\u{a0}"],
        ['abcxcba', 'a..c'],
        [''],
    ],
    'ltrim' => [
        ['  x  '],
        ["\0x"],
        ['xxhi', 'x'],
        ["\u{a0}x"],
    ],
    'strpos' => [
        ['hello', 'l'],
        ['hello', 'z'],
        ['hello', 'h'],
        ['hello', 'l', 3],
        ['hello', 'l', -2],
        ['', 'a'],
        ['args' => ['héllo', 'l'], 'knownDiff' => 'bytes-vs-chars'],
    ],
    'str_replace' => [
        ['a', 'b', 'banana'],
        [['a', 'n'], ['1', '2'], 'banana'],
        [['a', 'b'], 'x', 'abc'],
        [['a', 'b'], ['1'], 'abc'],
        ['a', 'b', ['aa', 'ba']],
        ['', 'x', 'abc'],
        ['1', 'x', 101],
    ],
    'strtolower' => [
        ['HeLLo 123'],
        ['ÉCOLE'],
    ],
    'strtoupper' => [
        ['hello 123'],
        ['école'],
    ],
    'number_format' => [
        [1234.5],
        [1234.5678, 2],
        [1234.5678, 2, ',', '.'],
        [1234.5, 0, '', ''],
        [0.5],
        [1.5],
        [2.5],
        [-1234.567, 1],
        [1.005, 2],
        [0.125, 2],
        [-0.4],
        [1000],
        [1234567.891, 2, '.', ' '],
        ['1234.5', 1],
    ],
    'substr' => [
        ['hello', 1, 3],
        ['hello', -3],
        ['hello', 10],
        ['hello', 5],
        ['hello', -10, 2],
        ['hello', 1, -1],
        ['hello', 2, -5],
        ['hello', 0, 0],
        ['hello', 1, null],
        ['', 0],
        ['args' => ['héllo', 0, 2], 'knownDiff' => 'bytes-vs-chars'],
    ],
    '_phpCastString' => [
        [true],
        [false],
        [null],
        [1.0],
        [0.1],
        [-0.0],
        [1e20],
        [0.1 + 0.2],
        [1.5e-5],
        [123456789012345.678],
        [0.0001],
        ['args' => [1e14], 'knownDiff' => 'number-type'],
    ],
    'explode' => [
        [',', 'a,b,,c'],
        [',', ''],
        [',', 'a,b,c', 2],
        [',', 'a,b,c', -1],
    ],
    'implode' => [
        [', ', ['a', 'b', 'c']],
        [', ', []],
        ['-', [1, 2.5, true, null]],
    ],
    // --- task 7: the rest of the Strings group ---
    'addcslashes' => [
        ['foo[bar]', 'A..Z'],
        ["zoo['.']", 'z..A'],
        ["a\nb", "\n"],
    ],
    'addslashes' => [
        ["O'Re\"il\\ly"],
        ["a\0b"],
    ],
    'bin2hex' => [
        ['abc'],
        [''],
        ['args' => ['é'], 'knownDiff' => 'bytes-vs-chars'],
    ],
    'hex2bin' => [
        ['616263'],
        [''],
    ],
    'chop' => [
        ["x  \n"],
        ['xyy', 'y'],
    ],
    'rtrim' => [
        ["x \t\n\0"],
        ['x..', '.'],
        ['abc', 'a..c'],
    ],
    'chr' => [
        [65],
        [256 + 65],
        ['args' => [-1], 'knownDiff' => 'bytes-vs-chars'],
        [0],
    ],
    'ord' => [
        ['A'],
        [''],
        ['args' => ['é'], 'knownDiff' => 'bytes-vs-chars'],
    ],
    'chunk_split' => [
        ['abcdefg', 3],
        ['abcdefg', 3, '-'],
        ['abc'],
    ],
    'convert_uuencode' => [
        ['test'],
    ],
    'count_chars' => [
        ['abca', 3],
        ['abca', 1],
    ],
    'get_html_translation_table' => [
        [],
    ],
    'htmlspecialchars' => [
        ["<a href='x'>T&C \"q\"</a>"],
        ['&amp; stays?'],
        ['&amp;', ENT_QUOTES, 'UTF-8', false],
        ["it's"],
        ["'\"", ENT_NOQUOTES],
        ["'\"", ENT_COMPAT],
        ["it's", ENT_QUOTES | ENT_HTML5],
        ['&amp; &#39; &#x27; &bogus; & x', ENT_QUOTES, 'UTF-8', false],
    ],
    'htmlspecialchars_decode' => [
        ['&lt;b&gt; &amp;amp; &quot;q&quot; &#039;s&#039;'],
        ['&#39; &apos;'],
        ['&#60;b&#62; &#x3C; &#169;'],
        ['&quot;', ENT_NOQUOTES],
        ['&apos;', ENT_QUOTES | ENT_HTML5],
    ],
    'htmlentities' => [
        ['café <b>'],
        ["it's"],
        ['€ … ™ &amp;', ENT_QUOTES, 'UTF-8', false],
    ],
    'html_entity_decode' => [
        ['caf&eacute; &lt;b&gt; &amp; &#039;'],
        ['&hellip; &euro; &#8364; &#x20AC;'],
        ['&amp;lt; &unknown; &#0; &#xD800;'],
        ['&quot;&#039;', ENT_NOQUOTES],
    ],
    'join' => [
        [',', [1, 2]],
    ],
    'lcfirst' => [
        ['Hello'],
        [''],
        ['ÉCOLE'],
    ],
    'ucfirst' => [
        ['hello'],
        [''],
        ['école'],
    ],
    'ucwords' => [
        ['hello world-foo bar_baz'],
        ['hello-world', '-'],
        ["a\tb\nc"],
    ],
    'levenshtein' => [
        ['kitten', 'sitting'],
        ['', 'abc'],
        ['same', 'same'],
    ],
    'metaphone' => [
        ['Thompson'],
        ['knight'],
    ],
    'soundex' => [
        ['Robert'],
        ['Tymczak'],
        [''],
    ],
    'similar_text' => [
        ['World', 'Word'],
        ['', ''],
    ],
    'nl2br' => [
        ["a\nb\r\nc"],
        ["a\nb", false],
    ],
    'parse_str' => [
        ['args' => ['a=1&b[]=2&b[]=3&c[x]=y', []], 'refs' => [1], 'knownDiff' => 'by-ref-type-change'],
        ['args' => ['a+b=c%20d&e', []], 'refs' => [1], 'knownDiff' => 'by-ref-type-change'],
    ],
    'printf' => [
        ['%s-%d', 'x', 5],
    ],
    'sprintf' => [
        ['%s is %d years', 'Tom', 30],
        ['%05.2f', 3.14159],
        ['%-5s|', 'ab'],
        ["%'*8s", 'x'],
        ['%u', -1],
        ['%x %X %o %b', 255, 255, 8, 5],
        ['%e', 1234.5],
        ['%1$s %2$s %1$s', 'a', 'b'],
        ['%%'],
        ['%c', 65],
        ['%+d', 5],
        ['%.3s', 'abcdef'],
        ['%d', '12abc'],
        ['%s', true],
        ['%s', 0.1 + 0.2],
        ['%.1f', 0.05],
    ],
    'vsprintf' => [
        ['%s=%d', ['a', 1]],
    ],
    'vprintf' => [
        ['%s=%d', ['a', 1]],
    ],
    'quoted_printable_encode' => [
        ["a=b é\r\n"],
    ],
    'quoted_printable_decode' => [
        ['a=3Db =C3=A9'],
    ],
    'quotemeta' => [
        ['1+1=2?'],
    ],
    'sscanf' => [
        ['age: 25 name: Bob', 'age: %d name: %s'],
    ],
    'str_getcsv' => [
        ['a,"b,c",d'],
        ['a;b', ';'],
        ['"x""y",z'],
        [''],
    ],
    'str_ireplace' => [
        ['L', 'x', 'Hello'],
        [['A', 'b'], '-', 'aBc'],
    ],
    'str_repeat' => [
        ['ab', 3],
        ['x', 0],
    ],
    'str_rot13' => [
        ['Hello, World!'],
    ],
    'str_shuffle' => [
        ['args' => ['abcdef'], 'shape' => true],
    ],
    'str_split' => [
        ['abcdef', 4],
        ['abc'],
        [''],
    ],
    'str_word_count' => [
        ["Hello fri3nd, you're looking good today!"],
        ['Hello World again', 1],
        ['Hello World', 2],
    ],
    'strcasecmp' => [
        ['Hello', 'hello'],
        ['a', 'B'],
        ['b', 'A'],
    ],
    'strcmp' => [
        ['a', 'b'],
        ['b', 'a'],
        ['a', 'a'],
        ['abc', 'ab'],
    ],
    'strncmp' => [
        ['abcd', 'abcf', 3],
        ['abcd', 'abcf', 4],
    ],
    'strncasecmp' => [
        ['ABcd', 'abCF', 3],
    ],
    'strnatcmp' => [
        ['img12', 'img10'],
        ['img2', 'img10'],
        ['a', 'a'],
    ],
    'strnatcasecmp' => [
        ['IMG2', 'img10'],
    ],
    'strcspn' => [
        ['abcd', 'cd'],
        ['hello', 'l', 1, 2],
    ],
    'strspn' => [
        ['42 is the answer', '1234567890'],
        ['foo', 'o', 1, 2],
    ],
    'strchr' => [
        ['user@example.com', '@'],
    ],
    'strstr' => [
        ['user@example.com', '@'],
        ['user@example.com', '@', true],
        ['abc', 'z'],
    ],
    'stristr' => [
        ['USER@EXAMPLE.com', 'example'],
        ['abc', 'B', true],
    ],
    'strrchr' => [
        ['a/b/c', '/'],
        ['abc', 'z'],
    ],
    'strpbrk' => [
        ['This is a test', 'st'],
        ['abc', 'xyz'],
    ],
    'stripos' => [
        ['ABC', 'b'],
        ['abc', 'z'],
        ['aXbx', 'x', 2],
    ],
    'strrpos' => [
        ['hello', 'l'],
        ['hello', 'z'],
        ['hello', 'l', -3],
        ['hello', 'l', 4],
    ],
    'strripos' => [
        ['HELLO', 'l'],
    ],
    'stripslashes' => [
        ["O\\'Re\\\\il"],
    ],
    'strip_tags' => [
        ['<p>Hi <b>there</b></p><br/>'],
        ['<p>Hi <b>there</b></p>', '<b>'],
        ['a < b and c > d'],
    ],
    'strrev' => [
        ['abc'],
        [''],
    ],
    'strtok' => [
        ['a b c', ' '],
    ],
    'strtr' => [
        ['Hi all', 'ai', 'eo'],
        ['Hi all', ['Hi' => 'Hello', 'all' => 'world']],
        ['aaa', ['a' => 'b', 'aa' => 'c']],
    ],
    'substr_compare' => [
        ['abcde', 'bc', 1, 2],
        ['abcde', 'BC', 1, 2, true],
        ['abcde', 'cd', -3, 2],
    ],
    'substr_count' => [
        ['hello hello', 'll'],
        ['aaa', 'aa'],
        ['hello hello', 'll', 3],
    ],
    'substr_replace' => [
        ['Hello', 'J', 0, 1],
        ['Hello', 'X', -2],
        ['Hello', '!', 5, 0],
        ['Hello', 'X', 1, -1],
    ],
    'wordwrap' => [
        ['The quick brown fox', 10],
        ['The quick brown fox', 10, "<br>"],
        ['A very long woooooooooooord.', 8, "\n", true],
        ['short', 10],
    ],
];
