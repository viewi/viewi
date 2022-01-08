<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsInt extends BaseFunctionConverter
{
    public static string $name = 'is_int';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsInt.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
