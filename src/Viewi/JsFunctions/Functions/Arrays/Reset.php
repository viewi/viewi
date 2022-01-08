<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Reset extends BaseFunctionConverter
{
    public static string $name = 'reset';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Reset.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
