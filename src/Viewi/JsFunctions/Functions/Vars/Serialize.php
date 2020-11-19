<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Serialize extends BaseFunctionConverter
{
    public static string $name = 'serialize';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Serialize.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
