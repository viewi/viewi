<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Each extends BaseFunctionConverter
{
    public static string $name = 'each';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Each.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
