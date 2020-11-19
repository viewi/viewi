<?php

namespace Viewi\JsFunctions\Functions\Bc;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Bcdiv extends BaseFunctionConverter
{
    public static string $name = 'bcdiv';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Bcdiv.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
