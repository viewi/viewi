<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Boolval extends BaseFunctionConverter
{
    public static string $name = 'boolval';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Boolval.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
