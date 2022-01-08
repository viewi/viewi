<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Dechex extends BaseFunctionConverter
{
    public static string $name = 'dechex';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Dechex.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
