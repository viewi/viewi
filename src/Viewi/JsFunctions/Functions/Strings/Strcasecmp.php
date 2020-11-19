<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strcasecmp extends BaseFunctionConverter
{
    public static string $name = 'strcasecmp';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Strcasecmp.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
