<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class StrWordCount extends BaseFunctionConverter
{
    public static string $name = 'str_word_count';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('ctype_alpha');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StrWordCount.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
