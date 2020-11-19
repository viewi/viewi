<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strrchr extends BaseFunctionConverter
{
    public static string $name = 'strrchr';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Strrchr.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
