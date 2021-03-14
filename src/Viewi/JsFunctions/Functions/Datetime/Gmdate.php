<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Gmdate extends BaseFunctionConverter
{
    public static string $name = 'gmdate';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Gmdate.js';
        $jsToInclueDep = __DIR__ . DIRECTORY_SEPARATOR . 'Date.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        $translator->includeJsFile('date', $jsToInclueDep);
        return $code . '(';
    }
}
