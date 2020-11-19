<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Fmod extends BaseFunctionConverter
{
    public static string $name = 'fmod';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Fmod.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
