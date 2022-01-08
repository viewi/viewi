<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Atanh extends BaseFunctionConverter
{
    public static string $name = 'atanh';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Atanh.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
