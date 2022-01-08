<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsNull extends BaseFunctionConverter
{
    public static string $name = 'is_null';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsNull.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
