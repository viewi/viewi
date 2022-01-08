<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Tanh extends BaseFunctionConverter
{
    public static string $name = 'tanh';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Tanh.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
