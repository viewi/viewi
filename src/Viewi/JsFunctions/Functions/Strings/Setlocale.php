<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Setlocale extends BaseFunctionConverter
{
    public static string $name = 'setlocale';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('getenv');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Setlocale.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
