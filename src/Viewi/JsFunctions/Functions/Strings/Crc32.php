<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Crc32 extends BaseFunctionConverter
{
    public static string $name = 'crc32';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Crc32.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
