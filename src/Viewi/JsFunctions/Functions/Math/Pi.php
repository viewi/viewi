<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Pi extends BaseFunctionConverter
{
    public static string $name = 'pi';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Pi.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
