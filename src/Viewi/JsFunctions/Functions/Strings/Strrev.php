<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strrev extends BaseFunctionConverter
{
    public static string $name = 'strrev';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Strrev.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
