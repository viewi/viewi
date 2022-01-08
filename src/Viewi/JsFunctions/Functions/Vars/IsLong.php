<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsLong extends BaseFunctionConverter
{
    public static string $name = 'is_long';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('is_float');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsLong.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
