<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strstr extends BaseFunctionConverter
{
    public static string $name = 'strstr';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Strstr.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
