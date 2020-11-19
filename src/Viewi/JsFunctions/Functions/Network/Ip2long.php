<?php

namespace Viewi\JsFunctions\Functions\Network;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Ip2long extends BaseFunctionConverter
{
    public static string $name = 'ip2long';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Ip2long.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
