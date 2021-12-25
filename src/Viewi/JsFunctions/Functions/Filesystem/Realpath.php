<?php

namespace Viewi\JsFunctions\Functions\Filesystem;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Realpath extends BaseFunctionConverter
{
    public static string $name = 'realpath';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Realpath.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
