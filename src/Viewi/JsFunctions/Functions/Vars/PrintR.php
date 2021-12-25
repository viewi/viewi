<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class PrintR extends BaseFunctionConverter
{
    public static string $name = 'print_r';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'PrintR.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
