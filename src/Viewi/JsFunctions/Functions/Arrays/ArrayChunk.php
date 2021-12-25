<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayChunk extends BaseFunctionConverter
{
    public static string $name = 'array_chunk';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayChunk.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
