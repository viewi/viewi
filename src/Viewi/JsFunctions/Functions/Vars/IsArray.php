<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsArray extends BaseFunctionConverter
{
    public static string $name = 'is_array';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsArray.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
