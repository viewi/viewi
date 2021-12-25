<?php

namespace Viewi\JsFunctions\Functions\Filesystem;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Pathinfo extends BaseFunctionConverter
{
    public static string $name = 'pathinfo';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Pathinfo.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
