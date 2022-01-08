<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Metaphone extends BaseFunctionConverter
{
    public static string $name = 'metaphone';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Metaphone.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
