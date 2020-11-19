<?php

namespace Viewi\JsFunctions\Functions\Misc;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Uniqid extends BaseFunctionConverter
{
    public static string $name = 'uniqid';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Uniqid.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
