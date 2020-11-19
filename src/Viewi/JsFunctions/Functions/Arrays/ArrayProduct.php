<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayProduct extends BaseFunctionConverter
{
    public static string $name = 'array_product';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayProduct.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
