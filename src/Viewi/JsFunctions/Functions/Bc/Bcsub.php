<?php

namespace Viewi\JsFunctions\Functions\Bc;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Bcsub extends BaseFunctionConverter
{
    public static string $name = 'bcsub';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Bcsub.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
