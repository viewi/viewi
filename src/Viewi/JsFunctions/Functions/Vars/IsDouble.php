<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsDouble extends BaseFunctionConverter
{
    public static string $name = 'is_double';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('is_float');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsDouble.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
