<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class StrWordCount extends BaseFunction
{
    public static string $name = 'str_word_count';

    public static function getUses(): array
    {
        return ['ctype_alpha'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StrWordCount.js';
        return file_get_contents($jsToInclude);
    }
}
