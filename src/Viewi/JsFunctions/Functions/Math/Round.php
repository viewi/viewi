<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Round extends BaseFunctionConverter
{
    public static string $name = 'round';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('_php_cast_float');
        $translator->includeFunction('_php_cast_int');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Round.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
