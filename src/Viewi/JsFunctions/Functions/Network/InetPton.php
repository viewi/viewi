<?php

namespace Viewi\JsFunctions\Functions\Network;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class InetPton extends BaseFunctionConverter
{
    public static string $name = 'inet_pton';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'InetPton.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
